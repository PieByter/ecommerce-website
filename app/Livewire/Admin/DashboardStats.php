<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render(): View
    {
        $stats = [
            'totalCustomers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            'totalProducts' => Product::query()->count(),
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->where('status', Order::STATUS_PENDING)->count(),
            'totalRevenue' => (float) Order::query()
                ->whereIn('status', Order::PAID_STATUSES)
                ->sum('total_price')
                + (float) Order::query()
                    ->whereIn('status', Order::PAID_STATUSES)
                    ->sum('shipping_cost'),
        ];

        return view('livewire.admin.dashboard-stats', compact('stats'));
    }
}
