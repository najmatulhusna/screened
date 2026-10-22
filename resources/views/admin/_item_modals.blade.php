{{-- Modal Add New Item --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" method="POST" action="{{ route('admin.store') }}" enctype="multipart/form-data" id="addItemForm">
                @csrf
                <input type="hidden" name="_open_modal" value="add">

                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel"><i class="bi bi-plus-circle"></i> Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Data belum lengkap</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-info-circle"></i> Item Info</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label" for="add_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="add_title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title') }}" placeholder="Contoh: Interstellar" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="add_type" class="form-select @error('type') is-invalid @enderror" required>
                                        @foreach(['Movie', 'Drama', 'Series', 'Tv Show'] as $typeOption)
                                            <option value="{{ $typeOption }}" @selected(old('type', 'Movie') === $typeOption)>
                                                {{ $typeOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_release_year">Release Year <span class="text-danger">*</span></label>
                                    <input type="number" name="release_year" id="add_release_year"
                                        class="form-control @error('release_year') is-invalid @enderror"
                                        value="{{ old('release_year') }}" min="1900" max="2100" required>
                                    @error('release_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_genre">Genre <span class="text-danger">*</span></label>
                                    <input type="text" name="genre" id="add_genre"
                                        class="form-control @error('genre') is-invalid @enderror"
                                        value="{{ old('genre') }}" placeholder="pisahkan koma, misal: Sci-Fi, Drama">
                                    @error('genre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_country">Country</label>
                                    <input type="text" name="country" id="add_country" class="form-control"
                                        value="{{ old('country') }}" placeholder="USA">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_director_creator">Director / Creator</label>
                                    <input type="text" name="director_creator" id="add_director_creator" class="form-control"
                                        value="{{ old('director_creator') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_cast_list">Cast</label>
                                    <input type="text" name="cast_list" id="add_cast_list" class="form-control"
                                        value="{{ old('cast_list') }}" placeholder="pisahkan koma">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_duration">Duration (menit)</label>
                                    <input type="number" name="duration" id="add_duration" class="form-control"
                                        value="{{ old('duration') }}" min="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_total_episodes">Total Episodes</label>
                                    <input type="number" name="total_episodes" id="add_total_episodes" class="form-control"
                                        value="{{ old('total_episodes') }}" min="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_synopsis">Synopsis</label>
                                    <textarea name="synopsis" id="add_synopsis" class="form-control" rows="4"
                                        placeholder="Tuliskan sinopsis singkat...">{{ old('synopsis') }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_poster">Poster</label>
                                    <input type="file" name="poster" id="add_poster" class="form-control @error('poster') is-invalid @enderror"
                                        accept="image/*">
                                    <div class="form-text">Format JPG/PNG, maksimal 500 KB.</div>
                                    @error('poster')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-link-45deg"></i> Streaming &amp; Trailer</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label" for="add_trailer_url">Trailer URL</label>
                                    <input type="text" name="trailer_url" id="add_trailer_url"
                                        class="form-control @error('trailer_url') is-invalid @enderror"
                                        value="{{ old('trailer_url') }}"
                                        placeholder="https://www.youtube.com/watch?v=... atau link video trailer">
                                    @error('trailer_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Stream Links (per episode)</label>
                                    @include('admin._stream_links_box')
                                    <div class="form-text">
                                        Movie: 1 link (URL Film). Serial: isi per nomor episode (Ep 1, Ep 2, ...).
                                    </div>
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

                                <div class="col-12">
                                    <label class="form-label" for="add_status">Status</label>
                                    <select name="status" id="add_status" class="form-select">
                                        @foreach(['Plan to Watch', 'Watching', 'Completed'] as $statusOption)
                                            <option value="{{ $statusOption }}" @selected(old('status', 'Plan to Watch') === $statusOption)>
                                                {{ $statusOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_episode_watched">Episode Watched</label>
                                    <input type="number" name="episode_watched" id="add_episode_watched" class="form-control"
                                        min="0" value="{{ old('episode_watched', 0) }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_rating">Rating (1-10)</label>
                                    <input type="number" name="rating" id="add_rating" class="form-control"
                                        min="1" max="10" step="0.1" value="{{ old('rating') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="add_review_text">Review</label>
                                    <textarea name="review_text" id="add_review_text" class="form-control" rows="4"
                                        placeholder="Bagaimana menurutmu item ini?">{{ old('review_text') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
    </div>
</div>

{{-- Modal Edit Item --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" method="POST" action="" enctype="multipart/form-data" id="editItemForm"
                data-action-template="{{ route('admin.update', '__ID__') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_open_modal" value="edit">
                <input type="hidden" name="id" id="edit_id" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-square"></i> Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Data belum lengkap</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-info-circle"></i> Item Info</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label" for="edit_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="edit_title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        placeholder="Contoh: Interstellar" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="edit_type" class="form-select @error('type') is-invalid @enderror" required>
                                        @foreach(['Movie', 'Drama', 'Series', 'Tv Show'] as $typeOption)
                                            <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_release_year">Release Year <span class="text-danger">*</span></label>
                                    <input type="number" name="release_year" id="edit_release_year"
                                        class="form-control @error('release_year') is-invalid @enderror"
                                        min="1900" max="2100" required>
                                    @error('release_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_genre">Genre <span class="text-danger">*</span></label>
                                    <input type="text" name="genre" id="edit_genre"
                                        class="form-control @error('genre') is-invalid @enderror"
                                        placeholder="pisahkan koma, misal: Sci-Fi, Drama">
                                    @error('genre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_country">Country</label>
                                    <input type="text" name="country" id="edit_country" class="form-control" placeholder="USA">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_director_creator">Director / Creator</label>
                                    <input type="text" name="director_creator" id="edit_director_creator" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_cast_list">Cast</label>
                                    <input type="text" name="cast_list" id="edit_cast_list" class="form-control" placeholder="pisahkan koma">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_duration">Duration (menit)</label>
                                    <input type="number" name="duration" id="edit_duration" class="form-control" min="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_total_episodes">Total Episodes</label>
                                    <input type="number" name="total_episodes" id="edit_total_episodes" class="form-control" min="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_synopsis">Synopsis</label>
                                    <textarea name="synopsis" id="edit_synopsis" class="form-control" rows="4"
                                        placeholder="Tuliskan sinopsis singkat..."></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_poster">Poster</label>
                                    <input type="file" name="poster" id="edit_poster" class="form-control @error('poster') is-invalid @enderror"
                                        accept="image/*">
                                    <div class="form-text">Format JPG/PNG, maksimal 500 KB. Kosongkan jika tidak diganti.</div>
                                    @error('poster')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Poster Saat Ini</label>
                                    <div>
                                        <img id="edit_poster_preview" src="" alt="Poster"
                                            class="img-thumbnail d-none" style="max-height: 250px; width: auto;">
                                        <div id="edit_poster_none" class="text-body-secondary fst-italic py-3 d-none">Belum ada poster.</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-link-45deg"></i> Streaming &amp; Trailer</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label" for="edit_trailer_url">Trailer URL</label>
                                    <input type="text" name="trailer_url" id="edit_trailer_url"
                                        class="form-control @error('trailer_url') is-invalid @enderror"
                                        placeholder="https://www.youtube.com/watch?v=... atau link video trailer">
                                    @error('trailer_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Stream Links (per episode)</label>
                                    <div class="stream-links-box" id="edit_stream_links"
                                        data-stream-links data-existing="{}"></div>
                                    <div class="form-text">
                                        Movie: 1 link (URL Film). Serial: isi per nomor episode (Ep 1, Ep 2, ...).
                                    </div>
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

                                <div class="col-12">
                                    <label class="form-label" for="edit_status">Status</label>
                                    <select name="status" id="edit_status" class="form-select">
                                        @foreach(['Plan to Watch', 'Watching', 'Completed'] as $statusOption)
                                            <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_episode_watched">Episode Watched</label>
                                    <input type="number" name="episode_watched" id="edit_episode_watched" class="form-control"
                                        min="0" value="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_rating">Rating (1-10)</label>
                                    <input type="number" name="rating" id="edit_rating" class="form-control"
                                        min="1" max="10" step="0.1">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="edit_review_text">Review</label>
                                    <textarea name="review_text" id="edit_review_text" class="form-control" rows="4"
                                        placeholder="Bagaimana menurutmu item ini?"></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
    </div>
</div>
