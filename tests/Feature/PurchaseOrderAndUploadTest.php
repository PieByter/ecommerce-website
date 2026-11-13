<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('receiving an ordered purchase order increases stock once', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    $purchaseOrder = PurchaseOrder::factory()->create([
        'supplier_id' => $supplier->id,
        'status' => PurchaseOrder::STATUS_ORDERED,
    ]);

    $purchaseOrder->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 3,
        'unit_cost' => 10000,
        'line_total' => 30000,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.receive', $purchaseOrder))
        ->assertSessionHas('success');

    expect($purchaseOrder->fresh()->status)->toBe(PurchaseOrder::STATUS_RECEIVED)
        ->and((int) $product->fresh()->stock)->toBe(8)
        ->and($purchaseOrder->fresh()->received_at)->not->toBeNull();

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.receive', $purchaseOrder))
        ->assertSessionHas('error');

    expect((int) $product->fresh()->stock)->toBe(8);
});

test('draft purchase order cannot be received', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $purchaseOrder = PurchaseOrder::factory()->create([
        'supplier_id' => $supplier->id,
        'status' => PurchaseOrder::STATUS_DRAFT,
    ]);

    $purchaseOrder->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'unit_cost' => 10000,
        'line_total' => 20000,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.purchase-orders.receive', $purchaseOrder))
        ->assertSessionHas('error');

    expect($purchaseOrder->fresh()->status)->toBe(PurchaseOrder::STATUS_DRAFT)
        ->and((int) $product->fresh()->stock)->toBe(5);
});

test('customer can upload a payment proof for a pending order', function () {
    Storage::fake('public');

    $customer = User::factory()->customer()->create();
    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'status' => Order::STATUS_PENDING,
        'payment_proof' => null,
        'paid_at' => null,
    ]);

    $response = $this->actingAs($customer)
        ->post(route('customer.orders.payment-proof', $order), [
            'payment_proof' => UploadedFile::fake()->image('proof.jpg', 600, 400),
        ]);

    $response->assertRedirect(route('customer.orders.show', $order));

    $order->refresh();

    expect($order->payment_proof)->not->toBeNull()
        ->and($order->paid_at)->not->toBeNull();

    Storage::disk('public')->assertExists($order->payment_proof);
});

test('product category and supplier cannot be deleted while still in use', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $product->suppliers()->syncWithoutDetaching([$supplier->id]);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertSessionHas('error');

    $this->actingAs($admin)
        ->delete(route('admin.suppliers.destroy', $supplier))
        ->assertSessionHas('error');

    expect(Category::query()->whereKey($category->id)->exists())->toBeTrue()
        ->and(Supplier::query()->whereKey($supplier->id)->exists())->toBeTrue();
});
