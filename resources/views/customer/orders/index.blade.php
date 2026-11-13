@extends('layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
    <div class="container">
        <style>
            .customer-orders-table {
                table-layout: fixed;
            }

            .customer-orders-table th,
            .customer-orders-table td {
                vertical-align: middle;
            }

            .order-items-table th,
            .order-items-table td {
                vertical-align: middle;
            }

            .order-item-thumb {
                width: 56px;
                height: 56px;
                object-fit: cover;
            }
        </style>

        <h1 class="h5 fw-semibold mb-3">Riwayat Pesanan</h1>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 align-middle customer-orders-table">
                    <colgroup>
                        <col style="width: 60px;">
                        <col style="width: 170px;">
                        <col style="width: 140px;">
                        <col style="width: 170px;">
                        <col style="width: 150px;">
                        <col style="width: 150px;">
                        <col style="width: 180px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-nowrap">No</th>
                            <th class="text-nowrap">No. Order</th>
                            <th>Item</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap">Total</th>
                            <th class="text-nowrap">Tanggal</th>
                            <th class="text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $orderRowId = 'orderItems' . $order->id;
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $orders->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('customer.orders.show', $order) }}"
                                        class="text-decoration-none fw-semibold">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="pe-2">
                                    @if ($order->items->isNotEmpty())
                                        <button class="btn btn-sm btn-outline-secondary text-nowrap" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $orderRowId }}"
                                            aria-expanded="false" aria-controls="{{ $orderRowId }}">
                                            {{ $order->items->count() }} item
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $order->status_badge_class }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                <td>{{ $order->created_at?->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ route('customer.orders.show', $order) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap">Detail</a>
                                        @if ($order->canBeCancelledByCustomer())
                                            <form action="{{ route('customer.orders.cancel', $order) }}" method="POST"
                                                onsubmit="return confirm('Batalkan pesanan ini?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($order->items->isNotEmpty())
                                <tr class="collapse" id="{{ $orderRowId }}">
                                    <td colspan="7" class="bg-light-subtle border-top-0 px-3 py-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 align-middle order-items-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 72px;">Gambar</th>
                                                        <th>Produk</th>
                                                        <th style="width: 100px;">Qty</th>
                                                        <th style="width: 160px;">Harga</th>
                                                        <th style="width: 180px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order->items as $item)
                                                        @php
                                                            $reviewLink = $item->product
                                                                ? route('products.show', $item->product->slug) . '#review-form'
                                                                : '#';
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <img src="{{ $item->product?->image_url ?? asset('images/placeholder.svg') }}"
                                                                    alt="{{ $item->product_name }}"
                                                                    class="rounded border order-item-thumb"
                                                                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder.svg') }}';">
                                                            </td>
                                                            <td>
                                                                <div class="fw-semibold">{{ $item->product_name }}</div>
                                                                @if ($item->product)
                                                                    <a href="{{ route('products.show', $item->product->slug) }}"
                                                                        class="small text-decoration-none">Lihat produk</a>
                                                                @endif
                                                            </td>
                                                            <td>{{ $item->quantity }}</td>
                                                            <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                                                            </td>
                                                            <td>
                                                                @if ($order->status === \App\Models\Order::STATUS_DELIVERED && $item->product)
                                                                    <a href="{{ $reviewLink }}"
                                                                        class="btn btn-sm btn-outline-danger">
                                                                        Tulis Review
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted small">Review setelah
                                                                        selesai.</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
