@extends('layouts.admin')

@section('title', 'Kelola Admin')
@section('page_title', 'Data Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Admin</h1>
    </div>

    <x-admin-table
        :columns="['No', 'Nama', 'Email', 'Phone', 'Alamat', 'Aksi']"
        search-route="admin.users.index"
        search-placeholder="Cari admin..."
    >
        @forelse($admins as $admin)
            <tr class="text-center">
                <td>{{ $admins->firstItem() + $loop->index }}</td>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->phone ?? '-' }}</td>
                <td class="text-start">{{ $admin->address ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $admin) }}"
                        class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn"
                        title="Edit admin" aria-label="Edit admin">
                        <i class="bi bi-pencil"></i>
                    </a>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="6" message="Belum ada admin." />
        @endforelse
    </x-admin-table>

    @if ($admins->hasPages())
        <div class="mt-3">{{ $admins->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection
