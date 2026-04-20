@extends('layouts.admin')

@section('title', 'Purchase Order')
@section('page_title', 'Purchase Order')

@section('content')
    @php
        $statuses = ['draft', 'ordered', 'received', 'cancelled'];
        $statusClasses = [
            'draft' => 'text-secondary-emphasis bg-secondary-subtle',
            'ordered' => 'text-primary-emphasis bg-primary-subtle',
            'received' => 'text-success-emphasis bg-success-subtle',
            'cancelled' => 'text-danger-emphasis bg-danger-subtle',
        ];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Purchase Order</h1>
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Buat PO
        </a>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
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
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tanggal PO</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Item</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $purchaseOrder)
                        <tr class="text-center align-middle">
                            <td>{{ $purchaseOrders->firstItem() + $loop->index }}</td>
                            <td>{{ $purchaseOrder->po_number }}</td>
                            <td>{{ $purchaseOrder->supplier?->name ?? '-' }}</td>
                            <td>{{ optional($purchaseOrder->order_date)->format('d M Y') }}</td>
                            <td>Rp {{ number_format((float) $purchaseOrder->total_cost, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$purchaseOrder->status] ?? 'text-bg-secondary' }}">
                                    {{ ucfirst($purchaseOrder->status) }}
                                </span>
                            </td>
                            <td>{{ (int) $purchaseOrder->items_count }}</td>
                            <td>
                                <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}"
                                    class="btn btn-sm btn-outline-primary admin-action-btn" title="Detail PO"
                                    aria-label="Detail PO">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada purchase order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $purchaseOrders->links('pagination::bootstrap-5') }}</div>
@endsection
