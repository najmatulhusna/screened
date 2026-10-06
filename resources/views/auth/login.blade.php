@extends('layouts.auth-public')

@section('title', 'Login - SCREENED')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <a href="{{ route('collection') }}" class="auth-brand">
            <i class="bi bi-film"></i>SCREENED
        </a>
        <div class="auth-subtitle text-center">Masuk untuk melanjutkan</div>
    </div>

    <div class="card-body login-card-body">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="text" name="username" id="loginUsername"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" placeholder="Username" required autofocus>
                    <label for="loginUsername">Username</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-person"></span>
                </div>
            </div>
            @error('username')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="password" name="password" id="loginPassword"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password" required>
                    <label for="loginPassword">Password</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-lock-fill"></span>
                </div>
            </div>
            @error('password')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="row">
                <div class="col-12 d-grid gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                </div>
            </div>
        </form>

        <div class="social-auth-links text-center mb-0 mt-3 d-grid gap-2">
            <p class="mb-0">- OR -</p>
            <a href="{{ route('register') }}" class="btn btn-outline-primary">
                <i class="bi bi-person-plus me-2"></i> Daftar Akun Baru
            </a>
        </div>

        <p class="mb-0 mt-3 text-center">
            <a href="{{ route('collection') }}">Kembali ke Collection</a>
        </p>
    </div>
</div>
@endsection
