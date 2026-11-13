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

    /**
     * @return array{status: string, message: string}
     */
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

        $requestedQuantity = max(0, (int) ($cartItem->quantity ?? 0)) + max(1, $quantity);
        $wasLimited = $this->setAndSaveQuantity($cartItem, $requestedQuantity, (int) $product->stock);

        return [
            'status' => 'success',
            'message' => $wasLimited
                ? 'Produk ditambahkan sesuai batas stok yang tersedia.'
                : 'Produk berhasil ditambahkan ke keranjang.',
        ];
    }

    /**
     * @return array{status: string, message?: string}
     */
    public function updateItemQuantity(Cart $cartItem, int $requestedQuantity): array
    {
        $product = $cartItem->product;

        if (! $product instanceof Product || ! $product->is_active) {
            return [
                'status' => 'error',
                'message' => 'Produk sudah tidak tersedia. Silakan hapus item ini dari keranjang.',
            ];
        }

        if ($product->stock <= 0) {
            return [
                'status' => 'error',
                'message' => 'Stok produk sedang kosong. Silakan hapus item ini dari keranjang.',
            ];
        }

        $this->setAndSaveQuantity($cartItem, $requestedQuantity, (int) $product->stock);

        return ['status' => 'success'];
    }

    /**
     * @return array{status: string, message?: string}
     */
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

    /**
     * @return bool Whether the requested quantity was capped by available stock.
     */
    private function setAndSaveQuantity(Cart $cartItem, int $requestedQuantity, int $stock): bool
    {
        $maxQuantity = max(1, $stock);
        $nextQuantity = max(1, min($requestedQuantity, $maxQuantity));

        $cartItem->quantity = $nextQuantity;
        $cartItem->save();

        return $nextQuantity < $requestedQuantity;
    }

    /**
     * @return array{status: string, message?: string}
     */
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

        $wasLimited = $this->setAndSaveQuantity($cartItem, $requestedQuantity, $productStock);

        return $wasLimited
            ? ['status' => 'success', 'message' => 'Jumlah disesuaikan dengan stok yang tersedia.']
            : ['status' => 'success'];
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function handleIncrementAction(Cart $cartItem, Product $product, int $currentQuantity, int $productStock): array
    {
        if (! $this->isProductAvailable($product)) {
            return [
                'status' => 'error',
                'message' => self::PRODUCT_UNAVAILABLE_MESSAGE,
            ];
        }

        $wasLimited = $this->setAndSaveQuantity($cartItem, $currentQuantity + 1, $productStock);

        return $wasLimited
            ? ['status' => 'success', 'message' => 'Jumlah sudah mencapai batas stok.']
            : ['status' => 'success'];
    }

    /**
     * @return array{status: string, message?: string}
     */
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
