# Sparepart Motor

Aplikasi e-commerce sparepart motor berbasis Laravel 13 dan Bootstrap 5. Aplikasi memiliki katalog publik, keranjang, checkout, riwayat pesanan, unggah bukti pembayaran, review produk, serta panel admin untuk kategori, supplier, produk, pesanan penjualan, purchase order, customer, dan admin.

## Fitur utama

- Katalog publik dengan pencarian, filter kategori (termasuk subkategori), dan rating produk.
- Keranjang tersimpan di database dengan validasi stok.
- Checkout aman menggunakan transaksi database dan row locking.
- Riwayat pesanan, pembatalan pesanan pending, dan unggah bukti pembayaran.
- Review hanya untuk produk yang sudah dibeli dan pesanannya berstatus selesai.
- Panel admin:
  - Dashboard statistik dan pesanan terbaru.
  - CRUD kategori bertingkat dengan urutan tampil.
  - CRUD supplier, produk, dan relasi produk-supplier.
  - Manajemen pesanan dengan validasi transisi status.
  - Purchase order dengan penerimaan barang yang menambah stok secara otomatis.
  - Manajemen data customer dan admin.

## Kebutuhan

- PHP 8.4+
- Composer
- Node.js dan npm
- MySQL 8+ (atau SQLite untuk pengembangan/testing)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database pada `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Atau gunakan script setup Composer:

```bash
composer run setup
```

Untuk mode pengembangan (server, queue, dan Vite):

```bash
composer run dev
```

Di Windows, `start-dev.bat` dapat digunakan untuk membuka server Laravel dan Vite secara bersamaan.

## Akun demo

Setelah `php artisan migrate --seed`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `test1234` |
| Customer | `customer@example.com` | `test1234` |

## Pengujian

```bash
php artisan test
```

Test menggunakan SQLite in-memory sesuai konfigurasi `phpunit.xml`.

## Struktur singkat

- `app/Http/Controllers` — controller admin, customer, dan autentikasi.
- `app/Services` — logika bisnis checkout, pesanan, cart, produk, kategori, dan purchase order.
- `app/Models` — model Eloquent beserta konstanta status dan helper tampilan.
- `database/migrations` — skema database dan index performa.
- `resources/views` — halaman Blade admin, customer, auth, dan komponen reusable.
- `resources/js/app.js` — interaksi UI (supplier picker, preview gambar, quantity control, form PO).
