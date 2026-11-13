<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
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

        $hasUnavailableItems = $cartItems->contains(function (Cart $item): bool {
            $product = $item->product;

            return ! $product instanceof Product
                || ! $product->is_active
                || (int) $product->stock < (int) $item->quantity;
        });

        $shippingCost = 0.0;

        return [
            'cartItems' => $cartItems,
            'subtotal' => (float) $subtotal,
            'shippingCost' => $shippingCost,
            'grandTotal' => (float) $subtotal + $shippingCost,
            'hasUnavailableItems' => $hasUnavailableItems,
        ];
    }

    /**
     * @throws RuntimeException
     */
    public function processCheckout(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data): Order {
            $cartItems = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw new RuntimeException('Keranjang kosong.');
            }

            /** @var Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;

            foreach ($cartItems as $item) {
                $product = $products->get((int) $item->product_id);
                $quantity = (int) $item->quantity;

                if (! $product instanceof Product || ! $product->is_active) {
                    throw new RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                }

                if ($product->stock < $quantity) {
                    throw new RuntimeException('Stok produk '.$product->name.' tidak mencukupi untuk checkout.');
                }

                $subtotal += (float) $product->price * $quantity;
            }

            $shippingCost = 0.0;

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'total_price' => $subtotal,
                'shipping_cost' => $shippingCost,
                'shipping_address' => $data['shipping_address'],
                'courier' => $data['courier'] ?? null,
                'courier_service' => $data['courier_service'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $product = $products->get((int) $item->product_id);
                $quantity = (int) $item->quantity;

                if (! $product instanceof Product) {
                    throw new RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => (float) $product->price,
                ]);

                $affected = Product::query()
                    ->whereKey($product->id)
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($affected === 0) {
                    throw new RuntimeException('Stok produk '.$product->name.' berubah saat checkout. Silakan coba lagi.');
                }
            }

            Cart::query()->where('user_id', $user->id)->delete();

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';

        $lastNumber = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('order_number');

        $nextSequence = 1;

        if (is_string($lastNumber) && str_starts_with($lastNumber, $prefix)) {
            $nextSequence = (int) substr($lastNumber, -4) + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
