@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('page_title', 'Kategori Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Kategori</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </a>
    </div>

    <x-admin-table :columns="['No', 'Nama', 'Slug', 'Aksi']" search-route="admin.categories.index" search-placeholder="Cari kategori...">
        @forelse($categories as $category)
            <tr class="text-center">
                <td>{{ $categories->firstItem() + $loop->index }}</td>
                <td>{{ $category->name }}</td>
                <td><code>{{ $category->slug }}</code></td>
                <td>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('admin.categories.edit', $category) }}"
                            class="btn btn-sm btn-outline-warning admin-action-btn" title="Edit kategori"
                            aria-label="Edit kategori">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger admin-action-btn" title="Hapus kategori"
                            aria-label="Hapus kategori" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                            data-delete-url="{{ route('admin.categories.destroy', $category) }}"
                            data-delete-name="{{ $category->name }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="4" message="Belum ada kategori." />
        @endforelse
    </x-admin-table>

    <div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>

    <x-delete-modal id="deleteCategoryModal" form-id="deleteCategoryForm" name-target-id="deleteCategoryName"
        confirm-btn-id="confirmDeleteCategoryBtn" label="Kategori" />
@endsection
