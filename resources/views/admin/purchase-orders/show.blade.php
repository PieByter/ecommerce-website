@extends('layouts.admin')

@section('title', 'Detail Purchase Order')
@section('page_title', 'Detail Purchase Order')

@section('content')
    @php
        $statusClasses = [
            'draft' => 'text-secondary-emphasis bg-secondary-subtle',
            'ordered' => 'text-primary-emphasis bg-primary-subtle',
            'received' => 'text-success-emphasis bg-success-subtle',
            'cancelled' => 'text-danger-emphasis bg-danger-subtle',
        ];
        $canReceive = in_array($purchaseOrder->status, ['draft', 'ordered'], true);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">{{ $purchaseOrder->po_number }}</h1>
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Informasi PO</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="small text-muted">Supplier</div>
                            <div class="fw-semibold">{{ $purchaseOrder->supplier?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Status</div>
                            <span class="badge {{ $statusClasses[$purchaseOrder->status] ?? 'text-bg-secondary' }}">
                                {{ ucfirst($purchaseOrder->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Tanggal PO</div>
                            <div>{{ optional($purchaseOrder->order_date)->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Estimasi Datang</div>
                            <div>{{ optional($purchaseOrder->expected_date)->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Diterima Pada</div>
                            <div>{{ optional($purchaseOrder->received_at)->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Total Biaya</div>
                            <div class="fw-bold text-danger">Rp
                                {{ number_format((float) $purchaseOrder->total_cost, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Catatan</div>
                            <div>{{ $purchaseOrder->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Aksi PO</div>
                <div class="card-body d-grid gap-2">
                    <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}"
                        class="row g-2">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label class="form-label">Status</label>
                            @if ($purchaseOrder->status === 'received')
                                <input type="hidden" name="status" value="received">
                                <select class="form-select" disabled>
                                    <option selected>Received</option>
                                </select>
                            @else
                                <select name="status" class="form-select">
                                    <option value="draft" @selected($purchaseOrder->status === 'draft')>Draft</option>
                                    <option value="ordered" @selected($purchaseOrder->status === 'ordered')>Ordered</option>
                                    <option value="cancelled" @selected($purchaseOrder->status === 'cancelled')>Cancelled</option>
                                </select>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="form-label">Estimasi Datang</label>
                            <input type="date" name="expected_date" class="form-control"
                                value="{{ optional($purchaseOrder->expected_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control">{{ $purchaseOrder->notes }}</textarea>
                        </div>

                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Update PO</button>
                        </div>
                    </form>

                    @if ($canReceive)
                        <form method="POST" action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}"
                            onsubmit="return confirm('Terima PO ini? Stok produk akan bertambah sesuai item PO.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">Terima Barang & Tambah Stok</button>
                        </form>
                    @endif

                    @if ($purchaseOrder->status !== 'received')
                        <form method="POST" action="{{ route('admin.purchase-orders.destroy', $purchaseOrder) }}"
                            onsubmit="return confirm('Hapus PO ini? Data item PO juga akan terhapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">Hapus PO</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Item Purchase Order</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                        <th>Stok Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrder->items as $item)
                        <tr class="text-center align-middle">
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-start">{{ $item->product_name }}</td>
                            <td>{{ number_format((int) $item->quantity, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $item->unit_cost, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                            <td>{{ number_format((int) ($item->product?->stock ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada item pada PO ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
