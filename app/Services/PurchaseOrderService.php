<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PurchaseOrderService
{
    public function getPurchaseOrdersPaginated(?string $statusFilter)
    {
        return PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items')
            ->when($statusFilter, function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * @throws ValidationException
     */
    public function createPurchaseOrder(array $validated): PurchaseOrder
    {
        $supplierId = (int) $validated['supplier_id'];
        $requestedItems = collect($validated['items'])
            ->filter(fn (array $item): bool => (int) ($item['quantity'] ?? 0) > 0)
            ->values();

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 item produk untuk membuat purchase order.',
            ]);
        }

        $productIds = $requestedItems->pluck('product_id')->map(fn ($id): int => (int) $id)->unique()->values();
        $products = Product::query()
            ->whereIn('id', $productIds->all())
            ->get()
            ->keyBy('id');

        foreach ($productIds as $productId) {
            $product = $products->get($productId);

            if (! $product || (int) $product->supplier_id !== $supplierId) {
                throw ValidationException::withMessages([
                    'items' => 'Semua produk PO harus berasal dari supplier yang dipilih.',
                ]);
            }
        }

        return DB::transaction(function () use ($validated, $products, $requestedItems, $supplierId): PurchaseOrder {
            $po = PurchaseOrder::query()->create([
                'supplier_id' => $supplierId,
                'po_number' => $this->generatePoNumber(),
                'status' => $validated['status'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_cost' => 0,
            ]);

            $totalCost = 0;

            foreach ($requestedItems as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = $quantity * $unitCost;

                $po->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $totalCost += $lineTotal;
            }

            $po->update(['total_cost' => $totalCost]);

            return $po;
        });
    }

    /**
     * @throws RuntimeException
     */
    public function updatePurchaseOrderStatus(PurchaseOrder $purchaseOrder, array $validated): void
    {
        $nextStatus = $validated['status'];

        if ($purchaseOrder->status === 'received' && $nextStatus !== 'received') {
            throw new RuntimeException('Status PO yang sudah received tidak dapat diubah.');
        }

        if ($purchaseOrder->status !== 'received' && $nextStatus === 'received') {
            throw new RuntimeException('Gunakan tombol Terima Barang agar stok otomatis bertambah.');
        }

        $purchaseOrder->update($validated);
    }

    /**
     * @throws RuntimeException
     */
    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status === 'received') {
            throw new RuntimeException('PO yang sudah diterima tidak dapat dihapus.');
        }

        $purchaseOrder->delete();
    }

    /**
     * @throws RuntimeException
     */
    public function receivePurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder): void {
            $lockedPo = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPo->status === 'received') {
                throw new RuntimeException('PO ini sudah pernah diterima sebelumnya.');
            }

            if ($lockedPo->status === 'cancelled') {
                throw new RuntimeException('PO berstatus cancelled tidak dapat diterima.');
            }

            $lockedPo->load('items');

            foreach ($lockedPo->items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->increment('stock', (int) $item->quantity);
            }

            $lockedPo->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }

    private function generatePoNumber(): string
    {
        $dateCode = now()->format('Ymd');
        $prefix = 'PO-'.$dateCode.'-';

        $lastPoToday = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix.'%')
            ->latest('id')
            ->value('po_number');

        $nextSequence = 1;

        if (is_string($lastPoToday) && str_starts_with($lastPoToday, $prefix)) {
            $lastSequence = (int) substr($lastPoToday, -4);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
