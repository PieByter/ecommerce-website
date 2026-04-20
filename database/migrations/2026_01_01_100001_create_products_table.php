<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel produk sparepart motor
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('restrict');                    // Tidak boleh hapus kategori jika ada produk
            $table->string('name');                          // Nama produk: "Kampas Rem Honda Beat"
            $table->string('slug')->unique();                // URL: "kampas-rem-honda-beat"
            $table->text('description')->nullable();         // Deskripsi lengkap produk
            $table->decimal('price', 12, 2);                 // Harga: 15000.00
            $table->unsignedInteger('stock')->default(0);    // Jumlah stok
            $table->decimal('weight', 8, 2)->default(0);     // Berat dalam gram (untuk hitung ongkir)
            $table->string('image')->nullable();             // Foto utama produk
            $table->boolean('is_active')->default(true);     // Aktif/nonaktif produk
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
