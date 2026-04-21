<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $orders = $user->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $cancelled = DB::transaction(function () use ($user, $order): bool {
            $customerOrder = Order::query()
                ->whereBelongsTo($user)
                ->whereKey($order->id)
                ->with('items.product')
                ->lockForUpdate()
                ->first();

            if (! $customerOrder) {
                abort(404);
            }

            if ($customerOrder->status !== 'pending') {
                return false;
            }

            foreach ($customerOrder->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', (int) $item->quantity);
                }
            }

            $customerOrder->update(['status' => 'cancelled']);

            return true;
        });

        if (! $cancelled) {
            return redirect()
                ->route('customer.orders.index')
                ->withErrors(['order' => 'Pesanan hanya bisa dibatalkan saat status masih pending.']);
        }

        return redirect()
            ->route('customer.orders.index')
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
