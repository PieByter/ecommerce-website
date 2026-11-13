@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page_title', 'Tambah Produk')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}" class="row g-3" enctype="multipart/form-data">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Preview Gambar</label>
                    <div class="border rounded p-2 bg-light-subtle text-center">
                        <img id="imagePreview" src="{{ asset('images/placeholder.svg') }}" alt="Preview gambar produk"
                            style="width: 100%; max-height: 210px; object-fit: cover; border-radius: 8px;">
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Upload Gambar (JPG/JPEG/PNG/WEBP)</label>
                    <input type="file" name="image_file" id="imageFileInput" class="form-control" required
                        data-image-input data-image-preview="#imagePreview"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="form-text">Maksimal 1MB. Upload file wajib untuk membuat produk baru.</div>
                    @error('image_file')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categoryOptions as $option)
                            <option value="{{ $option['id'] }}" @selected((int) old('category_id') === $option['id'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    @php
                        $selectedSupplierIds = collect(old('supplier_ids', []))->map(fn($id): int => (int) $id)->all();
                    @endphp
                    <div class="border rounded-3 p-3 bg-body-tertiary" data-supplier-picker>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Cari supplier</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Ketik nama supplier..."
                                    data-supplier-search>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2" data-supplier-badges></div>
                        </div>

                        <div class="list-group" style="max-height: 240px; overflow-y: auto;" data-supplier-options>
                            @foreach ($suppliers as $supplier)
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    data-supplier-option data-supplier-id="{{ $supplier->id }}"
                                    data-supplier-name="{{ strtolower($supplier->name) }}">
                                    <span>{{ $supplier->name }}</span>
                                    <span class="badge text-bg-light" data-supplier-option-label>Pilih</span>
                                </button>
                            @endforeach
                        </div>

                        <select name="supplier_ids[]" class="d-none" multiple data-supplier-select>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(in_array($supplier->id, $selectedSupplierIds, true))>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-2">Cari supplier lalu klik untuk memilih. Supplier yang dipilih tampil sebagai
                        badge dan bisa dihapus.</div>
                    @error('supplier_ids')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    @error('supplier_ids.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', 0) }}" min="0"
                        step="any" inputmode="decimal" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Berat (gram)</label>
                    <input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight', 0) }}"
                        min="0">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            @checked(old('is_active', true))>
                        <label class="form-check-label" for="is_active">Produk Aktif</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-danger">Simpan</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
