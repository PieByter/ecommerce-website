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
        $paidStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];

        $stats = [
            'totalCustomers' => User::query()->where('role', 'customer')->count(),
            'totalProducts' => Product::query()->count(),
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->where('status', 'pending')->count(),
            'totalRevenue' => (float) Order::query()
                ->whereIn('status', $paidStatuses)
                ->sum('total_price'),
        ];

        return view('livewire.admin.dashboard-stats', compact('stats'));
    }
}
