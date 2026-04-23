<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    private const PRODUCT_UNAVAILABLE_MESSAGE = 'Produk tidak tersedia untuk ditambahkan ke keranjang.';

    public function getCartItems(User $user): Collection
    {
        return $user->carts()->with('product')->latest()->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1): array
    {
        if (! $this->isProductAvailable($product)) {
            return [
                'status' => 'error',
                'message' => self::PRODUCT_UNAVAILABLE_MESSAGE,
            ];
        }

        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->setAndSaveQuantity($cartItem, ((int) ($cartItem->quantity ?? 0)) + $quantity, (int) $product->stock);

        return [
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
        ];
    }

    public function updateItemQuantity(Cart $cartItem, int $requestedQuantity): void
    {
        $stock = (int) ($cartItem->product?->stock ?? $requestedQuantity);
        $this->setAndSaveQuantity($cartItem, $requestedQuantity, $stock);
    }

    public function adjustQuantity(User $user, Product $product, string $action, int $requestedQuantity = 0): array
    {
        $cartItem = Cart::query()->firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = (int) ($cartItem->quantity ?? 0);
        $productStock = (int) $product->stock;

        return match ($action) {
            'set' => $this->handleSetAction($cartItem, $product, $requestedQuantity, $productStock),
            'increment' => $this->handleIncrementAction($cartItem, $product, $currentQuantity, $productStock),
            'decrement' => $this->handleDecrementAction($cartItem, $currentQuantity),
            default => [
                'status' => 'error',
                'message' => 'Aksi jumlah keranjang tidak valid.',
            ],
        };
    }

    public function removeItem(Cart $cartItem): void
    {
        $cartItem->delete();
    }

    private function isProductAvailable(Product $product): bool
    {
        return $product->is_active && $product->stock > 0;
    }

    private function setAndSaveQuantity(Cart $cartItem, int $requestedQuantity, int $stock): void
    {
        $maxQuantity = max(1, $stock);
        $nextQuantity = min($requestedQuantity, $maxQuantity);

        $cartItem->quantity = max(1, $nextQuantity);
        $cartItem->save();
    }

    private function handleSetAction(Cart $cartItem, Product $product, int $requestedQuantity, int $productStock): array
    {
        if ($requestedQuantity <= 0) {
            if ($cartItem->exists) {
                $cartItem->delete();
            }

            return ['status' => 'success'];
        }

        if (! $this->isProductAvailable($product)) {
            return [
                'status' => 'error',
                'message' => self::PRODUCT_UNAVAILABLE_MESSAGE,
            ];
        }

        $this->setAndSaveQuantity($cartItem, $requestedQuantity, $productStock);

        return ['status' => 'success'];
    }

    private function handleIncrementAction(Cart $cartItem, Product $product, int $currentQuantity, int $productStock): array
    {
        if (! $this->isProductAvailable($product)) {
            return [
                'status' => 'error',
                'message' => self::PRODUCT_UNAVAILABLE_MESSAGE,
            ];
        }

        $this->setAndSaveQuantity($cartItem, $currentQuantity + 1, $productStock);

        return ['status' => 'success'];
    }

    private function handleDecrementAction(Cart $cartItem, int $currentQuantity): array
    {
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
}
