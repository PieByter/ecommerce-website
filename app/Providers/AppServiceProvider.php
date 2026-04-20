<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.admin', function ($view): void {
            $view->with('adminNotifications', [
                'newOrdersCount' => Order::query()->where('status', 'pending')->count(),
                'outOfStockCount' => Product::query()->where('stock', '<=', 0)->count(),
            ]);
        });
    }
}
