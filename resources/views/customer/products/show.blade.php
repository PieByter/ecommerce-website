@extends('layouts.app')

@section('title', $product->name . ' - Toko Sparepart Motor')

@section('content')
    <div class="container">
        @php
            $rawImage = trim((string) $product->image);
            $imageUrl =
                $rawImage === ''
                    ? 'https://placehold.co/900x700?text=No+Image'
                    : (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')
                        ? $rawImage
                        : asset($rawImage));
            $soldCount = (int) ($product->total_purchased ?? 0);
            $averageRating = (float) ($product->avg_rating ?? 0);
            $averageRatingStars = max(0, min(5, (int) round($averageRating)));
            $reviewsCount = $product->reviews->count();
            $isOutOfStock = (int) $product->stock <= 0;
        @endphp

        <div class="mb-3">
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Produk</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                            class="card-img-top {{ $isOutOfStock ? 'opacity-50' : '' }}"
                            style="height: 420px; object-fit: cover;"
                            onerror="this.onerror=null;this.src='https://placehold.co/900x700?text=No+Image';">
                        @if ($isOutOfStock)
                            <div class="position-absolute top-0 inset-s-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                style="background: rgba(0, 0, 0, 0.35);">
                                <span class="badge rounded-pill text-bg-danger px-3 py-2">Barang Kosong</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted mb-1">{{ $product->category?->name }}</div>
                        <h1 class="h4 fw-bold mb-2">{{ $product->name }}</h1>
                        <div class="text-danger fw-bold fs-4 mb-3">Rp
                            {{ number_format((float) $product->price, 0, ',', '.') }}</div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge text-bg-light border">Stok: {{ $product->stock }}</span>
                            @if ($isOutOfStock)
                                <span class="badge text-bg-danger">Barang Kosong</span>
                            @endif
                            <span class="badge text-bg-light border">Terjual:
                                {{ number_format($soldCount, 0, ',', '.') }}</span>
                            <span class="badge text-bg-light border d-inline-flex align-items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="bi {{ $i <= $averageRatingStars ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                @endfor
                            </span>
                            <span class="badge text-bg-light border">{{ $reviewsCount }} ulasan</span>
                            <span class="badge text-bg-light border">Di cart:
                                {{ number_format((int) ($cartQuantity ?? 0), 0, ',', '.') }} item</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="action" value="decrement">
                                <button type="submit" class="btn btn-outline-secondary"
                                    @disabled($cartQuantity <= 0)>-</button>
                            </form>
                            <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="action" value="set">
                                <label for="cart-quantity" class="visually-hidden">Jumlah di cart</label>
                                <input id="cart-quantity" type="number" name="quantity" class="form-control text-center"
                                    value="{{ (int) ($cartQuantity ?? 0) }}" min="0"
                                    max="{{ (int) $product->stock }}" style="width: 96px;"
                                    onchange="this.form.requestSubmit()">
                            </form>
                            <form method="POST" action="{{ route('cart.adjust', $product) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="action" value="increment">
                                <button type="submit" class="btn btn-danger" @disabled($isOutOfStock)>+</button>
                            </form>
                        </div>

                        <h2 class="h6 fw-semibold mb-2">Deskripsi</h2>
                        <p class="text-muted mb-0">{{ $product->description ?: 'Belum ada deskripsi produk.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" id="review-form">
            <div class="card-body">
                @auth
                    <h2 class="h5 fw-semibold mb-3">Tulis Review Kamu</h2>
                    @if ($canReview)
                        <form method="POST" action="{{ route('products.reviews.store', $product->slug) }}" class="mb-4">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1">Rating Bintang</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input" type="radio" name="rating"
                                                id="rating{{ $i }}" value="{{ $i }}"
                                                @checked((int) old('rating', $myReview?->rating) === $i)>
                                            <label class="form-check-label" for="rating{{ $i }}">
                                                {{ $i }}
                                                <i class="bi bi-star-fill text-warning"></i>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Komentar</label>
                                <textarea name="comment" class="form-control" rows="3" maxlength="2000"
                                    placeholder="Tulis pengalaman kamu menggunakan produk ini...">{{ old('comment', $myReview?->comment) }}</textarea>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">
                                {{ $myReview ? 'Update Review' : 'Kirim Review' }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-light border mb-4">
                            Kamu bisa memberi review setelah membeli produk ini dan status pesanan sudah
                            <strong>delivered</strong>.
                        </div>
                    @endif
                @endauth

                <h2 class="h5 fw-semibold mb-3">Rating & Komentar Pengguna</h2>

                @forelse($product->reviews as $review)
                    @php
                        $reviewStars = max(0, min(5, (int) round((float) $review->rating)));
                    @endphp
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-semibold">{{ $review->user?->name ?? 'Pengguna' }}</div>
                            <div class="text-warning d-flex align-items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $reviewStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <div class="text-muted">{{ $review->comment ?: 'Tidak ada komentar.' }}</div>
                    </div>
                @empty
                    <div class="text-muted">Belum ada ulasan untuk produk ini.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
