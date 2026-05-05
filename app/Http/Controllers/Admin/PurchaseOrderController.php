<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $purchaseOrderService) {}

    public function index(Request $request): View
    {
        $statusFilter = $request->filled('status') ? $request->string('status')->toString() : null;
        $search = $request->string('search')->toString() ?: null;
        $purchaseOrders = $this->purchaseOrderService->getPurchaseOrdersPaginated($statusFilter, $search);

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->orderBy('name')->get();
        $products = Product::query()
            ->with('suppliers')
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

        try {
            $purchaseOrder = $this->purchaseOrderService->createPurchaseOrder($validated);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

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

        try {
            $this->purchaseOrderService->updatePurchaseOrderStatus($purchaseOrder, $validated);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->purchaseOrderService->deletePurchaseOrder($purchaseOrder);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase order berhasil dihapus.');
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->purchaseOrderService->receivePurchaseOrder($purchaseOrder);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'PO diterima dan stok produk berhasil ditambahkan.');
    }
}
