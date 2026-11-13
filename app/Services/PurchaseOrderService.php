<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PurchaseOrderService
{
    public function getPurchaseOrdersPaginated(?string $statusFilter, ?string $search = null): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items')
            ->when($statusFilter, function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($sq) use ($search): void {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
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

        if ($productIds->count() !== $requestedItems->count()) {
            throw ValidationException::withMessages([
                'items' => 'Produk yang sama tidak boleh dipilih lebih dari sekali dalam satu PO.',
            ]);
        }

        $products = Product::query()
            ->findMany($productIds->all())
            ->keyBy('id');

        $validSupplierProductIds = DB::table('product_supplier')
            ->where('supplier_id', $supplierId)
            ->whereIntegerInRaw('product_id', $productIds->all())
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        foreach ($productIds as $productId) {
            $product = $products->get($productId);

            if (! $product instanceof Product || ! in_array($productId, $validSupplierProductIds, true)) {
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

            $totalCost = 0.0;

            foreach ($requestedItems as $item) {
                /** @var Product $product */
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
        $nextStatus = (string) $validated['status'];

        if ($nextStatus === PurchaseOrder::STATUS_RECEIVED) {
            throw new RuntimeException('Gunakan tombol Terima Barang agar stok otomatis bertambah.');
        }

        if (! $purchaseOrder->canTransitionTo($nextStatus)) {
            throw new RuntimeException(sprintf(
                'Status PO tidak dapat diubah dari %s ke %s.',
                $purchaseOrder->status_label,
                PurchaseOrder::labelFor($nextStatus),
            ));
        }

        $purchaseOrder->update($validated);
    }

    /**
     * @throws RuntimeException
     */
    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder->canBeDeleted()) {
            throw new RuntimeException('PO yang sudah diterima tidak dapat dihapus.');
        }

        PurchaseOrder::destroy($purchaseOrder->getKey());
    }

    /**
     * @throws RuntimeException
     */
    public function receivePurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder): void {
            $lockedPo = PurchaseOrder::query()
                ->whereKey($purchaseOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPo->status === PurchaseOrder::STATUS_RECEIVED) {
                throw new RuntimeException('PO ini sudah pernah diterima sebelumnya.');
            }

            if ($lockedPo->status !== PurchaseOrder::STATUS_ORDERED) {
                throw new RuntimeException('Hanya PO berstatus Dipesan yang dapat diterima.');
            }

            $lockedPo->load('items');

            if ($lockedPo->items->isEmpty()) {
                throw new RuntimeException('PO tidak memiliki item untuk diterima.');
            }

            foreach ($lockedPo->items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->increment('stock', (int) $item->quantity);
            }

            $lockedPo->update([
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });
    }

    private function generatePoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';

        $lastPoToday = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('po_number');

        $nextSequence = 1;

        if (is_string($lastPoToday) && str_starts_with($lastPoToday, $prefix)) {
            $nextSequence = (int) substr($lastPoToday, -4) + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
