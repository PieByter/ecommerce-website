<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategoryId = $request->filled('category')
            ? (int) $request->input('category')
            : null;

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

        if (Auth::check()) {
            $cartQuantities = Cart::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('product_id', $featuredProducts->pluck('id')->all())
                ->pluck('quantity', 'product_id')
                ->all();
        }

        return view('customer.home', compact('featuredProducts', 'categories', 'cartQuantities', 'selectedCategoryId'));
    }
}
