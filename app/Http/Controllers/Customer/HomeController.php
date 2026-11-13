<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private readonly HomeService $homeService) {}

    public function index(Request $request): View
    {
        $selectedCategoryId = $request->filled('category')
            ? (int) $request->input('category')
            : null;

        $data = $this->homeService->getHomeData($selectedCategoryId, $request->user()?->id);

        return view('customer.home', [
            'featuredProducts' => $data['featuredProducts'],
            'categories' => $data['categories'],
            'cartQuantities' => $data['cartQuantities'],
            'selectedCategoryId' => $data['selectedCategoryId'],
        ]);
    }
}
