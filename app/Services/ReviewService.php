<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class ReviewService
{
    public function getUserReviews(User $user): LengthAwarePaginator
    {
        return ProductReview::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);
    }

    /**
     * @throws RuntimeException
     */
    public function storeReview(User $user, Product $product, array $data): void
    {
        $hasCompletedPurchase = $user->orders()
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product): void {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (! $hasCompletedPurchase) {
            throw new RuntimeException('Review hanya bisa diberikan setelah pesanan selesai (delivered).');
        }

        ProductReview::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );
    }
}
