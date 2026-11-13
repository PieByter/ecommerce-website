@extends('layouts.admin')

@section('title', 'Purchase Order')
@section('page_title', 'Purchase Order')

@section('content')
    @php
        $statuses = \App\Models\PurchaseOrder::STATUSES;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Purchase Order</h1>
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Buat PO
        </a>
    </div>

    <x-admin-table
        :columns="['No', 'No. PO', 'Supplier', 'Tanggal PO', 'Total', 'Status', 'Item', 'Aksi']"
        search-route="admin.purchase-orders.index"
        search-placeholder="Cari no. PO atau supplier..."
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
                            {{ \App\Models\PurchaseOrder::labelFor($status) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </x-slot>

        @forelse($purchaseOrders as $purchaseOrder)
            <tr class="text-center">
                <td>{{ $purchaseOrders->firstItem() + $loop->index }}</td>
                <td><code>{{ $purchaseOrder->po_number }}</code></td>
                <td>{{ $purchaseOrder->supplier?->name ?? '-' }}</td>
                <td>{{ optional($purchaseOrder->order_date)->format('d M Y') }}</td>
                <td>Rp {{ number_format((float) $purchaseOrder->total_cost, 0, ',', '.') }}</td>
                <td>
                    <span class="badge {{ $purchaseOrder->status_badge_class }}">
                        {{ $purchaseOrder->status_label }}
                    </span>
                </td>
                <td>{{ (int) $purchaseOrder->items_count }}</td>
                <td>
                    <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}"
                        class="btn btn-sm btn-outline-primary admin-action-btn"
                        title="Detail PO" aria-label="Detail PO">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="8" message="Belum ada purchase order." />
        @endforelse
    </x-admin-table>

    <div class="mt-3">{{ $purchaseOrders->links('pagination::bootstrap-5') }}</div>
@endsection
