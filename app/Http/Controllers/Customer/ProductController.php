<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CustomerProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly CustomerProductService $productService) {}

    public function index(Request $request): View
    {
        $selectedCategoryId = $request->filled('category')
            ? (int) $request->input('category')
            : null;

        $searchQuery = $request->filled('q') ? $request->string('q')->toString() : null;

        $data = $this->productService->getProductsData($selectedCategoryId, $searchQuery, $request->user());

        return view('customer.products.index', $data);
    }

    public function show(Request $request, Product $product): View
    {
        abort_if(! $product->is_active, 404);

        $data = $this->productService->getProductDetails($product, $request->user());

        return view('customer.products.show', $data);
    }
}
