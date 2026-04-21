<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('user')
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,shipped,delivered,cancelled'],
            'courier' => ['nullable', 'string', 'max:255'],
            'courier_service' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $shouldRestoreStock = $order->status !== 'cancelled' && $validated['status'] === 'cancelled';

        if ($shouldRestoreStock) {
            DB::transaction(function () use ($order, $validated): void {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->with('items.product')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedOrder->status !== 'cancelled') {
                    foreach ($lockedOrder->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', (int) $item->quantity);
                        }
                    }
                }

                $lockedOrder->update($validated);
            });
        } else {
            $order->update($validated);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
