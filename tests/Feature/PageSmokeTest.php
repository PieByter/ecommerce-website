<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin pages render successfully', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $category = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $order = Order::factory()->create(['user_id' => $customer->id]);
    $purchaseOrder = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.categories.index'))->assertOk();
    $this->get(route('admin.categories.create'))->assertOk();
    $this->get(route('admin.categories.edit', $category))->assertOk();
    $this->get(route('admin.suppliers.index'))->assertOk();
    $this->get(route('admin.suppliers.create'))->assertOk();
    $this->get(route('admin.suppliers.edit', $supplier))->assertOk();
    $this->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.products.create'))->assertOk();
    $this->get(route('admin.products.edit', $product))->assertOk();
    $this->get(route('admin.purchase-orders.index'))->assertOk();
    $this->get(route('admin.purchase-orders.create'))->assertOk();
    $this->get(route('admin.purchase-orders.show', $purchaseOrder))->assertOk();
    $this->get(route('admin.customers.index'))->assertOk();
    $this->get(route('admin.customers.edit', $customer))->assertOk();
    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.users.edit', $admin))->assertOk();
    $this->get(route('admin.orders.index'))->assertOk();
    $this->get(route('admin.orders.show', $order))->assertOk();
});

test('customer pages render successfully', function () {
    $customer = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
    $order = Order::factory()->create(['user_id' => $customer->id]);

    Cart::query()->create([
        'user_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->get(route('home'))->assertOk();
    $this->get(route('products.index'))->assertOk();
    $this->get(route('products.show', $product->slug))->assertOk();

    $this->actingAs($customer);

    $this->get(route('customer.home'))->assertOk();
    $this->get(route('cart.index'))->assertOk();
    $this->get(route('checkout.index'))->assertOk();
    $this->get(route('customer.orders.index'))->assertOk();
    $this->get(route('customer.orders.show', $order))->assertOk();
    $this->get(route('customer.reviews.index'))->assertOk();
    $this->get(route('customer.notifications.index'))->assertOk();
    $this->get(route('profile.edit'))->assertOk();
});
