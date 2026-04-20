<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $status = fake()->randomElement($statuses);

        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'total_price' => $this->faker->randomFloat(2, 50000, 5000000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 100000),
            'shipping_address' => $this->faker->address(),
            'courier' => $this->faker->randomElement(['JNE', 'J&T', 'SiCepat', 'AnterAja', 'Pos Indonesia', 'Ninja Xpress', 'GoSend', 'GrabExpress']),
            'courier_service' => $this->faker->randomElement(['REG', 'YES', 'ECO']),
            'tracking_number' => in_array($status, ['shipped', 'delivered'], true) ? strtoupper($this->faker->bothify('??##########')) : null,
            'payment_proof' => $this->faker->lexify('payments/??????????.jpg'),
            'notes' => $this->faker->sentence(),
            'paid_at' => in_array($status, ['confirmed', 'processing', 'shipped', 'delivered'], true)
                ? $this->faker->dateTimeBetween('-30 days', 'now')
                : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order): void {
            $items = OrderItem::factory(random_int(1, 4))->create([
                'order_id' => $order->id,
            ]);

            $subtotal = $items->sum(function (OrderItem $item): float {
                return (float) $item->price * $item->quantity;
            });

            $order->forceFill([
                'total_price' => $subtotal + (float) $order->shipping_cost,
            ])->saveQuietly();
        });
    }
}
