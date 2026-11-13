@extends('layouts.app')

@section('title', 'Semua Produk - Toko Sparepart Motor')

@section('content')
    <div class="container">
        @php
            $currentKeyword = request()->string('q')->toString();
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h5 fw-semibold mb-0">Semua Produk</h1>
            <span class="text-muted small">{{ $products->total() }} item</span>
        </div>

        <x-category-filter :categories="$categories" :selected-category-id="$selectedCategoryId"
            route-name="products.index" :keyword="$currentKeyword" />

        <div class="row g-3">
            @forelse($products as $product)
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
                            <div class="text-danger fw-bold">Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                            </div>
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
                <div class="col-12 text-muted">Tidak ada produk yang cocok dengan pencarian Anda.</div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
