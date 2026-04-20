<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $reviews = ProductReview::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return view('customer.reviews.index', compact('reviews'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_if(! $product->is_active, 404);

        $hasCompletedPurchase = $user->orders()
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product): void {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (! $hasCompletedPurchase) {
            return redirect()
                ->route('products.show', $product->slug)
                ->with('error', 'Review hanya bisa diberikan setelah pesanan selesai (delivered).');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        ProductReview::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('products.show', $product->slug)
            ->with('success', 'Review berhasil disimpan.');
    }
}
