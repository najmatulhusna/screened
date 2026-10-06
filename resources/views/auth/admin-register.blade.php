@extends('layouts.auth')

@section('title', 'Daftar Admin - SCREENED')

@section('content')
<div class="card card-outline card-dark">
    <div class="card-header text-center">
        <a href="{{ route('collection') }}" class="link-dark text-decoration-none">
            <i class="bi bi-shield-lock"></i> <span class="fs-5 fw-semibold">SCREENED</span>
        </a>
        <div class="small text-body-secondary mt-1">Buat akun admin baru</div>
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

        <form method="POST" action="{{ route('admin.register') }}">
            @csrf

            <div class="input-group mb-1">
                <div class="form-floating">
                    <input type="text" name="username" id="adminRegUsername"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" placeholder="Username" required autofocus>
                    <label for="adminRegUsername">Username</label>
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
                    <input type="password" name="password" id="adminRegPassword"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password" required>
                    <label for="adminRegPassword">Password</label>
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
                    <input type="password" name="password_confirmation" id="adminRegPasswordConfirm"
                        class="form-control" placeholder="Ulangi Password" required>
                    <label for="adminRegPasswordConfirm">Konfirmasi Password</label>
                </div>
                <div class="input-group-text">
                    <span class="bi bi-shield-check"></span>
                </div>
            </div>

            <div class="row">
                <div class="col-12 d-grid gap-2 mt-2">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-person-plus me-1"></i> Daftar
                    </button>
                </div>
            </div>
        </form>

        <p class="mb-0 mt-3 text-center">
            Sudah punya akun admin? <a href="{{ route('admin.login') }}">Login</a>
        </p>
    </div>
</div>
@endsection
