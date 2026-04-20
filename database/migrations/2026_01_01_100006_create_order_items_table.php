<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel detail item dalam pesanan (line items)
     *
     * PENTING: Harga disalin ke sini saat checkout!
     * Alasan: Jika harga produk berubah di kemudian hari,
     * histori order harus tetap menunjukkan harga SAAT BELI.
     *
     * Relasi: orders hasMany order_items, order_items belongsTo products
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');                     // Item terhapus jika order dihapus
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');                    // Tidak hapus produk jika ada di order
            $table->string('product_name');                  // Nama produk saat beli (snapshot)
            $table->unsignedInteger('quantity');             // Jumlah beli
            $table->decimal('price', 12, 2);                 // Harga per unit SAAT BELI (bukan harga sekarang)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
