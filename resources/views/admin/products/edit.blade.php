@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page_title', 'Edit Produk')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" class="row g-3"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">Preview Gambar</label>
                    <div class="border rounded p-2 bg-light-subtle text-center">
                        @php
                            $rawImage = trim((string) $product->image);
                            $previewImage =
                                $rawImage === ''
                                    ? 'https://placehold.co/400x300?text=No+Image'
                                    : (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')
                                        ? $rawImage
                                        : asset($rawImage));
                        @endphp
                        <img id="imagePreview" src="{{ $previewImage }}" alt="Preview gambar produk"
                            style="width: 100%; max-height: 210px; object-fit: cover; border-radius: 8px;">
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Upload Gambar (JPG/JPEG/PNG/WEBP)</label>
                    <input type="file" name="image_file" id="imageFileInput" class="form-control"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="form-text">Maksimal 1MB. Upload file hanya jika ingin mengganti gambar lama.</div>
                    @error('image_file')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    @php
                        $selectedSupplierIds = collect(old('supplier_ids', $product->suppliers->pluck('id')->all()))
                            ->map(fn($id): int => (int) $id)
                            ->all();
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
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Harga</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}"
                        min="0" step="any" inputmode="decimal" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}"
                        min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Berat (gram)</label>
                    <input type="number" step="0.01" name="weight" class="form-control"
                        value="{{ old('weight', $product->weight) }}" min="0">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            @checked(old('is_active', $product->is_active))>
                        <label class="form-check-label" for="is_active">Produk Aktif</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-supplier-picker]').forEach(function(picker) {
                const searchInput = picker.querySelector('[data-supplier-search]');
                const badgesContainer = picker.querySelector('[data-supplier-badges]');
                const select = picker.querySelector('[data-supplier-select]');
                const optionButtons = Array.from(picker.querySelectorAll('[data-supplier-option]'));

                if (!searchInput || !badgesContainer || !select || optionButtons.length === 0) {
                    return;
                }

                const selectedIds = new Set(
                    Array.from(select.options)
                    .filter(option => option.selected)
                    .map(option => Number(option.value))
                );

                const syncSelect = function() {
                    Array.from(select.options).forEach(function(option) {
                        option.selected = selectedIds.has(Number(option.value));
                    });
                };

                const syncOptionStates = function() {
                    const term = searchInput.value.trim().toLowerCase();

                    optionButtons.forEach(function(button) {
                        const id = Number(button.dataset.supplierId);
                        const name = button.dataset.supplierName || '';
                        const isSelected = selectedIds.has(id);
                        const matchesSearch = term === '' || name.includes(term);
                        const label = button.querySelector('[data-supplier-option-label]');

                        button.hidden = !matchesSearch && !isSelected;
                        button.classList.toggle('active', isSelected);
                        button.setAttribute('aria-pressed', String(isSelected));

                        if (label) {
                            label.className = isSelected ? 'badge text-bg-success' :
                                'badge text-bg-light';
                            label.textContent = isSelected ? 'Dipilih' : 'Pilih';
                        }
                    });
                };

                const renderBadges = function() {
                    badgesContainer.innerHTML = '';

                    if (selectedIds.size === 0) {
                        const emptyState = document.createElement('span');
                        emptyState.className = 'text-muted small';
                        emptyState.textContent = 'Belum ada supplier dipilih.';
                        badgesContainer.appendChild(emptyState);
                        return;
                    }

                    Array.from(select.options)
                        .filter(option => selectedIds.has(Number(option.value)))
                        .forEach(function(option) {
                            const badge = document.createElement('span');
                            badge.className =
                                'badge rounded-pill text-bg-danger d-inline-flex align-items-center gap-2 py-2 px-3';

                            const label = document.createElement('span');
                            label.textContent = option.text;

                            const removeButton = document.createElement('button');
                            removeButton.type = 'button';
                            removeButton.className = 'btn-close btn-close-white btn-sm';
                            removeButton.style.fontSize = '0.65rem';
                            removeButton.setAttribute('aria-label', `Hapus ${option.text}`);
                            removeButton.addEventListener('click', function() {
                                selectedIds.delete(Number(option.value));
                                syncSelect();
                                renderBadges();
                                syncOptionStates();
                            });

                            badge.appendChild(label);
                            badge.appendChild(removeButton);
                            badgesContainer.appendChild(badge);
                        });
                };

                optionButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const id = Number(button.dataset.supplierId);

                        if (selectedIds.has(id)) {
                            selectedIds.delete(id);
                        } else {
                            selectedIds.add(id);
                        }

                        syncSelect();
                        renderBadges();
                        syncOptionStates();
                    });
                });

                searchInput.addEventListener('input', syncOptionStates);

                syncSelect();
                renderBadges();
                syncOptionStates();
            });

            const fileInput = document.getElementById('imageFileInput');
            const preview = document.getElementById('imagePreview');
            const fallbackImage = 'https://placehold.co/400x300?text=No+Image';

            if (!fileInput || !preview) {
                return;
            }

            fileInput.addEventListener('change', function(event) {
                const file = event.target.files?.[0];

                if (!file) {
                    preview.src = preview.getAttribute('src') || fallbackImage;
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
