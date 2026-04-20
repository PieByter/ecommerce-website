@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('page_title', 'Produk')

@section('content')
    @php
        $stockFilter = request()->string('stock')->toString();
        $isEmptyStockFilterActive = $stockFilter === 'empty';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Daftar Produk</h1>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-2">
                <label for="stockFilter" class="form-label mb-0 small text-muted">Filter</label>
                <select id="stockFilter" name="stock" class="form-select form-select-sm" onchange="this.form.submit()"
                    style="min-width: 170px;">
                    <option value="" @selected(!$isEmptyStockFilterActive)>Semua Produk</option>
                    <option value="empty" @selected($isEmptyStockFilterActive)>Stok Kosong</option>
                </select>
            </form>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah Produk
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr class="text-center align-middle">
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="text-center align-middle">
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                @php
                                    $rawImage = trim((string) $product->image);
                                    $imageUrl =
                                        $rawImage === ''
                                            ? 'https://placehold.co/200x200?text=No+Image'
                                            : (str_starts_with($rawImage, 'http://') ||
                                            str_starts_with($rawImage, 'https://')
                                                ? $rawImage
                                                : asset($rawImage));
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->supplier?->name ?? '-' }}</td>
                            <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                        class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn"
                                        title="Edit produk" aria-label="Edit produk">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger admin-action-btn"
                                        title="Hapus produk" aria-label="Hapus produk" data-bs-toggle="modal"
                                        data-bs-target="#deleteProductModal"
                                        data-delete-url="{{ route('admin.products.destroy', $product) }}"
                                        data-delete-name="{{ $product->name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                {{ $isEmptyStockFilterActive ? 'Tidak ada produk dengan stok kosong.' : 'Belum ada produk.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form id="deleteProductForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteProductModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Produk <strong id="deleteProductName"></strong> akan dihapus permanen.
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteProductBtn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteProductModal');
            const deleteForm = document.getElementById('deleteProductForm');
            const nameTarget = document.getElementById('deleteProductName');
            const confirmButton = document.getElementById('confirmDeleteProductBtn');

            deleteModal.addEventListener('show.bs.modal', function(event) {
                const triggerButton = event.relatedTarget;
                const deleteUrl = triggerButton?.getAttribute('data-delete-url') ?? '';
                const deleteName = triggerButton?.getAttribute('data-delete-name') ?? 'produk ini';

                deleteForm.setAttribute('action', deleteUrl);
                nameTarget.textContent = deleteName;
            });

            confirmButton.addEventListener('click', function() {
                deleteForm.submit();
            });
        });
    </script>

    <div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>
@endsection
