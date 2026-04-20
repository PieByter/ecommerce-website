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
            <div class="row g-3">
                @forelse($categories as $category)
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="small text-muted">Kategori</div>
                                <div class="fw-semibold">{{ $category->name }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">Belum ada kategori.</div>
                @endforelse
            </div>
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
                            $rawImage = trim((string) $product->image);
                            $imageUrl =
                                $rawImage === ''
                                    ? 'https://placehold.co/600x450?text=No+Image'
                                    : (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')
                                        ? $rawImage
                                        : asset($rawImage));
                            $averageRating = (float) ($product->avg_rating ?? 0);
                            $ratingStars = max(0, min(5, (int) round($averageRating)));
                            $cartQuantity = (int) ($cartQuantities[$product->id] ?? 0);
                        @endphp
                        <div class="card h-100 border-0 shadow-sm">
                            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-reset">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="card-img-top"
                                    style="height: 180px; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x450?text=No+Image';">
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
                                <div class="small text-warning d-flex align-items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $ratingStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0">
                                <div class="d-flex align-items-center gap-2">
                                    <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="action" value="decrement">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                    </form>
                                    <div class="small text-muted flex-grow-1 text-center">Di cart: {{ $cartQuantity }}
                                    </div>
                                    <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="action" value="increment">
                                        <button type="submit" class="btn btn-sm btn-danger">+</button>
                                    </form>
                                </div>
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
