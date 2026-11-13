@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $order->order_number)

@section('content')
    <div class="container">
        @php
            $steps = [
                \App\Models\Order::STATUS_PENDING => 'Menunggu',
                \App\Models\Order::STATUS_CONFIRMED => 'Dikonfirmasi',
                \App\Models\Order::STATUS_PROCESSING => 'Diproses',
                \App\Models\Order::STATUS_SHIPPED => 'Dikirim',
                \App\Models\Order::STATUS_DELIVERED => 'Selesai',
            ];
            $currentStepIndex = array_search($order->status, array_keys($steps), true);
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h5 mb-1">Pesanan {{ $order->order_number }}</h1>
                <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
            </div>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        @if ($errors->has('order'))
            <div class="alert alert-danger">{{ $errors->first('order') }}</div>
        @endif

        @if ($order->status === \App\Models\Order::STATUS_CANCELLED)
            <div class="alert alert-danger">
                <i class="bi bi-x-circle-fill me-2"></i>Pesanan ini telah dibatalkan.
            </div>
        @else
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between text-center gap-2">
                        @foreach ($steps as $status => $label)
                            @php
                                $stepIndex = array_search($status, array_keys($steps), true);
                            @endphp
                            <div class="flex-fill">
                                <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center
                                    {{ $currentStepIndex !== false && $stepIndex <= $currentStepIndex ? 'bg-danger text-white' : 'bg-light text-muted' }}"
                                    style="width: 32px; height: 32px;">
                                    {{ $stepIndex + 1 }}
                                </div>
                                <div class="small {{ $currentStepIndex !== false && $stepIndex <= $currentStepIndex ? 'fw-semibold' : 'text-muted' }}">
                                    {{ $label }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Item Pesanan</div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $item->product?->image_url ?? asset('images/placeholder.svg') }}"
                                                    alt="{{ $item->product_name }}" class="rounded border"
                                                    style="width: 56px; height: 56px; object-fit: cover;"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                                                <div>
                                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                                    @if ($item->product)
                                                        <a href="{{ route('products.show', $item->product->slug) }}"
                                                            class="small text-decoration-none">Lihat produk</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal Produk</span>
                            <span>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ongkos Kirim</span>
                            <span>
                                @if ((float) $order->shipping_cost > 0)
                                    Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}
                                @else
                                    Gratis
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-danger mt-2">
                            <span>Total</span>
                            <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">Informasi Pengiriman</div>
                    <div class="card-body small">
                        <div class="mb-2">
                            <div class="text-muted">Alamat</div>
                            <div>{{ $order->shipping_address }}</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-muted">Kurir</div>
                                <div>{{ $order->courier ?: '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Layanan</div>
                                <div>{{ $order->courier_service ?: '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">No. Resi</div>
                                <div>{{ $order->tracking_number ?: '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Tanggal</div>
                                <div>{{ $order->created_at?->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        @if ($order->notes)
                            <div class="mt-2">
                                <div class="text-muted">Catatan</div>
                                <div>{{ $order->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Pembayaran</div>
                    <div class="card-body">
                        @if ($order->payment_proof_url)
                            <div class="mb-2">
                                <div class="small text-muted">Bukti pembayaran</div>
                                <a href="{{ $order->payment_proof_url }}" target="_blank" rel="noopener noreferrer">
                                    Lihat bukti pembayaran
                                </a>
                                <div class="small text-muted">
                                    Diunggah {{ $order->paid_at?->format('d M Y H:i') ?? '-' }}
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border py-2 small">
                                Belum ada bukti pembayaran.
                            </div>
                        @endif

                        @if ($order->status === \App\Models\Order::STATUS_PENDING)
                            <form method="POST" action="{{ route('customer.orders.payment-proof', $order) }}"
                                enctype="multipart/form-data" class="mt-3">
                                @csrf
                                <label for="payment_proof" class="form-label fw-semibold">
                                    {{ $order->payment_proof_url ? 'Ganti Bukti Pembayaran' : 'Unggah Bukti Pembayaran' }}
                                </label>
                                <input id="payment_proof" type="file" name="payment_proof"
                                    class="form-control @error('payment_proof') is-invalid @enderror"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                                <div class="form-text">Format JPG/JPEG/PNG/WEBP, maksimal 2MB.</div>
                                @error('payment_proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-danger w-100 mt-2">Unggah</button>
                            </form>
                        @endif

                        @if ($order->canBeCancelledByCustomer())
                            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="mt-3"
                                onsubmit="return confirm('Batalkan pesanan ini?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger w-100">Batalkan Pesanan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
