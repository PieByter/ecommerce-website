<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::query()
            ->with('category')
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::query()->latest()->take(6)->get();

        $cartQuantities = [];

        if (Auth::check()) {
            $cartQuantities = Cart::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('product_id', $featuredProducts->pluck('id')->all())
                ->pluck('quantity', 'product_id')
                ->all();
        }

        return view('customer.home', compact('featuredProducts', 'categories', 'cartQuantities'));
    }
}
