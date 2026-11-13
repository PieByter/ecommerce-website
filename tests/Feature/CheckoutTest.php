<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout creates an order, decrements stock, and clears the cart', function () {
    $customer = User::factory()->customer()->create();
    $product = Product::factory()->create([
        'price' => 100000,
        'stock' => 5,
        'is_active' => true,
    ]);

    Cart::query()->create([
        'user_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($customer)->post(route('checkout.store'), [
        'shipping_address' => 'Jl. Test No. 1',
        'courier' => 'JNE',
        'courier_service' => 'REG',
    ]);

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('customer.orders.show', $order));

    expect($order->status)->toBe(Order::STATUS_PENDING)
        ->and((int) $product->fresh()->stock)->toBe(3)
        ->and(Cart::query()->count())->toBe(0)
        ->and($order->items()->count())->toBe(1);
});

test('checkout rejects quantities above available stock', function () {
    $customer = User::factory()->customer()->create();
    $product = Product::factory()->create([
        'price' => 100000,
        'stock' => 2,
        'is_active' => true,
    ]);

    Cart::query()->create([
        'user_id' => $customer->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $response = $this->actingAs($customer)->post(route('checkout.store'), [
        'shipping_address' => 'Jl. Test No. 1',
        'courier' => 'JNE',
        'courier_service' => 'REG',
    ]);

    $response->assertRedirect(route('checkout.index'))
        ->assertSessionHasErrors('cart');

    expect(Order::query()->count())->toBe(0)
        ->and((int) $product->fresh()->stock)->toBe(2)
        ->and(Cart::query()->count())->toBe(1);
});
