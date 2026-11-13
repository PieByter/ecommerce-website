<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('test1234'),
        ]);

        User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('test1234'),
        ]);

        User::factory(10)->customer()->create();
        $this->call(CategorySeeder::class);

        Supplier::factory(8)->create();
        Product::factory(30)->create();

        $customers = User::query()->where('role', User::ROLE_CUSTOMER)->get();
        $products = Product::query()->get();

        if ($customers->isNotEmpty() && $products->isNotEmpty()) {
            $this->seedReviews($customers, $products);
            $this->seedCarts($customers, $products);
        }

        Order::factory(20)->create([
            'user_id' => fn (): int => (int) $customers->random()->id,
        ]);
    }

    /**
     * @param  Collection<int, User>  $customers
     * @param  Collection<int, Product>  $products
     */
    private function seedReviews($customers, $products): void
    {
        $created = 0;

        foreach ($customers as $customer) {
            foreach ($products as $product) {
                if ($created >= 50) {
                    break 2;
                }

                ProductReview::factory()->create([
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                ]);

                $created++;
            }
        }
    }

    /**
     * @param  Collection<int, User>  $customers
     * @param  Collection<int, Product>  $products
     */
    private function seedCarts($customers, $products): void
    {
        $created = 0;

        foreach ($customers as $customer) {
            foreach ($products as $product) {
                if ($created >= 30) {
                    break 2;
                }

                if ($product->stock <= 0) {
                    continue;
                }

                Cart::factory()->create([
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                    'quantity' => min(3, max(1, (int) $product->stock)),
                ]);

                $created++;
            }
        }
    }
}
