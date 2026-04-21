<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $reviews = $this->reviewService->getUserReviews($user);

        return view('customer.reviews.index', compact('reviews'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_if(! $product->is_active, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->reviewService->storeReview($user, $product, $validated);
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('products.show', $product->slug)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('products.show', $product->slug)
            ->with('success', 'Review berhasil disimpan.');
    }
}
