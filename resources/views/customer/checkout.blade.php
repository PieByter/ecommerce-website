@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="container">
        <h1 class="h5 fw-semibold mb-3">Checkout</h1>

        @if ($errors->has('cart'))
            <div class="alert alert-danger">{{ $errors->first('cart') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Alamat & Pengiriman</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('checkout.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Pengiriman</label>
                                <textarea name="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" rows="4"
                                    placeholder="Masukkan alamat pengiriman lengkap" required>{{ old('shipping_address', auth()->user()?->address) }}</textarea>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kurir</label>
                                <select name="courier" class="form-select @error('courier') is-invalid @enderror">
                                    <option value="">Pilih kurir</option>
                                    <option value="JNE" @selected(old('courier') === 'JNE')>JNE</option>
                                    <option value="J&T" @selected(old('courier') === 'J&T')>J&T</option>
                                    <option value="SiCepat" @selected(old('courier') === 'SiCepat')>SiCepat</option>
                                    <option value="Pos Indonesia" @selected(old('courier') === 'Pos Indonesia')>Pos Indonesia</option>
                                    <option value="Gosend" @selected(old('courier') === 'Gosend')>Gosend</option>
                                    <option value="Grab Express" @selected(old('courier') === 'Grab Express')>Grab Express</option>
                                </select>
                                @error('courier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Layanan Kurir</label>
                                <input type="text" name="courier_service"
                                    class="form-control @error('courier_service') is-invalid @enderror"
                                    value="{{ old('courier_service') }}" placeholder="Contoh: REG">
                                @error('courier_service')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan (opsional)</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                    placeholder="Contoh: Titip di satpam jika rumah kosong">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">Kembali ke Cart</a>
                                <button type="submit" class="btn btn-danger">Buat Pesanan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Ringkasan Pesanan</div>
                    <div class="card-body">
                        <div class="small text-muted mb-2">Item</div>
                        @foreach ($cartItems as $item)
                            @php
                                $itemSubtotal = (float) ($item->product?->price ?? 0) * (int) $item->quantity;
                            @endphp
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $item->product?->name ?? 'Produk tidak tersedia' }}</div>
                                    <div class="small text-muted">{{ $item->quantity }} x Rp
                                        {{ number_format((float) ($item->product?->price ?? 0), 0, ',', '.') }}</div>
                                </div>
                                <div class="fw-semibold">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</div>
                            </div>
                        @endforeach

                        <hr>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ongkir</span>
                            <span>Rp {{ number_format((float) $shippingCost, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-danger">
                            <span>Total</span>
                            <span>Rp {{ number_format((float) $grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
