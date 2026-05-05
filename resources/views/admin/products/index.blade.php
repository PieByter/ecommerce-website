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
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Produk
        </a>
    </div>

    <x-admin-table :columns="['No', 'Gambar', 'Nama', 'Kategori', 'Supplier', 'Harga', 'Stok', 'Status', 'Aksi']" search-route="admin.products.index" search-placeholder="Cari produk..."
        :filter-slot="true">
        {{-- Filter slot --}}
        <x-slot name="filter">
            <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-2">
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <label for="stockFilter" class="form-label mb-0 small text-muted">Filter Stok</label>
                <select id="stockFilter" name="stock" class="form-select form-select-sm" onchange="this.form.submit()"
                    style="min-width: 170px;">
                    <option value="" @selected(!$isEmptyStockFilterActive)>Semua Produk</option>
                    <option value="empty" @selected($isEmptyStockFilterActive)>Stok Kosong</option>
                </select>
            </form>
        </x-slot>

        @forelse($products as $product)
            <tr class="text-center">
                <td>{{ $products->firstItem() + $loop->index }}</td>
                <td>
                    @php
                        $rawImage = trim((string) $product->image);
                        $imageUrl =
                            $rawImage === ''
                                ? 'https://placehold.co/200x200?text=No+Image'
                                : (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')
                                    ? $rawImage
                                    : asset($rawImage));
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                        style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px;" loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';">
                </td>
                <td class="text-start">{{ $product->name }}</td>
                <td>{{ $product->category?->name }}</td>
                <td>{{ $product->suppliers->pluck('name')->implode(', ') ?: '-' }}</td>
                <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                <td>
                    @if ($product->stock === 0)
                        <span class="badge text-bg-danger">Habis</span>
                    @elseif ($product->stock <= 5)
                        <span class="badge text-bg-warning">{{ $product->stock }}</span>
                    @else
                        {{ $product->stock }}
                    @endif
                </td>
                <td>
                    @if ($product->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-danger">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('admin.products.edit', $product) }}"
                            class="btn btn-sm btn-outline-warning text-decoration-none admin-action-btn" title="Edit produk"
                            aria-label="Edit produk">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger admin-action-btn" title="Hapus produk"
                            aria-label="Hapus produk" data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                            data-delete-url="{{ route('admin.products.destroy', $product) }}"
                            data-delete-name="{{ $product->name }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <x-table-empty-row :colspan="9"
                message="{{ $isEmptyStockFilterActive ? 'Tidak ada produk dengan stok kosong.' : 'Belum ada produk.' }}" />
        @endforelse
    </x-admin-table>

    <div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>

    <x-delete-modal id="deleteProductModal" form-id="deleteProductForm" name-target-id="deleteProductName"
        confirm-btn-id="confirmDeleteProductBtn" label="Produk" />
@endsection
