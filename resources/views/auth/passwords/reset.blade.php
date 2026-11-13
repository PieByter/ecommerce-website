@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h1 class="h4 fw-bold mb-4 text-center">Reset Password</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-auth-input id="email" name="email" label="Email" type="email" autocomplete="email" :required="true"
            :value="$email ?? null" :autofocus="true" :error="$errors->first('email')" />

        <x-password-toggle id="password" name="password" label="Password Baru" autocomplete="new-password"
            :required="true" :error="$errors->first('password')" />

        <x-password-toggle id="password-confirm" name="password_confirmation" label="Konfirmasi Password Baru"
            autocomplete="new-password" :required="true" :error="$errors->first('password_confirmation')" />

        <button type="submit" class="btn btn-auth w-100">Reset Password</button>
    </form>

    <p class="text-center mt-3 mb-0">
        <a href="{{ route('login') }}" class="auth-link">Kembali ke Login</a>
    </p>
@endsection
