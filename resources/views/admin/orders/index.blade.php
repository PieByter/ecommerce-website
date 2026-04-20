@extends('layouts.admin')

@section('title', 'Kelola Pesanan')
@section('page_title', 'Pesanan')

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
        $statusTextColors = [
            'pending' => '#b7791f',
            'confirmed' => '#1f8a4c',
            'processing' => '#0c8599',
            'shipped' => '#0d6efd',
            'delivered' => '#495057',
            'cancelled' => '#c92a2a',
        ];
    @endphp

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)
                                style="color: {{ $statusTextColors[$status] ?? '#212529' }}; font-weight: 600;">
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>No. Order</th>
                        <th>Pembeli</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="text-center align-middle">
                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user?->name }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$order->status] ?? 'text-bg-secondary' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary admin-action-btn"
                                    href="{{ route('admin.orders.show', $order) }}" title="Detail pesanan"
                                    aria-label="Detail pesanan">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
