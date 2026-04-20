@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h1 class="h4 fw-bold mb-4 text-center">Reset Password</h1>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-auth w-100">Send Password Reset Link</button>
    </form>

    <p class="text-center mt-3 mb-0">
        <a href="{{ route('login') }}" class="auth-link">Kembali ke Login</a>
    </p>
@endsection
