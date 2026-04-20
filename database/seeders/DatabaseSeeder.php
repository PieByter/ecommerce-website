<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('test1234'),
        ]);
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'role' => 'customer',
            'password' => bcrypt('test1234'),
        ]);

        User::factory(10)->create();
        $this->call(CategorySeeder::class);

        Supplier::factory(8)->create();
        Product::factory(30)->create();
        ProductReview::factory(50)->create();
        Cart::factory(30)->create();
        Order::factory(20)->create();
    }
}
