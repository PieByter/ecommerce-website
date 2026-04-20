<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items')
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->orderBy('name')->get();
        $products = Product::query()
            ->with('supplier')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'in:draft,ordered'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $supplierId = (int) $validated['supplier_id'];
        $requestedItems = collect($validated['items'])
            ->filter(fn(array $item): bool => (int) ($item['quantity'] ?? 0) > 0)
            ->values();

        if ($requestedItems->isEmpty()) {
            return back()->withInput()->withErrors([
                'items' => 'Minimal harus ada 1 item produk untuk membuat purchase order.',
            ]);
        }

        $productIds = $requestedItems->pluck('product_id')->map(fn($id): int => (int) $id)->unique()->values();
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

        $purchaseOrder = DB::transaction(function () use ($validated, $products, $requestedItems, $supplierId): PurchaseOrder {
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

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.product']);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,ordered,received,cancelled'],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $nextStatus = $validated['status'];

        if ($purchaseOrder->status === 'received' && $nextStatus !== 'received') {
            return back()->with('error', 'Status PO yang sudah received tidak dapat diubah.');
        }

        if ($purchaseOrder->status !== 'received' && $nextStatus === 'received') {
            return back()->with('error', 'Gunakan tombol Terima Barang agar stok otomatis bertambah.');
        }

        $purchaseOrder->update($validated);

        return back()->with('success', 'Purchase order berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'PO yang sudah diterima tidak dapat dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase order berhasil dihapus.');
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
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
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'PO diterima dan stok produk berhasil ditambahkan.');
    }

    private function generatePoNumber(): string
    {
        $dateCode = now()->format('Ymd');
        $prefix = 'PO-' . $dateCode . '-';

        $lastPoToday = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix . '%')
            ->latest('id')
            ->value('po_number');

        $nextSequence = 1;

        if (is_string($lastPoToday) && str_starts_with($lastPoToday, $prefix)) {
            $lastSequence = (int) substr($lastPoToday, -4);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
