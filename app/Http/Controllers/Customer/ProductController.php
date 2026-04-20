<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategoryId = $request->filled('category')
            ? (int) $request->input('category')
            : null;

        $products = Product::query()
            ->with('category')
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where('name', 'like', '%' . $request->string('q') . '%');
            })
            ->when($selectedCategoryId, function ($query) use ($selectedCategoryId): void {
                $query->where('category_id', $selectedCategoryId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        $cartQuantities = [];

        if ($request->user()) {
            $cartQuantities = Cart::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('product_id', $products->pluck('id')->all())
                ->pluck('quantity', 'product_id')
                ->all();
        }

        return view('customer.products.index', compact('products', 'categories', 'cartQuantities', 'selectedCategoryId'));
    }

    public function show(Product $product): View
    {
        abort_if(! $product->is_active, 404);

        $product->load([
            'category',
            'reviews' => function ($query): void {
                $query->with('user')->latest();
            },
        ]);

        $product->loadSum('orderItems as total_purchased', 'quantity');
        $product->loadAvg('reviews as avg_rating', 'rating');

        $cartQuantity = 0;
        $canReview = false;
        $myReview = null;

        if (request()->user()) {
            $cartQuantity = (int) Cart::query()
                ->where('user_id', request()->user()->id)
                ->where('product_id', $product->id)
                ->value('quantity');

            $user = request()->user();

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

        return view('customer.products.show', compact('product', 'cartQuantity', 'canReview', 'myReview'));
    }
}
