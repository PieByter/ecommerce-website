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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title($this->faker->words(3, true));
        $categoryId = Category::query()->inRandomOrder()->value('id')
            ?? Category::query()->firstOrCreate(
                ['slug' => 'lain-lain'],
                ['name' => 'Lain-lain', 'parent_id' => null, 'sort_order' => 999],
            )->id;

        return [
            'category_id' => $categoryId,
            'supplier_id' => Supplier::factory(),
            'name' => $name,
            'slug' => $this->faker->unique()->slug(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10000, 5000000),
            'stock' => $this->faker->numberBetween(0, 250),
            'weight' => $this->faker->randomFloat(2, 50, 5000),
            'image' => $this->faker->imageUrl(800, 800, 'transport', true),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
