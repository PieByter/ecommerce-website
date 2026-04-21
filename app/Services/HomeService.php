<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;

class HomeService
{
    public function getHomeData(?int $selectedCategoryId, ?int $userId): array
    {
        $featuredProducts = Product::query()
            ->with('category')
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('is_active', true)
            ->when($selectedCategoryId, function ($query) use ($selectedCategoryId): void {
                $query->where('category_id', $selectedCategoryId);
            })
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::query()->orderBy('name')->get();

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
        ];
    }
}
