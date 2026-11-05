@extends('layouts.public')

@section('title', ($mode === 'edit' ? 'Edit - ' : 'Add Manually - ') . 'My Album - SCREENED')

@section('content')
<div class="container my-album-page album-form-page">

    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <a href="{{ route('myalbum.index') }}" class="text-decoration-none text-body-secondary">
            <i class="bi bi-arrow-left"></i> My Album
        </a>
    </div>

    <div class="page-head album-head">
        <h1>{{ $mode === 'edit' ? 'Edit Title' : 'Add Manual' }}</h1>
        <p class="album-subtitle">
            {{ $mode === 'edit'
                ? 'Perbarui status, rating, dan catatan pribadimu'
                : 'Catat film atau drama yang belum ada di Collection' }}
        </p>
    </div>

    <form action="{{ $mode === 'edit' ? route('myalbum.update', $entry->id) : route('myalbum.store') }}"
        method="POST" enctype="multipart/form-data" class="album-form">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        @include('partials.album-form-fields', ['entry' => $entry])

        <div class="d-flex flex-wrap gap-2 mb-4">
            <button type="submit" class="btn btn-gold">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
                {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah ke My Album' }}
            </button>
            <a href="{{ $mode === 'edit' ? route('myalbum.show', $entry->id) : route('myalbum.index') }}"
                class="btn btn-outline-secondary">
                Batal
            </a>
        </div>

    </form>
</div>
@endsection
