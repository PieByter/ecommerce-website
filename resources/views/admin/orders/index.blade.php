@extends('layouts.admin')

@section('title', 'Kelola Pesanan')
@section('page_title', 'Pesanan')

@section('content')
    @php
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $statusClasses = [
            'pending'    => 'text-warning-emphasis bg-warning-subtle',
            'confirmed'  => 'text-primary-emphasis bg-primary-subtle',
            'processing' => 'text-info-emphasis bg-info-subtle',
            'shipped'    => 'text-secondary-emphasis bg-secondary-subtle',
            'delivered'  => 'text-success-emphasis bg-success-subtle',
            'cancelled'  => 'text-danger-emphasis bg-danger-subtle',
        ];
    @endphp

    <x-admin-table
        :columns="['No', 'No. Order', 'Pembeli', 'Status', 'Total', 'Tanggal', 'Aksi']"
        search-route="admin.orders.index"
        search-placeholder="Cari no. order atau nama pembeli..."
        :filter-slot="true"
    >
        {{-- Status filter --}}
        <x-slot name="filter">
            <form method="GET" class="d-flex align-items-center gap-2">
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <label class="form-label mb-0 small text-muted">Filter Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 160px;">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </x-slot>

        @forelse($orders as $order)
            <tr class="text-center">
                <td>{{ $orders->firstItem() + $loop->index }}</td>
                <td><code>{{ $order->order_number }}</code></td>
                <td>{{ $order->user?->name }}</td>
                <td>
                    <span class="badge {{ $statusClasses[$order->status] ?? 'text-bg-secondary' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>
                    <a class="btn btn-sm btn-outline-primary admin-action-btn"
                        href="{{ route('admin.orders.show', $order) }}"
                        title="Detail pesanan" aria-label="Detail pesanan">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="7" message="Belum ada pesanan." />
        @endforelse
    </x-admin-table>

    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
