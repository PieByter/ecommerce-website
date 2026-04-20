@extends('layouts.app')

@section('title', 'Riwayat Review Saya')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h5 fw-semibold mb-0">Riwayat Review Saya</h1>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Tulis Review Lagi</a>
        </div>

        <div class="row g-3">
            @forelse($reviews as $review)
                @php
                    $reviewStars = max(0, min(5, (int) $review->rating));
                    $product = $review->product;
                    $productLink = $product ? route('products.show', $product->slug) : '#';
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="small text-muted">Produk</div>
                                    @if ($product)
                                        <a href="{{ $productLink }}" class="fw-semibold text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    @else
                                        <span class="fw-semibold text-muted">Produk tidak tersedia</span>
                                    @endif
                                </div>
                                <div class="small text-muted text-end">{{ $review->created_at?->format('d M Y H:i') }}</div>
                            </div>

                            <div class="text-warning d-flex align-items-center gap-1 mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $reviewStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>

                            <div class="text-muted">{{ $review->comment ?: 'Tidak ada komentar.' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-muted">Kamu belum pernah memberikan review produk.</div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($reviews->hasPages())
            <div class="mt-3">{{ $reviews->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
