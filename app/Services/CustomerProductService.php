<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class CustomerProductService
{
    public function getProductsData(?int $categoryId, ?string $searchQuery, ?User $user): array
    {
        $products = Product::query()
            ->with('category')
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('is_active', true)
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            })
            ->when($categoryId, function ($query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        $cartQuantities = [];

        if ($user) {
            $cartQuantities = Cart::query()
                ->where('user_id', $user->id)
                ->whereIn('product_id', $products->pluck('id')->all())
                ->pluck('quantity', 'product_id')
                ->all();
        }

        return [
            'products' => $products,
            'categories' => $categories,
            'cartQuantities' => $cartQuantities,
            'selectedCategoryId' => $categoryId,
        ];
    }

    public function getProductDetails(Product $product, ?User $user): array
    {
        $product->load([
            'category',
            'reviews' => function ($query): void {
                $query->with('user')->latest();
            },
        ]);

        $product->loadSum([
            'orderItems as total_purchased' => function ($query): void {
                $query->whereHas('order', function ($orderQuery): void {
                    $orderQuery->where('status', '!=', 'cancelled');
                });
            },
        ], 'quantity');
        $product->loadAvg('reviews as avg_rating', 'rating');

        $cartQuantity = 0;
        $canReview = false;
        $myReview = null;

        if ($user) {
            $cartQuantity = (int) Cart::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->value('quantity');

            $canReview = $user
                ->orders()
                ->where('status', 'delivered')
                ->whereHas('items', function ($query) use ($product): void {
                    $query->where('product_id', $product->id);
                })
                ->exists();

            $myReview = $user
                ->reviews()
                ->where('product_id', $product->id)
                ->first();
        }

        return [
            'product' => $product,
            'cartQuantity' => $cartQuantity,
            'canReview' => $canReview,
            'myReview' => $myReview,
        ];
    }
}
