@extends('layouts.app')

@section('pageTitle', 'Cricket Series & Tournaments — Live Schedule, Points Table, Stats & Squads | CricketKaScore')
@section('meta_description', 'Explore all ongoing, upcoming, and completed cricket series and tournaments. View match schedules, live points table, batting/bowling statistics, and participating team squads on CricketKaScore.')
@section('meta_keywords', 'cricket series, cricket tournaments, upcoming cricket series, ongoing cricket series, IPL 2026, World Championship of Legends, cricket schedule, points table, cricket squads, CricketKaScore')
@section('canonical_url', route('series'))

@section('content')
<!-- Breadcrumb Bar -->
<div style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color); padding: 10px 20px;">
    <div class="max-w-6xl mx-auto" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 0.82rem; font-weight: 700; color: var(--text-muted); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ url('/') }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">Cricket Series</span>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-dim);">
            <span>⚡ {{ $allSeries->count() }} Total Series Available</span>
        </div>
    </div>
</div>

<!-- Series Hub Hero Banner -->
<div class="series-hub-hero" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 32px 20px;">
    <div class="max-w-6xl mx-auto">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.35); color: #38bdf8; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                    <span>🏆</span> CRICKET CHAMPIONSHIPS &amp; TOURNAMENTS
                </div>
                <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: -0.02em; margin: 0 0 8px 0; line-height: 1.2;">
                    Cricket Series Hub
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 650px; line-height: 1.5;">
                    Stay updated with complete coverage of international, domestic, and league cricket tournaments. Select any series below to access dedicated live scores, stats, fixtures, points table, and squads.
                </p>
            </div>

            <!-- Series Quick Stats Summary -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 18px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: #22c55e;">{{ $ongoingSeries->count() }}</div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Ongoing</div>
                </div>
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 18px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: #38bdf8;">{{ $upcomingSeries->count() }}</div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Upcoming</div>
                </div>
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 18px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 900; color: #a855f7;">{{ $completedSeries->count() }}</div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Completed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 pb-28">

    <!-- Filter Tabs & Search Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 26px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
        
        <!-- Categorized Tabs: Ongoing, Upcoming, Completed, All -->
        <div style="display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none;" class="no-scrollbar">
            <button type="button" onclick="switchSeriesListTab('tab-ongoing', this)" class="series-hub-tab active" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid #2563eb; background: #2563eb; color: #ffffff; transition: all 0.2s;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px #22c55e;"></span>
                ONGOING ({{ $ongoingSeries->count() }})
            </button>
            <button type="button" onclick="switchSeriesListTab('tab-upcoming', this)" class="series-hub-tab" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>⏳</span> UPCOMING ({{ $upcomingSeries->count() }})
            </button>
            <button type="button" onclick="switchSeriesListTab('tab-completed', this)" class="series-hub-tab" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>🏁</span> COMPLETED ({{ $completedSeries->count() }})
            </button>
            <button type="button" onclick="switchSeriesListTab('tab-all', this)" class="series-hub-tab" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>🌐</span> ALL SERIES ({{ $allSeries->count() }})
            </button>
        </div>

        <!-- Realtime Search Input -->
        <div style="min-width: 240px; flex-grow: 1; max-width: 320px;">
            <input type="text" id="seriesHubSearch" oninput="filterSeriesCards()" placeholder="Search series or tournament..." style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); padding: 9px 16px; border-radius: 10px; font-size: 0.86rem; color: var(--text-main); outline: none;">
        </div>
    </div>

    <!-- TAB 1: ONGOING SERIES -->
    <div id="tab-ongoing" class="series-list-pane">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 series-cards-grid">
            @forelse($ongoingSeries as $series)
                @include('pages.partials.series_card', ['series' => $series, 'badgeClass' => 'bg-green-600'])
            @empty
                <div class="no-series-box" style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 40px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🏏</div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">No Ongoing Series At This Moment</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">Check out upcoming tournaments scheduled in the coming weeks.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: UPCOMING SERIES -->
    <div id="tab-upcoming" class="series-list-pane" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 series-cards-grid">
            @forelse($upcomingSeries as $series)
                @include('pages.partials.series_card', ['series' => $series, 'badgeClass' => 'bg-blue-600'])
            @empty
                <div class="no-series-box" style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 40px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">⏳</div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">No Upcoming Series Scheduled</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">New fixtures and tournaments are added daily. Stay tuned!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 3: COMPLETED SERIES -->
    <div id="tab-completed" class="series-list-pane" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 series-cards-grid">
            @forelse($completedSeries as $series)
                @include('pages.partials.series_card', ['series' => $series, 'badgeClass' => 'bg-purple-600'])
            @empty
                <div class="no-series-box" style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 40px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🏁</div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">No Completed Series Archived</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">Finished series records will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: ALL SERIES -->
    <div id="tab-all" class="series-list-pane" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 series-cards-grid">
            @forelse($allSeries as $series)
                @include('pages.partials.series_card', ['series' => $series, 'badgeClass' => 'bg-gray-600'])
            @empty
                <div class="no-series-box" style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 40px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🏏</div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">No Cricket Series Found</h3>
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
function switchSeriesListTab(tabId, btn) {
    document.querySelectorAll('.series-list-pane').forEach(p => p.style.display = 'none');
    const target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
    }

    document.querySelectorAll('.series-hub-tab').forEach(b => {
        b.style.background = 'var(--bg-card)';
        b.style.color = 'var(--text-muted)';
        b.style.borderColor = 'var(--border-color)';
    });

    if (btn) {
        btn.style.background = '#2563eb';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#2563eb';
    }
}

function filterSeriesCards() {
    const query = document.getElementById('seriesHubSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.series-hub-card');
    cards.forEach(card => {
        const text = card.getAttribute('data-search') || '';
        if (!query || text.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
