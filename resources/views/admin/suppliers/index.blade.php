@extends('layouts.admin')

@section('title', 'Kelola Supplier')
@section('page_title', 'Supplier')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Supplier</h1>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Supplier
        </a>
    </div>

    <x-admin-table
        :columns="['No', 'Nama', 'Telepon', 'Alamat', 'Aksi']"
        search-route="admin.suppliers.index"
        search-placeholder="Cari supplier..."
    >
        @forelse($suppliers as $supplier)
            <tr class="text-center">
                <td>{{ $suppliers->firstItem() + $loop->index }}</td>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->phone ?: '-' }}</td>
                <td class="text-start">{{ $supplier->address ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                            class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn"
                            title="Edit supplier" aria-label="Edit supplier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger admin-action-btn"
                            title="Hapus supplier" aria-label="Hapus supplier"
                            data-bs-toggle="modal" data-bs-target="#deleteSupplierModal"
                            data-delete-url="{{ route('admin.suppliers.destroy', $supplier) }}"
                            data-delete-name="{{ $supplier->name }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="5" message="Belum ada supplier." />
        @endforelse
    </x-admin-table>

    <div class="mt-3">{{ $suppliers->links('pagination::bootstrap-5') }}</div>

    <x-delete-modal
        id="deleteSupplierModal"
        form-id="deleteSupplierForm"
        name-target-id="deleteSupplierName"
        confirm-btn-id="confirmDeleteSupplierBtn"
        label="Supplier"
    />
@endsection
