@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('page_title', 'Detail Pesanan')

@section('content')
    @php
        $paymentProofPath = trim((string) $order->payment_proof);
        $paymentProofUrl =
            $paymentProofPath === ''
                ? null
                : (str_starts_with($paymentProofPath, 'http://') || str_starts_with($paymentProofPath, 'https://')
                    ? $paymentProofPath
                    : asset($paymentProofPath));

        $statusClasses = [
            'pending' => 'text-warning-emphasis bg-warning-subtle',
            'confirmed' => 'text-success-emphasis bg-success-subtle',
            'processing' => 'text-info-emphasis bg-info-subtle',
            'shipped' => 'text-primary-emphasis bg-primary-subtle',
            'delivered' => 'text-secondary-emphasis bg-secondary-subtle',
            'cancelled' => 'text-danger-emphasis bg-danger-subtle',
        ];

        $statusLabels = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        $orderedStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        $statusTextColors = [
            'pending' => '#b7791f',
            'confirmed' => '#1f8a4c',
            'processing' => '#0c8599',
            'shipped' => '#0d6efd',
            'delivered' => '#495057',
            'cancelled' => '#c92a2a',
        ];

        $currentStatus = (string) old('status', $order->status);
    @endphp

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Item Pesanan</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Info Pembeli</div>
                <div class="card-body small">
                    <div><strong>{{ $order->user?->name }}</strong></div>
                    <div>{{ $order->user?->email }}</div>
                    <hr>
                    <div><strong>No. Pesanan:</strong> {{ $order->order_number }}</div>
                    <div>
                        <strong>Status:</strong>
                        <span class="badge {{ $statusClasses[$order->status] ?? 'text-bg-secondary' }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </div>
                    <div><strong>Kurir:</strong> {{ $order->courier ?: '-' }}</div>
                    <div><strong>Layanan Kurir:</strong> {{ $order->courier_service ?: '-' }}</div>
                    <div><strong>No. Resi:</strong> {{ $order->tracking_number ?: '-' }}</div>
                    <div><strong>Waktu Pembayaran:</strong> {{ $order->paid_at?->format('d M Y H:i') ?: '-' }}</div>
                    <div class="mt-2">
                        <strong>Bukti Pembayaran:</strong>
                        @if ($paymentProofUrl)
                            <div class="mt-1">
                                <a href="{{ $paymentProofUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="d-inline-block text-decoration-none">Lihat Bukti Pembayaran</a>
                            </div>
                        @else
                            <span>-</span>
                        @endif
                    </div>
                    <hr>
                    <div><strong>Alamat Pengiriman:</strong></div>
                    <div>{{ $order->shipping_address }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Update Status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" id="statusSelect" class="form-select" required>
                                @foreach ($orderedStatuses as $status)
                                    <option value="{{ $status }}" @selected($currentStatus === $status)
                                        style="color: {{ $statusTextColors[$status] ?? '#212529' }}; font-weight: 600;">
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Kurir</label>
                            <input type="text" name="courier" class="form-control"
                                value="{{ old('courier', $order->courier) }}" placeholder="Contoh: JNE">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Layanan Kurir</label>
                            <input type="text" name="courier_service" class="form-control"
                                value="{{ old('courier_service', $order->courier_service) }}" placeholder="Contoh: REG">
                        </div>
                        <div class="col-12">
                            <label class="form-label">No. Resi</label>
                            <input type="text" name="tracking_number" class="form-control"
                                value="{{ old('tracking_number', $order->tracking_number) }}">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-danger w-100" type="submit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
