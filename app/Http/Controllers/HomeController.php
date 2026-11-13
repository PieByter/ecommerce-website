<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private readonly HomeService $homeService) {}

    /**
     * Show the storefront to guests and route authenticated users to their dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->isCustomer()) {
            return redirect()->route('customer.home');
        }

        $selectedCategoryId = $request->filled('category')
            ? (int) $request->input('category')
            : null;

        $data = $this->homeService->getHomeData($selectedCategoryId, null);

        return view('customer.home', [
            'featuredProducts' => $data['featuredProducts'],
            'categories' => $data['categories'],
            'cartQuantities' => $data['cartQuantities'],
            'selectedCategoryId' => $data['selectedCategoryId'],
        ]);
    }
}
