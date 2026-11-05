{{--
    Isi form My Album (Add Manually / Edit) — dipakai oleh:
      - halaman penuh  : user/my-album-form.blade.php
      - modal tambah   : user/my-album.blade.php  (#albumFormModal)
      - modal edit     : user/my-album-detail.blade.php (#albumEditModal)

    Variabel: $entry (null untuk tambah baru).
--}}

{{-- ============ DATA TONTONAN ============ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-2">
        <h2 class="card-title h6 mb-0"><i class="bi bi-film"></i> Data Tontonan</h2>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-12">
                <label class="form-label" for="fTitle">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="fTitle" class="form-control" required
                    maxlength="255" placeholder="Judul film / drama"
                    value="{{ old('title', $entry?->title) }}">
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="fType">Type <span class="text-danger">*</span></label>
                <select name="type" id="fType" class="form-select" required>
                    @foreach(\App\Models\UserWatchlist::TYPES as $typeOption)
                        <option value="{{ $typeOption }}"
                            @selected(old('type', $entry?->type ?? 'Movie') === $typeOption)>
                            {{ $typeOption === 'Tv Show' ? 'TV Show' : $typeOption }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="fYear">Release Year</label>
                <input type="number" name="release_year" id="fYear" class="form-control"
                    min="1900" max="2100" placeholder="2024" value="{{ old('release_year', $entry?->release_year) }}">
                @error('release_year')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label" for="fGenre">Genre</label>
                <input type="text" name="genre" id="fGenre" class="form-control"
                    maxlength="255" placeholder="Action, Thriller, Romance"
                    value="{{ old('genre', $entry?->genre) }}">
                @error('genre')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-3 col-lg-3">
                <label class="form-label" for="fCountry">Country</label>
                <input type="text" name="country" id="fCountry" class="form-control"
                    maxlength="100" placeholder="Korea Selatan" value="{{ old('country', $entry?->country) }}">
                @error('country')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label" for="fDirector">Director / Creator</label>
                <input type="text" name="director_creator" id="fDirector" class="form-control"
                    maxlength="150" placeholder="Nama sutradara / kreator"
                    value="{{ old('director_creator', $entry?->director_creator) }}">
                @error('director_creator')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-5">
                <label class="form-label" for="fCast">Cast</label>
                <input type="text" name="cast_list" id="fCast" class="form-control"
                    maxlength="2000" placeholder="Nama pemeran, dipisah koma"
                    value="{{ old('cast_list', $entry?->cast_list) }}">
                @error('cast_list')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="fDuration">Duration (mnt)</label>
                <input type="number" name="duration" id="fDuration" class="form-control"
                    min="1" max="10000" placeholder="120" value="{{ old('duration', $entry?->duration) }}">
                @error('duration')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="fEpisodes">Total Episodes</label>
                <input type="number" name="total_episodes" id="fEpisodes" class="form-control"
                    min="1" max="5000" placeholder="16" value="{{ old('total_episodes', $entry?->total_episodes) }}">
                @error('total_episodes')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="fSynopsis">Synopsis</label>
                <textarea name="synopsis" id="fSynopsis" class="form-control" rows="4"
                    maxlength="5000" placeholder="Sinopsis singkat...">{{ old('synopsis', $entry?->synopsis) }}</textarea>
                @error('synopsis')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label" for="fPoster">Poster (opsional, JPG/PNG maks 500KB)</label>
                <input type="file" name="poster" id="fPoster" class="form-control"
                    accept="image/jpeg,image/png,image/jpg">
                @error('poster')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                @if($entry?->poster)
                    <div class="album-form-poster mt-2">
                        <img src="{{ asset('storage/' . $entry->poster) }}" alt="Poster saat ini">
                        <span class="small text-body-secondary">Poster saat ini</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ============ DATA PERSONAL ============ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-2">
        <h2 class="card-title h6 mb-0"><i class="bi bi-journal-heart"></i> Data Personal</h2>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="fStatus">Watch Status <span class="text-danger">*</span></label>
                <select name="watch_status" id="fStatus" class="form-select" required>
                    @foreach(\App\Models\UserWatchlist::STATUSES as $statusOption)
                        <option value="{{ $statusOption }}"
                            @selected(old('watch_status', $entry?->watch_status ?? 'Plan to Watch') === $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach
                </select>
                @error('watch_status')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="fRating">Personal Rating (1-10)</label>
                <input type="number" name="personal_rating" id="fRating" class="form-control"
                    min="1" max="10" step="0.1" placeholder="8.5"
                    value="{{ old('personal_rating', $entry?->personal_rating) }}">
                @error('personal_rating')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="fStarted">Date Started</label>
                <input type="date" name="date_started" id="fStarted" class="form-control"
                    value="{{ old('date_started', $entry?->date_started?->format('Y-m-d')) }}">
                @error('date_started')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-3" id="completedDateField">
                <label class="form-label" for="fCompleted">Date Completed</label>
                <input type="date" name="date_completed" id="fCompleted" class="form-control"
                    value="{{ old('date_completed', $entry?->date_completed?->format('Y-m-d')) }}">
                @error('date_completed')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="fNotes">Personal Notes</label>
                <textarea name="notes" id="fNotes" class="form-control" rows="3"
                    maxlength="5000" placeholder="Catatan pribadimu tentang tontonan ini...">{{ old('notes', $entry?->notes) }}</textarea>
                @error('notes')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var status = document.getElementById('fStatus');
    var completed = document.getElementById('completedDateField');
    if (!status || !completed) return;

    function sync() {
        // Date Completed hanya relevan saat status Completed
        completed.classList.toggle('d-none', status.value !== 'Completed');
    }

    status.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
