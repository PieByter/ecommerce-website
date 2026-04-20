@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')

    @php
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $statusClasses = [
            'pending' => 'text-warning-emphasis bg-warning-subtle',
            'confirmed' => 'text-primary-emphasis bg-primary-subtle',
            'processing' => 'text-info-emphasis bg-info-subtle',
            'shipped' => 'text-secondary-emphasis bg-secondary-subtle',
            'delivered' => 'text-success-emphasis bg-success-subtle',
            'cancelled' => 'text-danger-emphasis bg-danger-subtle',
        ];
    @endphp
    <div class="page-header">
        <h1>Ringkasan Toko</h1>
    </div>

    <livewire:admin.dashboard-stats />

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Pesanan Terbaru</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. Order</th>
                            <th>Pembeli</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestOrders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->user?->name ?? '-' }}</td>
                                <td><span class="badge {{ $statusClasses[$order->status] ?? 'text-bg-secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                                <td>{{ $order->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
