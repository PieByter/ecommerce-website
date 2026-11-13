<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title($this->faker->words(3, true));
        $categoryId = Category::query()->whereNotNull('parent_id')->inRandomOrder()->value('id')
            ?? Category::query()->firstOrCreate(
                ['slug' => 'lain-lain'],
                ['name' => 'Lain-lain', 'parent_id' => null, 'sort_order' => 999],
            )->id;

        return [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $this->faker->unique()->slug(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10000, 5000000),
            'stock' => $this->faker->numberBetween(0, 250),
            'weight' => $this->faker->randomFloat(2, 50, 5000),
            'image' => null,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $supplierIds = Supplier::query()
                ->inRandomOrder()
                ->limit(2)
                ->pluck('id');

            if ($supplierIds->isEmpty()) {
                $supplierIds = Supplier::factory(2)->create()->pluck('id');
            }

            $product->suppliers()->syncWithoutDetaching($supplierIds);
        });
    }
}
