<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can move a pending order to confirmed and payment time is recorded', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => Order::STATUS_CONFIRMED,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    $order->refresh();

    expect($order->status)->toBe(Order::STATUS_CONFIRMED)
        ->and($order->paid_at)->not->toBeNull();
});

test('admin cannot move a delivered order back to pending', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => Order::STATUS_DELIVERED]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => Order::STATUS_PENDING,
        ])
        ->assertSessionHas('error');

    expect($order->fresh()->status)->toBe(Order::STATUS_DELIVERED);
});

test('shipped status requires a tracking number', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => Order::STATUS_PROCESSING]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => Order::STATUS_SHIPPED,
        ])
        ->assertSessionHasErrors('tracking_number');

    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING);
});

test('product cannot be deleted when it is used by an order', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create();

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'price' => 10000,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertSessionHas('error');

    expect($product->fresh())->not->toBeNull();
});
