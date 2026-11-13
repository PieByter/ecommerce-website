@extends('layouts.auth')

@section('title', 'Konfirmasi Password')

@section('content')
    <h1 class="h4 fw-bold mb-3 text-center">Konfirmasi Password</h1>

    <p class="text-muted">Silakan konfirmasi password Anda sebelum melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <x-password-toggle id="password" name="password" label="Password" autocomplete="current-password" :required="true"
            :error="$errors->first('password')" />

        <button type="submit" class="btn btn-auth w-100">Konfirmasi</button>
    </form>

    @if (Route::has('password.request'))
        <p class="text-center mt-3 mb-0">
            <a class="auth-link" href="{{ route('password.request') }}">Lupa password?</a>
        </p>
    @endif
@endsection
