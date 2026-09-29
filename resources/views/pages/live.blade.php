@extends('layouts.app')

@section('pageTitle', 'Live Cricket Scores, Upcoming & Today Matches — Where to Watch | CricketKaScore')
@section('meta_description', 'Live cricket score updates, today\'s ongoing matches, upcoming series schedules, match dates, timings, venues, and where to watch (broadcast TV & online live streaming channels) on CricketKaScore.')
@section('meta_keywords', 'live cricket score, today match live score, upcoming cricket matches, completed cricket matches, cricket match schedule, where to watch live cricket, match live streaming, CricketKaScore live')
@section('canonical_url', route('live'))
@section('og_type', 'website')
@section('og_title', 'Live Cricket Scores, Upcoming & Today Matches | CricketKaScore')
@section('og_description', 'Track live scores, upcoming cricket schedules, start timings, venues, and where to watch broadcast channels.')
@section('og_url', route('live'))

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'CollectionPage',
    'name' => 'Live Cricket Scores, Upcoming & Completed Matches — CricketKaScore',
    'url' => route('live'),
    'description' => 'Real-time live cricket scores, today matches, upcoming schedules, match venues, and broadcast streaming channels.',
    'breadcrumb' => [
        chr(64) . 'type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/')
            ],
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 2,
                'name' => 'Live Scores',
                'item' => route('live')
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container py-6 sm:py-8">
    <!-- Header with Primary H1 & On-Page Visible SEO Description -->
    <div class="section-header mb-5 flex flex-col gap-3 pb-4 border-b w-full" style="border-color: var(--border-color); align-items: flex-start !important; text-align: left !important;">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 w-full" style="align-items: flex-start !important; text-align: left !important;">
            <div style="text-align: left !important;">
                <h1 class="section-title text-xl sm:text-2xl font-black flex items-center gap-3 m-0 tracking-tight" style="color: var(--text-main); justify-content: flex-start !important; text-align: left !important;">
                    <span>LIVE MATCH CENTER</span>
                    <span class="badge-live animate-pulse">LIVE</span>
                </h1>
                <p class="mt-2 leading-relaxed" style="color: var(--text-muted); font-size: 0.875rem; max-width: 840px; margin: 6px 0 0 0; text-align: left !important;">
                    Live cricket score updates, today's ongoing matches, upcoming series schedules, match dates, timings, venues, and where to watch (broadcast TV &amp; online live streaming channels) on CricketKaScore.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('matches') }}" class="tag-badge whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">All Schedules &rarr;</a>
            </div>
        </div>
    </div>

    <!-- ========================================================
         3-TAB SEGMENTED NAVIGATION BAR (Upcoming | Today's | Completed)
         ======================================================== -->
    <div class="match-tabs-wrapper mb-6">
        <div class="match-tabs-container" role="tablist">
            <button type="button" 
                    id="tab-btn-upcoming" 
                    role="tab" 
                    aria-selected="{{ $activeTab === 'upcoming' ? 'true' : 'false' }}"
                    aria-controls="panel-upcoming"
                    onclick="switchLiveTab('upcoming')" 
                    class="match-tab-btn {{ $activeTab === 'upcoming' ? 'active' : '' }}">
                <span class="tab-label">Upcoming</span>
                <span class="tab-badge">{{ $upcomingMatches->count() }}</span>
            </button>

            <button type="button" 
                    id="tab-btn-today" 
                    role="tab" 
                    aria-selected="{{ $activeTab === 'today' ? 'true' : 'false' }}"
                    aria-controls="panel-today"
                    onclick="switchLiveTab('today')" 
                    class="match-tab-btn {{ $activeTab === 'today' ? 'active' : '' }}">
                @if($todayMatches->where('effective_status', 'live')->count() > 0)
                    <span class="live-dot-pulse"></span>
                @endif
                <span class="tab-label">Today's</span>
                <span class="tab-badge">{{ $todayMatches->count() }}</span>
            </button>

            <button type="button" 
                    id="tab-btn-completed" 
                    role="tab" 
                    aria-selected="{{ $activeTab === 'completed' ? 'true' : 'false' }}"
                    aria-controls="panel-completed"
                    onclick="switchLiveTab('completed')" 
                    class="match-tab-btn {{ $activeTab === 'completed' ? 'active' : '' }}">
                <span class="tab-label">Completed</span>
                <span class="tab-badge">{{ $completedMatches->count() }}</span>
            </button>
        </div>
    </div>

    <!-- ========================================================
         TAB 1: UPCOMING MATCHES
         ======================================================== -->
    <div id="panel-upcoming" class="tab-match-panel" style="display: {{ $activeTab === 'upcoming' ? 'block' : 'none' }};" role="tabpanel" aria-labelledby="tab-btn-upcoming">
        <div class="matches-list-layout">
            @forelse($upcomingMatches as $m)
                @include('pages.partials.live_match_card', ['m' => $m, 'tabType' => 'upcoming'])
            @empty
                <div class="empty-matches-card">
                    <div class="empty-icon">📅</div>
                    <h3 class="empty-title">No Upcoming Matches Scheduled</h3>
                    <p class="empty-desc">Check back soon or explore today's active matches and completed scorecards.</p>
                    <button type="button" onclick="switchLiveTab('today')" class="empty-btn">View Today's Matches</button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================
         TAB 2: TODAY'S MATCHES (Active Live & Today's Schedule)
         ======================================================== -->
    <div id="panel-today" class="tab-match-panel" style="display: {{ $activeTab === 'today' ? 'block' : 'none' }};" role="tabpanel" aria-labelledby="tab-btn-today">
        <div class="matches-list-layout">
            @forelse($todayMatches as $m)
                @include('pages.partials.live_match_card', ['m' => $m, 'tabType' => 'today'])
            @empty
                <div class="empty-matches-card">
                    <div class="empty-icon">⚡</div>
                    <h3 class="empty-title">No Matches Scheduled For Today</h3>
                    <p class="empty-desc">There are no ongoing matches today. Browse upcoming scheduled fixtures or recent match results.</p>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 14px;">
                        <button type="button" onclick="switchLiveTab('upcoming')" class="empty-btn">View Upcoming</button>
                        <button type="button" onclick="switchLiveTab('completed')" class="empty-btn-outline">View Completed</button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================
         TAB 3: COMPLETED MATCHES (Results & Recent Scorecards)
         ======================================================== -->
    <div id="panel-completed" class="tab-match-panel" style="display: {{ $activeTab === 'completed' ? 'block' : 'none' }};" role="tabpanel" aria-labelledby="tab-btn-completed">
        <div class="matches-list-layout">
            @forelse($completedMatches as $m)
                @include('pages.partials.live_match_card', ['m' => $m, 'tabType' => 'completed'])
            @empty
                <div class="empty-matches-card">
                    <div class="empty-icon">🏆</div>
                    <h3 class="empty-title">No Completed Matches Yet</h3>
                    <p class="empty-desc">Recent results and finalized scorecards will appear here once games finish.</p>
                    <button type="button" onclick="switchLiveTab('today')" class="empty-btn">View Today's Matches</button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- SEO Content & Live Scoring Information Section -->
    <section class="mt-12 p-6 sm:p-8 rounded-2xl border" style="background: var(--bg-card); border-color: var(--border-color);">
        <h2 class="text-base sm:text-lg font-bold text-white mb-3 flex items-center gap-2 m-0">
            <span>🏏</span> Fast Real-Time Live Cricket Scorecard &amp; Fixtures Hub
        </h2>
        <p class="text-xs sm:text-sm text-gray-400 leading-relaxed mb-4 mt-2">
            CricketKaScore provides instantaneous ball-by-ball cricket scores, fixtures, series schedules, toss reports, pitch condition analysis, and official TV/OTT live streaming broadcast directories. Whether tracking the IPL, ICC Men's &amp; Women's World Cups, Big Bash League, CSA T20 Challenge, County Championships, or regional club cricket tournaments, stay connected to every delivery.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t" style="border-color: var(--border-color);">
            <div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1 m-0">⚡ Live Ball-by-Ball</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Real-time ball tracker, batsman strike rates, current run rate, and bowler economy.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider mb-1 m-0">📺 Where To Watch</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Verified broadcast networks, streaming OTT channels, and live digital links for every match.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1 m-0">📅 Complete Fixtures</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Multi-day Test series, T20 leagues, local grassroots matches, and comprehensive match results.</p>
            </div>
        </div>
    </section>
