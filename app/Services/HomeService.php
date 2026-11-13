<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;

class HomeService
{
    public function getHomeData(?int $selectedCategoryId, ?int $userId): array
    {
        $selectedCategory = $selectedCategoryId
            ? Category::query()->find($selectedCategoryId)
            : null;

        $categoryIds = $selectedCategory
            ? array_merge([(int) $selectedCategory->id], $selectedCategory->descendantIds())
            : [];

        $featuredProducts = Product::query()
            ->with('category')
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('is_active', true)
            ->when($categoryIds !== [], function ($query) use ($categoryIds): void {
                $query->whereIn('category_id', $categoryIds);
            })
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::query()->ordered()->get();

        $cartQuantities = [];

        if ($userId) {
            $cartQuantities = Cart::query()
                ->where('user_id', $userId)
                ->whereIn('product_id', $featuredProducts->pluck('id')->all())
                ->pluck('quantity', 'product_id')
                ->all();
        }

        return [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'cartQuantities' => $cartQuantities,
            'selectedCategoryId' => $selectedCategory?->id,
        ];
    }
}
