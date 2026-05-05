@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white fw-semibold py-3 px-4">Profil Saya</div>
                    <div class="card-body">
                        @php($user = auth()->user())

                        {{-- @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif --}}

                        <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
                            @csrf
                            @method('PUT')

                            <div class="col-12">
                                <div class="p-3 rounded-3 border bg-light-subtle">
                                    <div class="mb-2">
                                        <span class="fw-semibold">Nama :</span>
                                        <span class="ms-1">{{ $user?->name }}</span>
                                        <span
                                            class="ms-2 badge text-bg-primary">{{ strtoupper((string) $user?->role) }}</span>
                                    </div>
                                    <div>
                                        <span class="fw-semibold">Email :</span>
                                        <span class="ms-1">{{ $user?->email }}</span>
                                        @if ($user?->email_verified_at)
                                            <span class="ms-2 text-success">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Verified
                                            </span>
                                        @else
                                            <span class="ms-2 text-muted">Belum terverifikasi</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Telepon :</label>
                                <input type="text" name="phone" class="form-control rounded-3"
                                    value="{{ old('phone', $user?->phone) }}" placeholder="081234567890">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat :</label>
                                <textarea name="address" class="form-control rounded-3" rows="3" placeholder="Alamat lengkap">{{ old('address', $user?->address) }}</textarea>
                                @error('address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Baru :</label>
                                <input type="password" name="password" class="form-control rounded-3"
                                    placeholder="Isi jika ingin mengganti password">
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Konfirmasi :</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3"
                                    placeholder="Ulangi password baru">
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-danger px-4">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
