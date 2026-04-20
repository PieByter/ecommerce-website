@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="container">
        <h1 class="h5 fw-semibold mb-3">Notifikasi Pesanan</h1>
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                @forelse($orders as $order)
                    <div class="list-group-item py-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">Order {{ $order->order_number }}</div>
                                <div class="small text-muted">Status terbaru: {{ ucfirst($order->status) }}</div>
                            </div>
                            <span class="small text-muted">{{ $order->updated_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item py-4 text-center text-muted">Belum ada notifikasi baru.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
