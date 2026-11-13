<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_ORDERED,
            PurchaseOrder::STATUS_RECEIVED,
            PurchaseOrder::STATUS_CANCELLED,
        ]);

        $orderDate = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'supplier_id' => Supplier::factory(),
            'po_number' => 'PO-'.$orderDate->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'order_date' => $orderDate,
            'expected_date' => $this->faker->dateTimeBetween($orderDate, '+14 days'),
            'total_cost' => $this->faker->randomFloat(2, 100000, 10000000),
            'notes' => $this->faker->optional()->sentence(),
            'received_at' => $status === PurchaseOrder::STATUS_RECEIVED
                ? $this->faker->dateTimeBetween($orderDate, 'now')
                : null,
        ];
    }
}
