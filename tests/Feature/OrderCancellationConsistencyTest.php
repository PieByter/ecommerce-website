<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeOrderForUser(User $user, string $status): Order
{
    return Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-TEST-' . strtoupper((string) str()->random(10)),
        'status' => $status,
        'total_price' => 0,
        'shipping_cost' => 0,
        'shipping_address' => 'Jl. Test No. 1',
        'courier' => null,
        'courier_service' => null,
        'tracking_number' => null,
        'payment_proof' => null,
        'notes' => null,
        'paid_at' => null,
    ]);
}

test('customer can cancel own pending order and stock is restored', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $product = Product::factory()->create(['stock' => 5, 'is_active' => true]);

    $order = makeOrderForUser($customer, 'pending');

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 100000,
    ]);

    $product->decrement('stock', 2);

    $response = $this
        ->actingAs($customer)
        ->patch(route('customer.orders.cancel', $order));

    $response->assertRedirect(route('customer.orders.index'));
    $response->assertSessionHas('success');

    expect($order->fresh()->status)->toBe('cancelled');
    expect((int) $product->fresh()->stock)->toBe(5);
});

test('admin cancelling order restores stock only once', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $product = Product::factory()->create(['stock' => 5, 'is_active' => true]);

    $order = makeOrderForUser($customer, 'confirmed');

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 100000,
    ]);

    $product->decrement('stock', 2);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => 'cancelled',
            'courier' => null,
            'courier_service' => null,
            'tracking_number' => null,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe('cancelled');
    expect((int) $product->fresh()->stock)->toBe(5);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => 'cancelled',
            'courier' => null,
            'courier_service' => null,
            'tracking_number' => null,
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    expect((int) $product->fresh()->stock)->toBe(5);
});

test('total purchased excludes cancelled orders', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $product = Product::factory()->create(['is_active' => true]);

    $deliveredOrder = makeOrderForUser($customer, 'delivered');
    OrderItem::query()->create([
        'order_id' => $deliveredOrder->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 3,
        'price' => 100000,
    ]);

    $cancelledOrder = makeOrderForUser($customer, 'cancelled');
    OrderItem::query()->create([
        'order_id' => $cancelledOrder->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 4,
        'price' => 100000,
    ]);

    $service = app(CustomerProductService::class);
    $details = $service->getProductDetails($product, $customer);

    expect((int) ($details['product']->total_purchased ?? 0))->toBe(3);
});
