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
            @php
                $quickCategories = $categories->take(9);
                $otherCategories = $categories->skip(9);
                $selectedOutsideQuickCategory = $otherCategories->firstWhere('id', (int) $selectedCategoryId);
            @endphp
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('customer.home') }}"
                    class="badge rounded-pill text-decoration-none px-3 py-2 {{ !$selectedCategoryId ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                    Semua
                </a>
                @forelse($quickCategories as $category)
                    <a href="{{ route('customer.home', ['category' => $category->id]) }}"
                        class="badge rounded-pill text-decoration-none px-3 py-2 {{ (int) $selectedCategoryId === (int) $category->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                        {{ $category->name }}
                    </a>
                @empty
                    <span class="text-muted">Belum ada kategori.</span>
                @endforelse

                @if ($otherCategories->isNotEmpty())
                    <div class="dropdown">
                        <button class="badge rounded-pill text-bg-light border text-dark px-3 py-2 dropdown-toggle"
                            style="cursor: pointer;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            +{{ $otherCategories->count() }} lainnya
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-3 border-0"
                            style="width: min(500px, 92vw); max-height: min(500px, 60vh); overflow: auto;">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($otherCategories as $category)
                                    <a href="{{ route('customer.home', ['category' => $category->id]) }}"
                                        class="badge rounded-pill text-decoration-none px-3 py-2 {{ (int) $selectedCategoryId === (int) $category->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($selectedOutsideQuickCategory)
                    <span class="badge rounded-pill text-bg-danger px-3 py-2">
                        {{ $selectedOutsideQuickCategory->name }}
                    </span>
                @endif
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
                            $quantityMax = max($cartQuantity, (int) $product->stock);
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
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="action" value="decrement">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                    </form>

                                    <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="action" value="set">
                                        <input type="number" name="quantity"
                                            class="form-control form-control-sm text-center" value="{{ $cartQuantity }}"
                                            min="0" max="{{ $quantityMax }}" style="width: 88px;"
                                            aria-label="Jumlah item di keranjang" onchange="this.form.submit()">
                                    </form>

                                    <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="action" value="increment">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            @disabled((int) $product->stock <= 0)>+</button>
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
