@extends('layouts.app')

@section('title', 'Semua Produk - Toko Sparepart Motor')

@section('content')
    <div class="container">
        @php
            $activeCategoryId = (int) ($selectedCategoryId ?? 0);
            $currentKeyword = request()->string('q')->toString();
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h5 fw-semibold mb-0">Semua Produk</h1>
            <span class="text-muted small">{{ $products->total() }} item</span>
        </div>

        @php
            $quickCategories = $categories->take(9);
            $otherCategories = $categories->skip(9);
            $selectedOutsideQuickCategory = $otherCategories->firstWhere('id', $activeCategoryId);
        @endphp

        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
            <span class="small text-muted">Filter kategori:</span>
            <a href="{{ route('products.index', array_filter(['q' => $currentKeyword], fn($value) => $value !== '')) }}"
                class="badge rounded-pill text-decoration-none px-3 py-2 {{ $activeCategoryId === 0 ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                Semua
            </a>
            @foreach ($quickCategories as $category)
                <a href="{{ route('products.index', array_filter(['q' => $currentKeyword, 'category' => $category->id], fn($value) => $value !== '')) }}"
                    class="badge rounded-pill text-decoration-none px-3 py-2 {{ $activeCategoryId === (int) $category->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
                    {{ $category->name }}
                </a>
            @endforeach

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
                                <a href="{{ route('products.index', array_filter(['q' => $currentKeyword, 'category' => $category->id], fn($value) => $value !== '')) }}"
                                    class="badge rounded-pill text-decoration-none px-3 py-2 {{ $activeCategoryId === (int) $category->id ? 'text-bg-danger' : 'text-bg-light border text-dark' }}">
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

        <div class="row g-3">
            @forelse($products as $product)
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
                        $isInactive = !$product->is_active;
                        $isOutOfStock = (int) $product->stock <= 0;
                        $isUnavailable = $isInactive || $isOutOfStock;
                        $quantityMax = max($cartQuantity, (int) $product->stock);
                    @endphp
                    <div class="card h-100 border-0 shadow-sm">
                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-reset">
                            <div class="position-relative">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                    class="card-img-top {{ $isOutOfStock ? 'opacity-50' : '' }}"
                                    style="height: 180px; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x450?text=No+Image';">
                                @if ($isOutOfStock)
                                    <div class="position-absolute top-0 inset-s-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                        style="background: rgba(0, 0, 0, 0.35);">
                                        <span class="badge rounded-pill text-bg-danger px-3 py-2">Barang Kosong</span>
                                    </div>
                                @endif
                                @if ($isInactive)
                                    <div class="position-absolute top-0 inset-s-0 m-2">
                                        <span class="badge text-bg-danger">Tidak Aktif</span>
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
                            <div class="small text-muted mt-2">Stok: {{ $product->stock }}</div>
                            @if ($isOutOfStock)
                                <div class="small text-danger fw-semibold">Barang Kosong</div>
                            @endif
                            @if ($isInactive)
                                <div class="small text-danger fw-semibold">Produk tidak aktif</div>
                            @endif
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
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        @disabled($isUnavailable)>-</button>
                                </form>

                                <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="action" value="set">
                                    <input type="number" name="quantity" class="form-control form-control-sm text-center"
                                        value="{{ $cartQuantity }}" min="0" max="{{ $quantityMax }}"
                                        style="width: 88px;" aria-label="Jumlah item di keranjang"
                                        onchange="this.form.submit()" @disabled($isUnavailable)>
                                </form>

                                <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="action" value="increment">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        @disabled($isUnavailable)>+</button>
                                </form>
                            </div>
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
