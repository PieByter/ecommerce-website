<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel pesanan (header order)
     *
     * Alur status pesanan:
     * pending → confirmed → processing → shipped → delivered → (cancelled kapan saja)
     *
     * - pending     : Pembeli sudah checkout, menunggu konfirmasi admin
     * - confirmed   : Admin konfirmasi pembayaran sudah diterima
     * - processing  : Admin sedang kemas barang
     * - shipped     : Barang sudah dikirim (admin input no resi)
     * - delivered   : Pembeli konfirmasi barang sudah diterima
     * - cancelled   : Dibatalkan (oleh admin atau customer)
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');                    // Jangan hapus user jika ada order
            $table->string('order_number')->unique();        // Nomor unik: "ORD-20260415-0001"
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending');
            $table->decimal('total_price', 12, 2);           // Total harga produk
            $table->decimal('shipping_cost', 10, 2)->default(0); // Ongkos kirim
            $table->text('shipping_address');                // Alamat lengkap pengiriman
            $table->string('courier')->nullable();           // Kurir: "JNE", "J&T", "SiCepat"
            $table->string('courier_service')->nullable();   // Layanan: "REG", "YES", "OKE"
            $table->string('tracking_number')->nullable();   // Nomor resi pengiriman
            $table->string('payment_proof')->nullable();     // Path foto bukti transfer
            $table->text('notes')->nullable();               // Catatan dari customer
            $table->timestamp('paid_at')->nullable();        // Waktu upload bukti bayar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