</main>

<style>
/* ========================================================
   MATCH TABS BAR STYLING (Inspired by Screenshot)
   ======================================================== */
.match-tabs-wrapper {
    width: 100%;
    margin-top: 4px;
}

.match-tabs-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: var(--bg-card-secondary, #161b22);
    padding: 6px;
    border-radius: 12px;
    border: 1px solid var(--border-color, #30363d);
}

body.light-theme .match-tabs-container, html.light-theme .match-tabs-container {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.match-tab-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 0.95rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: var(--text-muted, #8b949e);
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    outline: none;
}

.match-tab-btn:hover {
    color: var(--text-main, #ffffff);
    background: rgba(255, 255, 255, 0.06);
}

body.light-theme .match-tab-btn, html.light-theme .match-tab-btn {
    color: #475569;
}

body.light-theme .match-tab-btn:hover, html.light-theme .match-tab-btn:hover {
    color: #0f172a;
    background: rgba(0, 0, 0, 0.05);
}

/* Active tab style matching user's primary blue theme (No red) */
.match-tab-btn.active {
    background: var(--primary, #2563eb) !important;
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
}

.match-tab-btn .tab-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.15);
    color: inherit;
    line-height: 1.2;
}

.match-tab-btn.active .tab-badge {
    background: rgba(255, 255, 255, 0.28);
    color: #ffffff;
}

.live-dot-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #22c55e;
    box-shadow: 0 0 8px #22c55e;
    animation: livePulseAnim 1.4s infinite;
}

.match-tab-btn.active .live-dot-pulse {
    background-color: #ffffff;
    box-shadow: 0 0 8px #ffffff;
}

@keyframes livePulseAnim {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.35); opacity: 0.6; }
}

