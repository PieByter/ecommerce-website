@extends('layouts.app')

@section('title', 'Beranda - Toko Sparepart Motor')

@section('content')
    <div class="container">
        <div class="p-4 p-md-5 rounded-4 text-white mb-4" style="background: linear-gradient(135deg,#ef4444,#f97316);">
            <h1 class="h3 fw-bold mb-2">Cari Sparepart Motor Jadi Lebih Cepat</h1>
            <p class="mb-0">Belanja mudah untuk kebutuhan bengkel dan motor harian Anda.</p>
        </div>

        <div class="mb-4">
            <h2 class="h5 fw-semibold mb-3">Kategori Populer</h2>
            <x-category-filter :categories="$categories" :selected-category-id="$selectedCategoryId"
                route-name="customer.home" />
        </div>

        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-semibold mb-0">Produk Terbaru</h2>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
            </div>
            <div class="row g-3">
                @forelse($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @php
                            $ratingStars = max(0, min(5, (int) round((float) ($product->avg_rating ?? 0))));
                            $cartQuantity = (int) ($cartQuantities[$product->id] ?? 0);
                            $isOutOfStock = $product->isOutOfStock();
                        @endphp
                        <div class="card h-100 border-0 shadow-sm">
                            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-reset">
                                <div class="position-relative">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="card-img-top {{ $isOutOfStock ? 'opacity-50' : '' }}"
                                        style="height: 180px; object-fit: cover;"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                                    @if ($isOutOfStock)
                                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                            style="background: rgba(0, 0, 0, 0.35);">
                                            <span class="badge rounded-pill text-bg-danger px-3 py-2">Barang Kosong</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="card-body">
                                <div class="small text-muted">{{ $product->category?->name }}</div>
                                <div class="fw-semibold mb-2">
                                    <a href="{{ route('products.show', $product->slug) }}"
                                        class="text-decoration-none text-reset">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="text-danger fw-bold">Rp
                                    {{ number_format((float) $product->price, 0, ',', '.') }}</div>
                                <div class="small text-muted mt-1">Stok: {{ $product->stock }}</div>
                                <div class="small text-warning d-flex align-items-center gap-1 mt-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $ratingStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0">
                                <x-cart-controls :product="$product" :cart-quantity="$cartQuantity"
                                    :is-out-of-stock="$isOutOfStock" />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">Belum ada produk aktif.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
