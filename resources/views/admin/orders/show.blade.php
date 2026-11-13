@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('page_title', 'Detail Pesanan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 mb-1">{{ $order->order_number }}</h1>
            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Item Pesanan</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada item.</td>
                                </tr>
                            @endforelse
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
                        <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold text-danger mt-2">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Informasi Pengiriman</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Alamat</div>
                            <div>{{ $order->shipping_address }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Kurir</div>
                            <div>{{ $order->courier ?: '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Layanan</div>
                            <div>{{ $order->courier_service ?: '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">No. Resi</div>
                            <div>{{ $order->tracking_number ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Catatan Pembeli</div>
                            <div>{{ $order->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Info Pembeli</div>
                <div class="card-body small">
                    <div class="fw-semibold">{{ $order->user?->name }}</div>
                    <div>{{ $order->user?->email }}</div>
                    <div>{{ $order->user?->phone ?: '-' }}</div>
                    <hr>
                    <div><strong>Tanggal Pesanan:</strong> {{ $order->created_at?->format('d M Y H:i') }}</div>
                    <div><strong>Waktu Pembayaran:</strong> {{ $order->paid_at?->format('d M Y H:i') ?: '-' }}</div>
                    <div class="mt-2">
                        <strong>Bukti Pembayaran:</strong>
                        @if ($order->payment_proof_url)
                            <a href="{{ $order->payment_proof_url }}" target="_blank" rel="noopener noreferrer"
                                class="d-inline-block mt-1">Lihat Bukti Pembayaran</a>
                        @else
                            <span>-</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Update Status</div>
                <div class="card-body">
                    @if ($order->allowedTransitions() === [])
                        <div class="alert alert-light border mb-0">
                            Pesanan sudah berada pada status final ({{ $order->status_label }}).
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <div class="col-12">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach ($nextStatuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>
                                            {{ \App\Models\Order::labelFor($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="shipping_cost" class="form-label">Ongkos Kirim</label>
                                <input id="shipping_cost" type="number" name="shipping_cost" class="form-control"
                                    value="{{ old('shipping_cost', (float) $order->shipping_cost) }}" min="0"
                                    step="0.01">
                            </div>
                            <div class="col-12">
                                <label for="courier" class="form-label">Kurir</label>
                                <input id="courier" type="text" name="courier" class="form-control"
                                    value="{{ old('courier', $order->courier) }}" placeholder="Contoh: JNE">
                            </div>
                            <div class="col-12">
                                <label for="courier_service" class="form-label">Layanan Kurir</label>
                                <input id="courier_service" type="text" name="courier_service" class="form-control"
                                    value="{{ old('courier_service', $order->courier_service) }}" placeholder="Contoh: REG">
                            </div>
                            <div class="col-12">
                                <label for="tracking_number" class="form-label">
                                    No. Resi
                                    <span class="text-danger" id="trackingRequiredMark" hidden>*</span>
                                </label>
                                <input id="tracking_number" type="text" name="tracking_number" class="form-control"
                                    value="{{ old('tracking_number', $order->tracking_number) }}">
                                <div class="form-text">Wajib diisi saat status diubah menjadi Dikirim.</div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-danger w-100" type="submit">Update</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const trackingInput = document.getElementById('tracking_number');
            const requiredMark = document.getElementById('trackingRequiredMark');

            if (!statusSelect || !trackingInput) {
                return;
            }

            const syncTrackingRequirement = function() {
                const isShipped = statusSelect.value === 'shipped';
                trackingInput.required = isShipped;

                if (requiredMark) {
                    requiredMark.hidden = !isShipped;
                }
            };

            statusSelect.addEventListener('change', syncTrackingRequirement);
            syncTrackingRequirement();
        });
    </script>
@endsection
