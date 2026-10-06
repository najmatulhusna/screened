@extends('layouts.auth-public')

@section('title', 'Daftar Akun - SCREENED')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <a href="{{ route('collection') }}" class="auth-brand">
            <i class="bi bi-film"></i>SCREENED
        </a>
        <div class="auth-subtitle text-center">Buat akun untuk mulai mencatat watchlist</div>
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

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="text" name="username" id="regUsername"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" placeholder="Username" required autofocus>
                    <label for="regUsername">Username</label>
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
                    <input type="text" name="name" id="regName"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="Nama Lengkap" required>
                    <label for="regName">Nama</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-person-vcard"></span>
                </div>
            </div>
            @error('name')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="email" name="email" id="regEmail"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" placeholder="Email" required>
                    <label for="regEmail">Email</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-envelope"></span>
                </div>
            </div>
            @error('email')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="password" name="password" id="regPassword"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password" required>
                    <label for="regPassword">Password</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-lock-fill"></span>
                </div>
            </div>
            @error('password')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="password" name="password_confirmation" id="regPasswordConfirm"
                        class="form-control" placeholder="Ulangi Password" required>
                    <label for="regPasswordConfirm">Konfirmasi Password</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-shield-check"></span>
                </div>
            </div>

            <div class="row">
                <div class="col-12 d-grid gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Daftar
                    </button>
                </div>
            </div>
        </form>

        <div class="social-auth-links text-center mb-0 mt-3 d-grid gap-2">
            <p class="mb-0">- OR -</p>
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sudah punya akun? Login
            </a>
        </div>

        <p class="mb-0 mt-3 text-center">
            <a href="{{ route('collection') }}">Kembali ke Collection</a>
        </p>
    </div>
</div>
@endsection
