<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->whereIn('status', Order::PAID_STATUSES)
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('customer.notifications', compact('orders'));
    }
}
