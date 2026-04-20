@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1 class="h4 fw-bold mb-4 text-center">Masuk ke Akun</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <x-auth-input id="email" name="email" label="Email" type="email" autocomplete="email" :required="true"
            :autofocus="true" :error="$errors->first('email')" />

        <x-password-toggle id="password" name="password" label="Password" autocomplete="current-password" :required="true"
            :error="$errors->first('password')" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            @if (Route::has('password.request'))
                <a class="small auth-link" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-auth w-100">Login</button>
    </form>

    @if (Route::has('register'))
        <p class="text-center mt-3 mb-0">
            Belum punya akun? <a href="{{ route('register') }}" class="auth-link">Daftar</a>
        </p>
    @endif
@endsection
