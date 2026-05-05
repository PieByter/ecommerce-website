<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('products')
            ->whereNotNull('supplier_id')
            ->orderBy('id')
            ->chunkById(200, function ($products): void {
                $now = now();
                $rows = $products->map(function ($product) use ($now): array {
                    return [
                        'product_id' => $product->id,
                        'supplier_id' => $product->supplier_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                if ($rows !== []) {
                    DB::table('product_supplier')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('product_supplier')->delete();
    }
};
