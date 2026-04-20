@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('page_title', 'Kategori Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Kategori</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                    class="btn btn-sm btn-outline-warning admin-action-btn" title="Edit kategori"
                                    aria-label="Edit kategori">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger admin-action-btn"
                                    title="Hapus kategori" aria-label="Hapus kategori" data-bs-toggle="modal"
                                    data-bs-target="#deleteCategoryModal"
                                    data-delete-url="{{ route('admin.categories.destroy', $category) }}"
                                    data-delete-name="{{ $category->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form id="deleteCategoryForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Kategori <strong id="deleteCategoryName"></strong> akan dihapus permanen.
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCategoryBtn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteCategoryModal');
            const deleteForm = document.getElementById('deleteCategoryForm');
            const nameTarget = document.getElementById('deleteCategoryName');
            const confirmButton = document.getElementById('confirmDeleteCategoryBtn');

            deleteModal.addEventListener('show.bs.modal', function(event) {
                const triggerButton = event.relatedTarget;
                const deleteUrl = triggerButton?.getAttribute('data-delete-url') ?? '';
                const deleteName = triggerButton?.getAttribute('data-delete-name') ?? 'kategori ini';

                deleteForm.setAttribute('action', deleteUrl);
                nameTarget.textContent = deleteName;
            });

            confirmButton.addEventListener('click', function() {
                deleteForm.submit();
            });
        });
    </script>

    <div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
@endsection
