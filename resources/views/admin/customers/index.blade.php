@extends('layouts.admin')

@section('title', 'Kelola Customer')
@section('page_title', 'Data Customer')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Customer</h1>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Alamat</th>
                        <th>Email Verified</th>
                        <th>Total Order</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="text-center align-middle text-sm">
                            <td>{{ $customers->firstItem() + $loop->index }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td class="text-start">{{ $customer->address ?? '-' }}</td>
                            <td>
                                @if ($customer->email_verified_at)
                                    <span class="badge text-bg-success">Sudah</span>
                                @else
                                    <span class="badge text-bg-secondary">Belum</span>
                                @endif
                            </td>
                            <td>{{ $customer->orders_count }}</td>
                            <td>
                                <a href="{{ route('admin.customers.edit', $customer) }}"
                                    class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn"
                                    title="Edit customer" aria-label="Edit customer">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($customers->hasPages())
        <div class="mt-3">{{ $customers->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
