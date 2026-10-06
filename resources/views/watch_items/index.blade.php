@extends('layouts.public')

@section('title', 'Collection - SCREENED')

@section('content')
<div class="container collection-page">
    <div class="collection-head page-head">
        <h1>Collection</h1>
    </div>

    <div class="card collection-filters">
        <div class="card-body p-2">
            <form action="{{ route('collection') }}" method="GET">
                <div class="row g-2 align-items-end">

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small mb-1" for="search">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="search" id="search" class="form-control"
                                placeholder="Cari judul film..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small mb-1" for="type">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All types</option>
                            @foreach(['Movie', 'Drama', 'Series', 'TV Show'] as $typeOption)
                                <option value="{{ $typeOption }}" @selected(request('type') === $typeOption)>
                                    {{ $typeOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small mb-1" for="genre">Genre</label>
                        <select name="genre" id="genre" class="form-select">
                            <option value="">All Genres</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2 col-lg-1">
                        <label class="form-label small mb-1" for="year">Year</label>
                        <input type="number" name="year" id="year" class="form-control"
                            placeholder="2024" value="{{ request('year') }}" min="1900" max="2100">
                    </div>

                    <div class="col-6 col-md-2 col-lg-2">
                        <label class="form-label small mb-1" for="sort">Sort</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="recent" @selected(request('sort') === 'recent')>Recently Added</option>
                            <option value="title" @selected(request('sort') === 'title')>Title A-Z</option>
                            <option value="year" @selected(request('sort') === 'year')>Release Year</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 col-lg-2 d-grid d-md-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Apply
                        </button>
                        <a href="{{ route('collection') }}" class="btn btn-outline-secondary flex-fill">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
        @forelse($watchItems as $item)
            <div class="col">
                <div class="card h-100 shadow-sm overflow-hidden">
                    <a href="{{ route('watchitem.detail', $item->id) }}" class="text-decoration-none text-reset d-block">
                        <div class="position-relative">
                            <div class="ratio ratio-16x9">
                                @if($item->poster)
                                    <img src="{{ asset('storage/' . $item->poster) }}"
                                        class="card-img-top object-fit-cover" alt="{{ $item->title }}">
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary">
                                        <i class="bi bi-film fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="badge bg-dark position-absolute top-0 end-0 m-1">{{ $item->type }}</span>
                        </div>

                        <div class="card-body p-2">
                            <div class="fw-semibold small text-truncate" title="{{ $item->title }}">{{ $item->title }}</div>
                            <div class="small text-body-secondary text-truncate">
                                {{ $item->release_year }} &middot; {{ $item->genre }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card empty-state">
                    <div class="card-body text-center text-body-secondary py-5">
                        <i class="bi bi-search fs-1 d-block mb-2"></i>
                        No items found.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection


