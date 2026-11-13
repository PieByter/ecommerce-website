@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
    <div class="container">
        <h1 class="h5 fw-semibold mb-3">Keranjang Belanja</h1>

        @if ($errors->has('cart'))
            <div class="alert alert-danger">{{ $errors->first('cart') }}</div>
        @endif

        @php
            $hasUnavailableItems = $cartItems->contains(function ($item): bool {
                return ! $item->product
                    || ! $item->product->is_active
                    || $item->product->stock < $item->quantity;
            });
            $grandTotal = 0;
        @endphp

        @if ($hasUnavailableItems)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Ada produk yang sudah tidak tersedia atau melebihi stok. Hapus/sesuaikan item tersebut sebelum checkout.
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cartItems as $item)
                            @php
                                $product = $item->product;
                                $subtotal = (float) ($product?->price ?? 0) * (int) $item->quantity;
                                $grandTotal += $subtotal;
                                $isInactive = ! $product || ! $product->is_active;
                                $isOverStock = $product && $product->stock < $item->quantity;
                                $isUnavailable = $isInactive || $isOverStock || ($product && $product->stock <= 0);
                                $stockLimit = max(1, (int) ($product?->stock ?? 1));
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($product)
                                        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none"
                                            aria-label="Lihat detail produk">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                                loading="lazy"
                                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                                        </a>
                                    @else
                                        <img src="{{ asset('images/placeholder.svg') }}" alt="Produk tidak tersedia"
                                            style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                            loading="lazy">
                                    @endif
                                </td>
                                <td>
                                    @if ($product)
                                        <a href="{{ route('products.show', $product->slug) }}"
                                            class="text-decoration-none text-dark fw-semibold">
                                            {{ $product->name }}
                                        </a>
                                        @if ($isInactive)
                                            <div class="small text-danger">Produk tidak aktif</div>
                                        @elseif ($isOverStock)
                                            <div class="small text-danger">Melebihi stok tersedia ({{ $product->stock }})</div>
                                        @elseif ($product->stock <= 0)
                                            <div class="small text-danger">Stok habis</div>
                                        @endif
                                    @else
                                        Produk tidak tersedia
                                    @endif
                                </td>
                                <td>Rp {{ number_format((float) ($product?->price ?? 0), 0, ',', '.') }}</td>
                                <td>
                                    @if (! $isUnavailable)
                                        <div class="d-inline-flex align-items-center gap-2 js-qty-control" data-min="1"
                                            data-max="{{ $stockLimit }}">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary js-qty-dec">-</button>
                                            <input type="number" class="form-control form-control-sm text-center js-qty-input"
                                                value="{{ $item->quantity }}" min="1" max="{{ $stockLimit }}"
                                                style="width:72px;">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary js-qty-inc">+</button>
                                        </div>

                                        <form method="POST" action="{{ route('cart.update', $item) }}"
                                            class="d-none js-qty-form">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity }}"
                                                class="js-qty-hidden">
                                        </form>
                                    @else
                                        <span class="text-muted">{{ $item->quantity }} item</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Keranjang Anda masih kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <span class="fw-semibold">Total</span>
                <span class="fw-bold text-danger">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>

            @if ($cartItems->isNotEmpty())
                <div class="card-footer bg-white border-top-0 text-end">
                    @if ($hasUnavailableItems)
                        <button type="button" class="btn btn-danger" disabled>Lanjut Checkout</button>
                    @else
                        <a href="{{ route('checkout.index') }}" class="btn btn-danger">Lanjut Checkout</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
