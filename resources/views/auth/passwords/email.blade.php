@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
    <h1 class="h4 fw-bold mb-4 text-center">Reset Password</h1>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <p class="text-muted">Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat password baru.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <x-auth-input id="email" name="email" label="Email" type="email" autocomplete="email" :required="true"
            :autofocus="true" :error="$errors->first('email')" />

        <button type="submit" class="btn btn-auth w-100">Kirim Link Reset Password</button>
    </form>

    <p class="text-center mt-3 mb-0">
        <a href="{{ route('login') }}" class="auth-link">Kembali ke Login</a>
    </p>
@endsection
