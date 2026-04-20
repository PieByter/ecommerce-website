<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $latestOrders = Order::query()
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.dashboard', compact('latestOrders'));
    }
}
