@extends('layouts.admin')

@section('title', 'Edit Supplier')
@section('page_title', 'Edit Supplier')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}"
                        required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                    @error('phone')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $supplier->address) }}</textarea>
                    @error('address')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0">Produk dari Supplier Ini</h2>
                <span class="text-muted small">{{ $products->count() }} produk</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th scope="col" style="width: 70px;">No</th>
                            <th scope="col" class="text-start">Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Stok</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-start">{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
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
                                        @if ($product->is_active)
                                            <a href="{{ route('products.show', $product->slug) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary" title="Lihat produk"
                                                aria-label="Lihat produk">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @else
                                            <span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true"
                                                title="Produk tidak aktif">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        @endif
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit produk"
                                            aria-label="Edit produk">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada produk untuk supplier ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
