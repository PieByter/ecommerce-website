@extends('layouts.auth')

@section('title', 'Verifikasi Email')

@section('content')
    <h1 class="h4 fw-bold mb-3 text-center">Verifikasi Email</h1>

    @if (session('resent'))
        <div class="alert alert-success" role="alert">
            Link verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <p class="text-muted">
        Sebelum melanjutkan, periksa email Anda untuk menemukan link verifikasi.
        Jika Anda belum menerima email tersebut,
    </p>

    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-auth w-100">Kirim Ulang Link Verifikasi</button>
    </form>

    <p class="text-center mt-3 mb-0">
        <a href="{{ route('logout') }}" class="auth-link"
            onclick="event.preventDefault(); document.getElementById('logout-form-verify').submit();">
            Keluar
        </a>
    </p>
    <form id="logout-form-verify" method="POST" action="{{ route('logout') }}" class="d-none">
        @csrf
    </form>
@endsection
