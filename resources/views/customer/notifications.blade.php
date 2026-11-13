@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="container">
        <h1 class="h5 fw-semibold mb-3">Notifikasi Pesanan</h1>
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                @forelse($orders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}"
                        class="list-group-item list-group-item-action py-3 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">Order {{ $order->order_number }}</div>
                                <div class="small">
                                    <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                                </div>
                            </div>
                            <span class="small text-muted text-nowrap">{{ $order->updated_at?->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item py-4 text-center text-muted">Belum ada notifikasi pesanan.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
