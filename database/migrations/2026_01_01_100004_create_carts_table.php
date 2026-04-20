<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel keranjang belanja (persistent - tersimpan di DB, bukan hanya session)
     * Keuntungan: keranjang tidak hilang walau logout, bisa sync antar device
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');                     // Cart terhapus jika user dihapus
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');                     // Cart terhapus jika produk dihapus
            $table->unsignedInteger('quantity')->default(1); // Jumlah item
            $table->timestamps();

            // Satu user tidak boleh punya 2 baris untuk produk yang sama
            // Jika tambah produk yang sama → update quantity saja
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
