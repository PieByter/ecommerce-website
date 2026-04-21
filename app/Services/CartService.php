<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    public function getCartItems(User $user): Collection
    {
        return $user->carts()->with('product')->latest()->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1): array
    {
        if (! $product->is_active || $product->stock < 1) {
            return [
                'status' => 'error',
                'message' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.',
            ];
        }

        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = (int) ($cartItem->quantity ?? 0);
        $nextQuantity = min($currentQuantity + $quantity, (int) $product->stock);

        $cartItem->quantity = max(1, $nextQuantity);
        $cartItem->save();

        return [
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
        ];
    }

    public function updateItemQuantity(Cart $cartItem, int $requestedQuantity): void
    {
        $maxQuantity = max(1, (int) ($cartItem->product?->stock ?? $requestedQuantity));
        $nextQuantity = min($requestedQuantity, $maxQuantity);

        $cartItem->update(['quantity' => $nextQuantity]);
    }

    public function adjustQuantity(User $user, Product $product, string $action, int $requestedQuantity = 0): array
    {
        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = (int) ($cartItem->quantity ?? 0);

        if ($action === 'set') {
            if ($requestedQuantity <= 0) {
                if ($cartItem->exists) {
                    $cartItem->delete();
                }

                return ['status' => 'success'];
            }

            if (! $product->is_active || $product->stock < 1) {
                return [
                    'status' => 'error',
                    'message' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.',
                ];
            }

            $nextQuantity = min($requestedQuantity, (int) $product->stock);
            $cartItem->quantity = max(1, $nextQuantity);
            $cartItem->save();

            return ['status' => 'success'];
        }

        if ($action === 'increment') {
            if (! $product->is_active || $product->stock < 1) {
                return [
                    'status' => 'error',
                    'message' => 'Produk tidak tersedia untuk ditambahkan ke keranjang.',
                ];
            }

            $nextQuantity = min($currentQuantity + 1, (int) $product->stock);
            $cartItem->quantity = max(1, $nextQuantity);
            $cartItem->save();

            return ['status' => 'success'];
        }

        if ($currentQuantity <= 1) {
            if ($cartItem->exists) {
                $cartItem->delete();
            }

            return ['status' => 'success'];
        }

        $cartItem->quantity = $currentQuantity - 1;
        $cartItem->save();

        return ['status' => 'success'];
    }

    public function removeItem(Cart $cartItem): void
    {
        $cartItem->delete();
    }
}
