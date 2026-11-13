<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(Order::STATUSES);

        return [
            'user_id' => User::factory()->customer(),
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'total_price' => $this->faker->randomFloat(2, 50000, 5000000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 100000),
            'shipping_address' => $this->faker->address(),
            'courier' => $this->faker->randomElement(['JNE', 'J&T', 'SiCepat', 'AnterAja', 'Pos Indonesia', 'Ninja Xpress', 'GoSend', 'GrabExpress']),
            'courier_service' => $this->faker->randomElement(['REG', 'YES', 'ECO']),
            'tracking_number' => in_array($status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED], true)
                ? strtoupper($this->faker->bothify('??##########'))
                : null,
            'payment_proof' => in_array($status, Order::PAID_STATUSES, true)
                ? 'payments/'.$this->faker->lexify('??????????').'.jpg'
                : null,
            'notes' => $this->faker->sentence(),
            'paid_at' => in_array($status, Order::PAID_STATUSES, true)
                ? $this->faker->dateTimeBetween('-30 days', 'now')
                : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order): void {
            $productIds = Product::query()
                ->inRandomOrder()
                ->limit(random_int(1, 4))
                ->pluck('id');

            $items = collect();

            if ($productIds->isEmpty()) {
                $items = OrderItem::factory(random_int(1, 4))->create([
                    'order_id' => $order->id,
                ]);
            } else {
                foreach ($productIds as $productId) {
                    $product = Product::query()->find($productId);

                    if (! $product instanceof Product) {
                        continue;
                    }

                    $quantity = random_int(1, 3);

                    $items->push(OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => (float) $product->price,
                    ]));
                }
            }

            $subtotal = $items->sum(function (OrderItem $item): float {
                return (float) $item->price * (int) $item->quantity;
            });

            $order->forceFill(['total_price' => $subtotal])->saveQuietly();
        });
    }
}
