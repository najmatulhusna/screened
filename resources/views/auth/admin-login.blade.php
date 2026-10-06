@extends('layouts.auth')

@section('title', 'Admin Login - SCREENED')

@section('content')
<div class="card card-outline card-dark">
    <div class="card-header text-center">
        <a href="{{ route('collection') }}" class="link-dark text-decoration-none">
            <i class="bi bi-shield-lock"></i> <span class="fs-5 fw-semibold">SCREENED</span>
        </a>
        <div class="small text-body-secondary mt-1">Masuk sebagai admin</div>
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

        <form method="POST" action="{{ url('admin/login') }}">
            @csrf

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="text" name="username" id="adminUsername"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" placeholder="Username" required autofocus>
                    <label for="adminUsername">Username</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-person-badge"></span>
                </div>
            </div>
            @error('username')
                <div class="invalid-feedback d-block mb-1">{{ $message }}</div>
            @enderror

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="password" name="password" id="adminPassword"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password" required>
                    <label for="adminPassword">Password</label>
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
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                </div>
            </div>
        </form>

        <div class="social-auth-links text-center mb-0 mt-3 d-grid gap-2">
            <p class="mb-0">- OR -</p>
            <a href="{{ route('admin.register') }}" class="btn btn-outline-dark">
                <i class="bi bi-person-plus me-2"></i> Daftar Admin
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="bi bi-people me-2"></i> Login sebagai User
            </a>
        </div>

        <p class="mb-0 mt-3 text-center">
            <a href="{{ route('collection') }}">Kembali ke Collection</a>
        </p>
    </div>
</div>
@endsection
