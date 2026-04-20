@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
    <div class="container">
        <h1 class="h5 fw-semibold mb-3">Keranjang Belanja</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->has('cart'))
            <div class="alert alert-danger">{{ $errors->first('cart') }}</div>
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
                        @php $grandTotal = 0; @endphp
                        @forelse($cartItems as $item)
                            @php
                                $subtotal = (float) ($item->product?->price ?? 0) * (int) $item->quantity;
                                $grandTotal += $subtotal;
                                $stockLimit = max(1, (int) ($item->product?->stock ?? 1));
                                $rawImage = trim((string) ($item->product?->image ?? ''));
                                $imageUrl =
                                    $rawImage === ''
                                        ? 'https://placehold.co/200x200?text=No+Image'
                                        : (str_starts_with($rawImage, 'http://') ||
                                        str_starts_with($rawImage, 'https://')
                                            ? $rawImage
                                            : asset($rawImage));
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <img src="{{ $imageUrl }}" alt="{{ $item->product?->name ?? 'Produk' }}"
                                        style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';">
                                </td>
                                <td>{{ $item->product?->name ?? 'Produk tidak tersedia' }}</td>
                                <td>Rp {{ number_format((float) ($item->product?->price ?? 0), 0, ',', '.') }}</td>
                                <td>
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
                    <a href="{{ route('checkout.index') }}" class="btn btn-danger">Lanjut Checkout</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const controls = document.querySelectorAll('.js-qty-control');

            controls.forEach(function(control) {
                const container = control.closest('td');
                const input = control.querySelector('.js-qty-input');
                const decButton = control.querySelector('.js-qty-dec');
                const incButton = control.querySelector('.js-qty-inc');
                const form = container?.querySelector('.js-qty-form');
                const hiddenInput = container?.querySelector('.js-qty-hidden');

                if (!input || !decButton || !incButton || !form || !hiddenInput) {
                    return;
                }

                const min = Number(control.getAttribute('data-min') || '1');
                const max = Number(control.getAttribute('data-max') || '999999');

                const submitQuantity = function(nextValue) {
                    const clampedValue = Math.max(min, Math.min(max, Number(nextValue || min)));
                    input.value = String(clampedValue);
                    hiddenInput.value = String(clampedValue);
                    form.submit();
                };

                decButton.addEventListener('click', function() {
                    submitQuantity(Number(input.value) - 1);
                });

                incButton.addEventListener('click', function() {
                    submitQuantity(Number(input.value) + 1);
                });

                input.addEventListener('change', function() {
                    submitQuantity(Number(input.value));
                });
            });
        });
    </script>
@endsection
