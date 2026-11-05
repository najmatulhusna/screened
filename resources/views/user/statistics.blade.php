@extends('layouts.public')

@section('title', 'Statistics - SCREENED')

@section('content')
@php
    // warna status mengikuti palet SCREENED (success / biru / gold / grey / danger)
    $statusColors = [
        'Completed'    => '#6fae7c',
        'Watching'     => '#7f9cc0',
        'Plan to Watch'=> '#c9a24d',
        'On Hold'      => '#9a9a9e',
        'Dropped'      => '#c96a4d',
    ];
@endphp
<div class="container stats-page">

    {{-- ============ A. PAGE HEADING ============ --}}
    <div class="page-head">
        <h1>My Statistics</h1>
        <p>Your personal watch insights</p>
    </div>

    {{-- ============ B. SUMMARY CARDS (Free & VIP) ============ --}}
    <div class="stats-summary">
        <p>
            <i class="bi bi-collection-play stat-icon" aria-hidden="true"></i>
            <span class="stat-value">{{ $totalTitles }}</span>
            <span class="stat-label">Total Titles</span>
        </p>
        <p>
            <i class="bi bi-check2-circle stat-icon" aria-hidden="true"></i>
            <span class="stat-value">{{ $totalWatched }}</span>
            <span class="stat-label">Completed</span>
        </p>
        <p>
            <i class="bi bi-play-circle stat-icon" aria-hidden="true"></i>
            <span class="stat-value">{{ $totalWatching }}</span>
            <span class="stat-label">Watching</span>
        </p>
        <p>
            <i class="bi bi-heart-fill stat-icon" aria-hidden="true"></i>
            <span class="stat-value">{{ $totalFavorites }}</span>
            <span class="stat-label">Favorites</span>
        </p>
    </div>

    {{-- ============ C. WATCH DISTRIBUTION (VIP) ============ --}}
    @if($isVip)
    <h2>Watch Distribution</h2>

    @if(! $hasWatchData)
        <div class="card empty-state">
            <div class="card-body text-center text-body-secondary py-5">
                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                No watch activity yet. Start building your collection!
            </div>
        </div>
    @else
        <div class="card stat-panel">
            <div class="card-body">
                <div class="stat-panel-head">
                    <span class="panel-note"><i class="bi bi-pie-chart"></i>Status tontonan dari koleksi pribadimu</span>
                    <span class="panel-note">{{ $totalTitles }} judul</span>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="chart-holder chart-donut">
                            <canvas id="distributionChart" role="img" aria-label="Watch distribution"></canvas>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <ul class="dist-legend">
                            @foreach($distribution as $stat)
                                <li>
                                    <span class="dist-dot" style="background: {{ $statusColors[$stat['status']] }}"></span>
                                    <span class="dist-name">{{ $stat['status'] }}</span>
                                    <span class="dist-count">{{ $stat['count'] }}</span>
                                    <span class="dist-percent">{{ $stat['percent'] }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @endif

    {{-- ============ D. GENRE STATISTICS (VIP) ============ --}}
    @if($isVip)
    <h2>Genre Statistics</h2>

    @if($genres->isEmpty())
        <div class="card empty-state">
            <div class="card-body text-center text-body-secondary py-5">
                <i class="bi bi-film fs-1 d-block mb-2"></i>
                No genre data yet. Add some titles to your collection first!
            </div>
        </div>
    @else
        <div class="card stat-panel">
            <div class="card-body">
                <div class="stat-panel-head">
                    <span class="panel-note"><i class="bi bi-bar-chart-line"></i>Genre paling sering kamu tonton</span>
                    <span class="panel-note">{{ $genres->count() }} genre</span>
                </div>

                <div class="chart-holder chart-bar">
                    <canvas id="genreChart" role="img" aria-label="Genre statistics"></canvas>
                </div>
            </div>
        </div>
    @endif
    @endif

    {{-- ============ E. MONTHLY WATCH ACTIVITY (VIP) ============ --}}
    @if($isVip)
    <h2>Monthly Watch Activity</h2>

        <div class="card stat-panel">
            <div class="card-body">
                <div class="stat-panel-head">
                    <span class="panel-note"><i class="bi bi-calendar3"></i>Judul selesai ditonton per bulan &middot; {{ $year }}</span>

                    <form method="GET" action="{{ route('statistics') }}" class="year-filter">
                        <label class="form-label" for="statsYear">Year</label>
                        <select name="year" id="statsYear" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($years as $availableYear)
                                <option value="{{ $availableYear }}" {{ (int) $availableYear === $year ? 'selected' : '' }}>
                                    {{ $availableYear }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                    </form>
                </div>

                @if($yearTotal === 0)
                    <div class="stats-empty">
                        <i class="bi bi-calendar-x"></i>
                        Belum ada judul yang selesai ditonton pada {{ $year }}.
                    </div>
                @else
                    <div class="chart-holder chart-bar">
                        <canvas id="monthlyChart" role="img" aria-label="Monthly watch activity {{ $year }}"></canvas>
                    </div>
                @endif

                {{-- Yearly Watch Recap --}}
                <div class="recap-row">
                    <span class="recap-pill"><b>{{ $recap['completed'] }}</b> Completed {{ $year }}</span>
                    <span class="recap-pill"><b>{{ $recap['added'] }}</b> Added {{ $year }}</span>
                    <span class="recap-pill">
                        <b>{{ $recap['avg_rating'] !== null ? number_format($recap['avg_rating'], 1) : '—' }}</b>
                        Avg rating {{ $year }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ F. MY HISTORY (dipertahankan seperti semula) ============ --}}
    <h2>My History</h2>

    <div class="history-meta">
        {{ $historyTitles }} {{ \Illuminate\Support\Str::plural('title', $historyTitles) }} tracked
        &middot; {{ $totalReviewText }} {{ \Illuminate\Support\Str::plural('review', $totalReviewText) }}
        &middot; Average rating {{ $avgRating !== null ? number_format($avgRating, 1) : '—' }}/10
    </div>

    {{-- ============ Activity timeline (aktivitas terbaru, maks 10 + View All) ============ --}}
    @if($activityCount > 0)
        <p class="history-sub"><i class="bi bi-activity" aria-hidden="true"></i> Recent Activity</p>

        <div class="activity-timeline" id="activityTimeline">
            @foreach($activities as $index => $activity)
                <div class="activity-item{{ $index >= 10 ? ' activity-hidden' : '' }}">
                    <span class="activity-icon"><i class="bi {{ $activity['icon'] }}" aria-hidden="true"></i></span>
                    <div class="activity-body">
                        <a class="activity-title" href="{{ route('watchitem.detail', $activity['item_id']) }}">
                            {{ $activity['title'] }}
                        </a>
                        <p class="activity-desc">{{ $activity['description'] }}</p>
                        <span class="activity-date">
                            {{ $activity['date']->format('d M Y') }} &middot; {{ $activity['date']->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if($activityCount > 10)
            <button type="button" class="btn btn-sm btn-outline-primary activity-view-all"
                id="activityViewAll" data-count="{{ $activityCount }}">
                View All ({{ $activityCount }})
            </button>
        @endif
    @endif

    <p class="history-sub"><i class="bi bi-clock-history" aria-hidden="true"></i> Full History</p>

    @forelse($reviews as $review)
        <div class="review-card">
            <a href="{{ route('watchitem.detail', $review->watchItem->id) }}">
                <strong>{{ $review->watchItem->title }}</strong>
            </a>
            <span>{{ $review->rating }}/10</span>
            <span>{{ $review->status }}</span>
            <span>Ep: {{ $review->episode_watched }}</span>
            <p>{{ $review->review_text }}</p>

            <form method="POST" action="{{ route('watchitem.progress.destroy', $review->watch_items_id) }}"
                class="review-card-actions">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                    data-confirm
                    data-title="Hapus dari History?"
                    data-text="Progress, rating, dan review milikmu untuk {{ $review->watchItem->title }} akan dihapus permanen."
                    data-submit="Ya, Hapus">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </form>
        </div>
    @empty
        <p>Belum ada histori.</p>
    @endforelse
</div>

@endsection

@push('scripts')
    @if($isVip)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
        (function () {
            if (typeof Chart === 'undefined') return;

            var GOLD = '#c9a24d';
            var GRID = 'rgba(255, 255, 255, .06)';
            var TOOLTIP = {
                backgroundColor: '#151517',
                borderColor: '#2a2a2d',
                borderWidth: 1,
                titleColor: '#f5f5f0',
                bodyColor: '#c9a24d',
                padding: 10,
                displayColors: false
            };

            Chart.defaults.color = '#9a9a9e';
            Chart.defaults.font.family = "'DM Sans', sans-serif";
            Chart.defaults.font.size = 11;

            var statusColors = @json($statusColors);
            var distLabels = @json($distribution->pluck('status'));
            var distValues = @json($distribution->pluck('count'));

            /* --- Donut: Watch Distribution --- */
            var distCanvas = document.getElementById('distributionChart');
            if (distCanvas) {
                new Chart(distCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: distLabels,
                        datasets: [{
                            data: distValues,
                            backgroundColor: distLabels.map(function (label) { return statusColors[label] || GOLD; }),
                            borderColor: '#1a1a1d',
                            borderWidth: 2,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: { display: false },
                            tooltip: Object.assign({}, TOOLTIP, {
                                callbacks: {
                                    label: function (context) {
                                        var total = distValues.reduce(function (sum, value) { return sum + value; }, 0);
                                        var percent = total ? Math.round(context.parsed * 1000 / total) / 10 : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percent + '%)';
                                    }
                                }
                            })
                        }
                    }
                });
            }

            /* --- Horizontal bar: Genre Statistics --- */
            var genreCanvas = document.getElementById('genreChart');
            if (genreCanvas) {
                var genreLabels = @json($genres->pluck('genre'));
                var genreValues = @json($genres->pluck('count'));

                var valueAfterBar = {
                    id: 'valueAfterBar',
                    afterDatasetsDraw: function (chart) {
                        var ctx = chart.ctx;
                        ctx.save();
                        ctx.fillStyle = '#9a9a9e';
                        ctx.font = "600 11px 'DM Sans', sans-serif";
                        ctx.textBaseline = 'middle';

                        chart.data.datasets.forEach(function (dataset) {
                            chart.getDatasetMeta(chart.data.datasets.indexOf(dataset)).data.forEach(function (bar, index) {
                                ctx.fillText(String(dataset.data[index]), bar.x + 7, bar.y);
                            });
                        });
                        ctx.restore();
                    }
                };

                new Chart(genreCanvas, {
                    type: 'bar',
                    data: {
                        labels: genreLabels,
                        datasets: [{
                            data: genreValues,
                            backgroundColor: 'rgba(201, 162, 77, .75)',
                            hoverBackgroundColor: GOLD,
                            borderRadius: 4,
                            barPercentage: .72,
                            categoryPercentage: .85
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { right: 36 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: Object.assign({}, TOOLTIP, {
                                callbacks: {
                                    label: function (context) {
                                        return context.parsed.x + ' judul';
                                    }
                                }
                            })
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0, color: '#9a9a9e' },
                                grid: { color: GRID, drawBorder: false }
                            },
                            y: {
                                ticks: { color: '#f5f5f0', font: { size: 12, weight: '600' } },
                                grid: { display: false }
                            }
                        }
                    },
                    plugins: [valueAfterBar]
                });
            }

            /* --- Bar: Monthly Watch Activity --- */
            var monthlyCanvas = document.getElementById('monthlyChart');
            if (monthlyCanvas) {
                new Chart(monthlyCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            data: @json($monthly),
                            backgroundColor: 'rgba(201, 162, 77, .78)',
                            hoverBackgroundColor: GOLD,
                            borderRadius: 4,
                            maxBarThickness: 34
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: Object.assign({}, TOOLTIP, {
                                callbacks: {
                                    label: function (context) {
                                        return context.parsed.y + ' judul selesai';
                                    }
                                }
                            })
                        },
                        scales: {
                            x: {
                                ticks: { color: '#9a9a9e' },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0, color: '#9a9a9e' },
                                grid: { color: GRID, drawBorder: false }
                            }
                        }
                    }
                });
            }
        })();
        </script>
    @endif
@endpush

@push('scripts')
    <script>
    (function () {
        var btn = document.getElementById('activityViewAll');
        var list = document.getElementById('activityTimeline');
        if (!btn || !list) return;

        btn.addEventListener('click', function () {
            var expanded = list.classList.toggle('timeline-expanded');
            btn.textContent = expanded ? 'Show Less' : 'View All (' + btn.dataset.count + ')';
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    })();
    </script>
@endpush
