@extends('layouts.admin')

@section('title', 'Kelola Admin')
@section('page_title', 'Data Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Admin</h1>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr class="text-center align-middle">
                            <td>{{ $admins->firstItem() + $loop->index }}</td>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->phone ?? '-' }}</td>
                            <td>{{ $admin->address ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('admin.users.edit', $admin) }}"
                                        class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn"
                                        title="Edit admin" aria-label="Edit admin">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada admin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($admins->hasPages())
        <div class="mt-3">{{ $admins->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    @endif

@endsection
