@extends('layouts.adminlte')

@section('title', 'Edit Item - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Edit Item</h1>
            <p class="text-body-secondary mb-0">{{ $watchItem->title }}</p>
        </div>
        <a href="{{ route('admin.manage') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Manage
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> Data belum lengkap
            </h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.update', $watchItem->id) }}" enctype="multipart/form-data"
        id="editItemForm">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-info-circle"></i> Item Info</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $watchItem->title) }}" placeholder="Contoh: Interstellar" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach(['Movie', 'Drama', 'Series', 'Tv Show'] as $typeOption)
                                <option value="{{ $typeOption }}" @selected(old('type', $watchItem->type) === $typeOption)>
                                    {{ $typeOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="release_year">Release Year <span class="text-danger">*</span></label>
                        <input type="number" name="release_year" id="release_year"
                            class="form-control @error('release_year') is-invalid @enderror"
                            value="{{ old('release_year', $watchItem->release_year) }}" min="1900" max="2100" required>
                        @error('release_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="genre">Genre <span class="text-danger">*</span></label>
                        <input type="text" name="genre" id="genre"
                            class="form-control @error('genre') is-invalid @enderror"
                            value="{{ old('genre', $watchItem->genre) }}" placeholder="pisahkan koma, misal: Sci-Fi, Drama">
                        @error('genre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="country">Country</label>
                        <input type="text" name="country" id="country" class="form-control"
                            value="{{ old('country', $watchItem->country) }}" placeholder="USA">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="director_creator">Director / Creator</label>
                        <input type="text" name="director_creator" id="director_creator" class="form-control"
                            value="{{ old('director_creator', $watchItem->director_creator) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="cast_list">Cast</label>
                        <input type="text" name="cast_list" id="cast_list" class="form-control"
                            value="{{ old('cast_list', $watchItem->cast_list) }}" placeholder="pisahkan koma">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="duration">Duration (menit)</label>
                        <input type="number" name="duration" id="duration" class="form-control"
                            value="{{ old('duration', $watchItem->duration) }}" min="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="total_episodes">Total Episodes</label>
                        <input type="number" name="total_episodes" id="total_episodes" class="form-control"
                            value="{{ old('total_episodes', $watchItem->total_episodes) }}" min="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="synopsis">Synopsis</label>
                        <textarea name="synopsis" id="synopsis" class="form-control" rows="4"
                            placeholder="Tuliskan sinopsis singkat...">{{ old('synopsis', $watchItem->synopsis) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="poster">Poster</label>
                        <input type="file" name="poster" id="poster" class="form-control @error('poster') is-invalid @enderror"
                            accept="image/*">
                        <div class="form-text">Format JPG/PNG, maksimal 2 MB. Kosongkan jika tidak diganti.</div>
                        @error('poster')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Poster Saat Ini</label>
                        @if($watchItem->poster)
                            <div>
                                <img src="{{ asset('storage/' . $watchItem->poster) }}"
                                    class="img-thumbnail" alt="{{ $watchItem->title }}"
                                    style="max-height: 300px; width: auto;">
                            </div>
                        @else
                            <div class="text-body-secondary fst-italic py-4">Belum ada poster.</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-star"></i> Your Review</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            @foreach(['Plan to Watch', 'Watching', 'Completed'] as $statusOption)
                                <option value="{{ $statusOption }}"
                                    @selected(old('status', $adminReview ? $adminReview->status : 'Plan to Watch') === $statusOption)>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="episode_watched">Episode Watched</label>
                        <input type="number" name="episode_watched" id="episode_watched" class="form-control"
                            min="0" value="{{ old('episode_watched', $adminReview ? $adminReview->episode_watched : 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="rating">Rating (1-10)</label>
                        <input type="number" name="rating" id="rating" class="form-control"
                            min="1" max="10" step="0.1"
                            value="{{ old('rating', $adminReview ? $adminReview->rating : '') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="review_text">Review</label>
                        <textarea name="review_text" id="review_text" class="form-control" rows="4"
                            placeholder="Bagaimana menurutmu item ini?">{{ old('review_text', $adminReview ? $adminReview->review_text : '') }}</textarea>
                    </div>

                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.manage') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
