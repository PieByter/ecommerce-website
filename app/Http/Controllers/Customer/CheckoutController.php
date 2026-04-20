<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $cartItems = Cart::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang kosong. Tambahkan produk terlebih dahulu.']);
        }

        $subtotal = $cartItems->sum(function (Cart $item): float {
            return (float) ($item->product?->price ?? 0) * (int) $item->quantity;
        });

        $shippingCost = 0;
        $grandTotal = $subtotal + $shippingCost;

        return view('customer.checkout', compact('cartItems', 'subtotal', 'shippingCost', 'grandTotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
            'courier' => ['nullable', 'string', 'max:255'],
            'courier_service' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cartItems = Cart::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang kosong.']);
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $user, $validated): Order {
                $subtotal = 0;

                foreach ($cartItems as $item) {
                    $product = $item->product;

                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                    }

                    if ((int) $item->quantity > (int) $product->stock) {
                        throw new \RuntimeException('Stok produk tidak mencukupi untuk checkout.');
                    }

                    $subtotal += ((float) $product->price * (int) $item->quantity);
                }

                $shippingCost = 0;

                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending',
                    'total_price' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'shipping_address' => $validated['shipping_address'],
                    'courier' => $validated['courier'] ?? null,
                    'courier_service' => $validated['courier_service'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($cartItems as $item) {
                    $product = $item->product;

                    if (! $product) {
                        continue;
                    }

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $product->price,
                    ]);

                    $product->decrement('stock', (int) $item->quantity);
                }

                Cart::query()->where('user_id', $user->id)->delete();

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('checkout.index')->withErrors(['cart' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('customer.orders.index')->with('success', 'Checkout berhasil. Pesanan '.$order->order_number.' sedang menunggu konfirmasi admin.');
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
