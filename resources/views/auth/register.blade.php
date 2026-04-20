@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <h1 class="h4 fw-bold mb-4 text-center">Daftar Akun Baru</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <x-auth-input id="name" name="name" label="Nama" type="text" autocomplete="name" :required="true"
            :autofocus="true" :error="$errors->first('name')" />

        <x-auth-input id="email" name="email" label="Email" type="email" autocomplete="email" :required="true"
            :error="$errors->first('email')" />

        <x-password-toggle id="password" name="password" label="Password" autocomplete="new-password" :required="true"
            :error="$errors->first('password')" />

        <x-password-toggle id="password-confirm" name="password_confirmation" label="Konfirmasi Password"
            autocomplete="new-password" :required="true" />

        <button type="submit" class="btn btn-auth w-100">Register</button>
    </form>

    <p class="text-center mt-3 mb-0">
        Sudah punya akun? <a href="{{ route('login') }}" class="auth-link">Login</a>
    </p>
@endsection
