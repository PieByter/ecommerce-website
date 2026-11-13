@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page_title', 'Edit Kategori')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name) }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="slug" class="form-label">Slug</label>
                    <input id="slug" type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $category->slug) }}">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="parent_id" class="form-label">Kategori Induk</label>
                    <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">Jadikan kategori utama</option>
                        @foreach ($parentOptions as $option)
                            <option value="{{ $option['id'] }}"
                                @selected((int) old('parent_id', $category->parent_id) === $option['id'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Kategori ini dan subkategorinya tidak dapat dipilih sebagai induk.</div>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="sort_order" class="form-label">Urutan Tampil</label>
                    <input id="sort_order" type="number" name="sort_order" min="0" max="9999"
                        class="form-control @error('sort_order') is-invalid @enderror"
                        value="{{ old('sort_order', $category->sort_order) }}">
                    <div class="form-text">Angka kecil tampil lebih dahulu.</div>
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
