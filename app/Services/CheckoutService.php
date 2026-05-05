<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function getCheckoutDetails(User $user): array
    {
        $cartItems = Cart::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $subtotal = $cartItems->sum(function (Cart $item): float {
            return (float) ($item->product?->price ?? 0) * (int) $item->quantity;
        });

        $shippingCost = 0;
        $grandTotal = $subtotal + $shippingCost;

        return [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shippingCost' => $shippingCost,
            'grandTotal' => $grandTotal,
        ];
    }

    /**
     * @throws RuntimeException
     */
    public function processCheckout(User $user, array $data): Order
    {
        $cartItems = Cart::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Keranjang kosong.');
        }

        return DB::transaction(function () use ($cartItems, $user, $data): Order {
            $subtotal = 0;

            foreach ($cartItems as $item) {
                $product = $item->product;

                if (! $product || ! $product->is_active) {
                    throw new RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                }

                if ((int) $item->quantity > (int) $product->stock) {
                    throw new RuntimeException('Stok produk '.$product->name.' tidak mencukupi untuk checkout.');
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
                'shipping_address' => $data['shipping_address'] ?? null,
                'courier' => $data['courier'] ?? null,
                'courier_service' => $data['courier_service'] ?? null,
                'notes' => $data['notes'] ?? null,
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
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
