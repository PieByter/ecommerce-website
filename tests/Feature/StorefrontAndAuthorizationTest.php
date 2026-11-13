<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can browse the storefront and product catalog', function () {
    Product::factory()->create(['is_active' => true]);

    $this->get(route('home'))->assertOk();
    $this->get(route('products.index'))->assertOk();
});

test('parent category filter includes products from its subcategories', function () {
    $parent = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);
    $child = Category::query()->create([
        'name' => 'Oli Mesin',
        'slug' => 'oli-mesin',
        'parent_id' => $parent->id,
        'sort_order' => 1,
    ]);
    $product = Product::factory()->create([
        'category_id' => $child->id,
        'is_active' => true,
    ]);

    $this->get(route('products.index', ['category' => $parent->id]))
        ->assertOk()
        ->assertSee($product->name);
});

test('customer cannot access the admin dashboard', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access the admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('admin cannot edit a customer through the admin user route', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $customer))
        ->assertNotFound();
});

test('customer cannot view another customers order', function () {
    $owner = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherCustomer)
        ->get(route('customer.orders.show', $order))
        ->assertNotFound();
});
