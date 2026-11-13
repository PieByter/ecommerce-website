@extends('layouts.admin')

@section('title', 'Detail Purchase Order')
@section('page_title', 'Detail Purchase Order')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 mb-1">{{ $purchaseOrder->po_number }}</h1>
            <span class="badge {{ $purchaseOrder->status_badge_class }}">{{ $purchaseOrder->status_label }}</span>
        </div>
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Informasi PO</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Supplier</div>
                            <div class="fw-semibold">{{ $purchaseOrder->supplier?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Tanggal PO</div>
                            <div>{{ $purchaseOrder->order_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Estimasi Datang</div>
                            <div>{{ $purchaseOrder->expected_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Diterima Pada</div>
                            <div>{{ $purchaseOrder->received_at?->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
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
                    @if ($purchaseOrder->allowedTransitions() === [])
                        <div class="alert alert-light border mb-0">
                            PO sudah berada pada status final ({{ $purchaseOrder->status_label }}).
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}"
                            class="row g-2">
                            @csrf
                            @method('PUT')

                            <div class="col-12">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    @foreach ($nextStatuses as $status)
                                        <option value="{{ $status }}"
                                            @selected(old('status', $purchaseOrder->status) === $status)>
                                            {{ \App\Models\PurchaseOrder::labelFor($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="expected_date" class="form-label">Estimasi Datang</label>
                                <input id="expected_date" type="date" name="expected_date" class="form-control"
                                    min="{{ $purchaseOrder->order_date?->toDateString() }}"
                                    value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea id="notes" name="notes" rows="3" class="form-control">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                            </div>

                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-outline-primary">Update PO</button>
                            </div>
                        </form>
                    @endif

                    @if ($purchaseOrder->canBeReceived())
                        <form method="POST" action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}"
                            onsubmit="return confirm('Terima PO ini? Stok produk akan bertambah sesuai item PO.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">Terima Barang & Tambah Stok</button>
                        </form>
                    @elseif ($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT)
                        <div class="alert alert-info border mb-0 py-2">
                            Ubah status menjadi <strong>Dipesan</strong> sebelum menerima barang.
                        </div>
                    @endif

                    @if ($purchaseOrder->canBeDeleted())
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
                            <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
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
