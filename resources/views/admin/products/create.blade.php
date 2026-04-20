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
                    <img id="imagePreview" src="https://placehold.co/400x300?text=No+Image" alt="Preview gambar produk"
                        style="width: 100%; max-height: 210px; object-fit: cover; border-radius: 8px;">
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">Upload Gambar (JPG/JPEG/PNG/WEBP)</label>
                <input type="file" name="image_file" id="imageFileInput" class="form-control" required
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
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id')===$category->id)>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Pilih supplier</option>
                    @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((int) old('supplier_id')===$supplier->id)>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
                </select>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('imageFileInput');
        const preview = document.getElementById('imagePreview');
        const fallbackImage = 'https://placehold.co/400x300?text=No+Image';

        if (!fileInput || !preview) {
            return;
        }

        fileInput.addEventListener('change', function(event) {
            const file = event.target.files?.[0];

            if (!file) {
                preview.src = fallbackImage;
                return;
            }

            const reader = new FileReader();
            reader.onload = function(loadEvent) {
                preview.src = String(loadEvent.target?.result ?? fallbackImage);
            };
            reader.readAsDataURL(file);
        });

        preview.addEventListener('error', function() {
            preview.src = fallbackImage;
        });
    });
</script>
@endsection