/* ========================================================
   MATCH CARD STYLING (Inspired by Screenshot)
   ======================================================== */
.matches-list-layout {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.custom-match-card {
    display: block;
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    padding: 16px 20px;
    text-decoration: none !important;
    color: inherit;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.custom-match-card:hover {
    transform: translateY(-2px);
    border-color: #94a3b8;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

/* Top metadata row: Series name, venue, format badge */
.card-top-meta {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--border-color, #f1f5f9);
    padding-bottom: 10px;
    margin-bottom: 14px;
}

.card-series-info {
    flex: 1;
    min-width: 0;
}

.card-series-title {
    font-size: 0.94rem;
    font-weight: 800;
    color: var(--text-main, #0f172a);
    display: flex;
    align-items: center;
    gap: 6px;
    line-height: 1.35;
    margin-bottom: 2px;
}

.card-series-bullet {
    color: var(--primary, #2563eb);
    font-size: 1.15rem;
    line-height: 0;
}

.card-venue-text {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
}

.card-format-badge {
    background: #0f172a;
    color: #38bdf8;
    font-size: 0.72rem;
    font-weight: 900;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.06em;
    border: 1px solid rgba(56, 189, 248, 0.25);
    white-space: nowrap;
    line-height: 1.3;
}

/* 3-Column Match Content Layout: Team 1 | Center Info | Team 2 */
.card-content-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 16px;
}

/* Left Team */
.team-col-left {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: flex-start;
}

/* Right Team */
.team-col-right {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: flex-end;
}

.team-code-title {
    font-size: 1.05rem;
    font-weight: 900;
    color: var(--text-main, #0f172a);
    letter-spacing: -0.02em;
    white-space: nowrap;
}

.team-logo-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: contain;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    padding: 2px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    flex-shrink: 0;
}

.team-logo-fallback {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--bg-card-secondary, #1e293b);
    color: #38bdf8;
    border: 2px solid #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 900;
    letter-spacing: 0.02em;
    flex-shrink: 0;
}

.team-score-badge {
    font-size: 0.92rem;
    font-weight: 800;
    color: #22c55e;
    margin-top: 2px;
    white-space: nowrap;
}

.team-score-badge .score-overs {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
}

/* Center Column */
.center-info-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0 10px;
}

.match-status-title {
    font-size: 0.96rem;
    font-weight: 800;
    margin-bottom: 3px;
    letter-spacing: -0.01em;
}

.match-status-title.upcoming-blue {
    color: #2563eb;
}

.match-status-title.live-red {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(37, 99, 235, 0.12);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.3);
    padding: 2px 10px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 900;
}

.match-status-title.completed-green {
    color: #10b981;
}

.match-timing-str {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-main, #334155);
    margin-top: 2px;
    margin-bottom: 4px;
}

.match-result-summary {
    font-size: 0.82rem;
    font-weight: 700;
    color: #f59e0b;
    margin-top: 3px;
    line-height: 1.35;
    max-width: 320px;
}

/* WHERE TO WATCH BADGE (Requested by user) */
.where-to-watch-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid rgba(56, 189, 248, 0.25);
    color: #38bdf8;
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 0.77rem;
    line-height: 1.3;
    cursor: pointer;
    transition: all 0.18s ease;
}

.where-to-watch-tag:hover {
    background: rgba(56, 189, 248, 0.22);
    border-color: #38bdf8;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.25);
}

body.light-theme .where-to-watch-tag, html.light-theme .where-to-watch-tag {
    background: #f0f9ff;
    border-color: #bae6fd;
    color: #0284c7;
}

body.light-theme .where-to-watch-tag:hover, html.light-theme .where-to-watch-tag:hover {
    background: #e0f2fe;
    border-color: #0284c7;
}

.where-to-watch-tag .wtw-label {
    font-weight: 700;
    color: var(--text-muted, #8b949e);
}

body.light-theme .where-to-watch-tag .wtw-label, html.light-theme .where-to-watch-tag .wtw-label {
    color: #64748b;
}

.where-to-watch-tag .wtw-channels {
    font-weight: 800;
    color: #38bdf8;
}

.wtw-channel-link {
    color: #38bdf8;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 800;
    cursor: pointer;
    transition: color 0.15s ease;
}

.wtw-channel-link:hover {
    color: #ffffff;
    text-decoration: underline;
}

body.light-theme .where-to-watch-tag .wtw-channels, html.light-theme .where-to-watch-tag .wtw-channels {
    color: #0284c7;
}

body.light-theme .wtw-channel-link, html.light-theme .wtw-channel-link {
    color: #0284c7;
}

body.light-theme .wtw-channel-link:hover, html.light-theme .wtw-channel-link:hover {
    color: #0369a1;
}

/* Empty State */
.empty-matches-card {
    background: var(--bg-card, #ffffff);
    border: 1px dashed var(--border-color, #cbd5e1);
    border-radius: 16px;
    padding: 48px 24px;
    text-align: center;
}

.empty-matches-card .empty-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
}

.empty-matches-card .empty-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-main, #0f172a);
    margin: 0;
}

.empty-matches-card .empty-desc {
    font-size: 0.86rem;
    color: var(--text-muted, #64748b);
    max-width: 440px;
    margin: 6px auto 0;
}

.empty-btn {
    display: inline-block;
    margin-top: 16px;
    padding: 8px 18px;
    background: var(--primary, #2563eb);
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 800;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.15s ease;
}

.empty-btn:hover {
    background: var(--primary-hover, #1d4ed8);
}

.empty-btn-outline {
    display: inline-block;
    margin-top: 16px;
    padding: 8px 18px;
    background: transparent;
    color: var(--text-main, #0f172a);
    border: 1px solid var(--border-color, #cbd5e1);
    font-size: 0.82rem;
    font-weight: 800;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.empty-btn-outline:hover {
    background: var(--bg-card-secondary, #f1f5f9);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .match-tab-btn {
        padding: 9px 8px;
        font-size: 0.82rem;
        gap: 4px;
    }
    .custom-match-card {
        padding: 12px 14px;
    }
    .card-content-grid {
        grid-template-columns: 1fr auto 1fr;
        gap: 8px;
    }
    .team-logo-img, .team-logo-fallback {
        width: 36px;
        height: 36px;
        font-size: 0.7rem;
    }
    .team-code-title {
        font-size: 0.88rem;
    }
    .where-to-watch-tag {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
    .match-timing-str {
        font-size: 0.72rem;
    }
}
</style>

<script>
/**
 * Switch Live Page Tabs (Upcoming, Today's, Completed)
 */
function switchLiveTab(tabName) {
    const validTabs = ['upcoming', 'today', 'completed'];
    if (!validTabs.includes(tabName)) return;

    // 1. Update button states
    validTabs.forEach(name => {
        const btn = document.getElementById('tab-btn-' + name);
        const panel = document.getElementById('panel-' + name);
        if (btn) {
            if (name === tabName) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            }
        }
        if (panel) {
            panel.style.display = (name === tabName) ? 'block' : 'none';
        }
    });

    // 2. Update URL query parameter without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

// Check URL query param on initial load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam && ['upcoming', 'today', 'completed'].includes(tabParam)) {
        switchLiveTab(tabParam);
    }
});
</script>
@endsection
