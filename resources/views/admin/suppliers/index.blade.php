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

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr class="text-center align-middle">
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
                                        title="Hapus supplier" aria-label="Hapus supplier" data-bs-toggle="modal"
                                        data-bs-target="#deleteSupplierModal"
                                        data-delete-url="{{ route('admin.suppliers.destroy', $supplier) }}"
                                        data-delete-name="{{ $supplier->name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form id="deleteSupplierForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-labelledby="deleteSupplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteSupplierModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Supplier <strong id="deleteSupplierName"></strong> akan dihapus.
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSupplierBtn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteSupplierModal');
            const deleteForm = document.getElementById('deleteSupplierForm');
            const nameTarget = document.getElementById('deleteSupplierName');
            const confirmButton = document.getElementById('confirmDeleteSupplierBtn');

            deleteModal.addEventListener('show.bs.modal', function(event) {
                const triggerButton = event.relatedTarget;
                const deleteUrl = triggerButton?.getAttribute('data-delete-url') ?? '';
                const deleteName = triggerButton?.getAttribute('data-delete-name') ?? 'supplier ini';

                deleteForm.setAttribute('action', deleteUrl);
                nameTarget.textContent = deleteName;
            });

            confirmButton.addEventListener('click', function() {
                deleteForm.submit();
            });
        });
    </script>

    <div class="mt-3">{{ $suppliers->links('pagination::bootstrap-5') }}</div>
@endsection
