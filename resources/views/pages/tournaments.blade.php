@extends('layouts.app')

@section('pageTitle', 'Cricket Tournaments, Series & Championships Hub — CricketKaScore')
@section('meta_description', 'Explore ongoing, upcoming, and completed cricket tournaments, bilateral series, leagues, and international championships on CricketKaScore.')
@section('meta_keywords', 'cricket tournaments, cricket series, IPL series, ICC world cup, cricket championships, ongoing tournaments, upcoming series, CricketKaScore series')
@section('canonical_url', route('tournaments'))
@section('og_type', 'website')
@section('og_title', 'Cricket Tournaments, Series & Championships Hub | CricketKaScore')
@section('og_description', 'Follow all ongoing, upcoming, and completed cricket tournaments and series on CricketKaScore.')
@section('og_url', route('tournaments'))

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'CollectionPage',
    'name' => 'Cricket Tournaments, Series & Championships Hub',
    'url' => route('tournaments'),
    'description' => 'Complete directory of ongoing, upcoming, and completed cricket tournaments and series.',
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
                'name' => 'Tournaments',
                'item' => route('tournaments')
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container py-6 sm:py-10">
    <!-- Header with Primary H1 & On-Page Visible SEO Description -->
    <div class="section-header mb-6 flex flex-col gap-3 pb-4 border-b w-full" style="border-color: var(--border-color); align-items: flex-start !important; text-align: left !important;">
        <div style="text-align: left !important;">
            <h1 class="text-xl sm:text-2xl font-black uppercase tracking-tight m-0" style="color: var(--text-main); text-align: left !important;">TOURNAMENTS, SERIES &amp; CHAMPIONSHIPS HUB</h1>
            <p class="text-xs sm:text-sm mt-2 leading-relaxed" style="color: var(--text-muted); max-width: 840px; text-align: left !important;">
                Explore comprehensive schedules, points tables, team standings, and match fixtures across international bilateral series, ICC tournaments, franchise leagues, and domestic championships on CricketKaScore.
            </p>
        </div>
    </div>

    <!-- Ongoing Series -->
    <div class="mb-8">
        <h2 class="text-sm sm:text-base font-extrabold text-emerald-400 uppercase tracking-wide mb-3.5 flex items-center gap-2">
            <span>🟢</span> ONGOING TOURNAMENTS &amp; SERIES
        </h2>
        <div class="series-grid">
            @forelse($ongoingSeries as $s)
                <a href="{{ route('tournament.public', $s->id) }}" class="series-card no-underline block" style="cursor: pointer;">
                    <div class="series-info">
                        <h3 class="series-title" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-main);">{{ $s->name }}</h3>
                        <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                    </div>
                </a>
            @empty
                <p class="text-xs py-2" style="color: var(--text-muted);">No ongoing tournaments right now.</p>
            @endforelse
        </div>
    </div>

    <!-- Upcoming Series -->
    <div class="mb-8">
        <h2 class="text-sm sm:text-base font-extrabold text-sky-400 uppercase tracking-wide mb-3.5 flex items-center gap-2">
            <span>🔵</span> UPCOMING TOURNAMENTS &amp; FIXTURES
        </h2>
        <div class="series-grid">
            @forelse($upcomingSeries as $s)
                <a href="{{ route('tournament.public', $s->id) }}" class="series-card no-underline block" style="cursor: pointer;">
                    <div class="series-info">
                        <h3 class="series-title" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text-main);">{{ $s->name }}</h3>
                        <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                    </div>
                </a>
            @empty
                <p class="text-xs py-2" style="color: var(--text-muted);">No upcoming tournaments added yet.</p>
            @endforelse
        </div>
    </div>

    <!-- SEO Content & Tournament Hub Information Section -->
    <section class="mt-12 p-6 sm:p-8 rounded-2xl border" style="background: var(--bg-card); border-color: var(--border-color);">
        <h2 class="text-base sm:text-lg font-bold mb-3 flex items-center gap-2 m-0" style="color: var(--text-main);">
            <span>🏆</span> Global Cricket Series, Tournament Ladders &amp; Championship Hub
        </h2>
        <p class="text-xs sm:text-sm leading-relaxed mb-4 mt-2" style="color: var(--text-muted);">
            CricketKaScore covers every major global tournament, franchise league, and domestic competition. Access comprehensive series portals featuring participating teams, match fixtures, group-stage points tables, net run rates, player leaderboards, and championship playoff brackets.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t" style="border-color: var(--border-color);">
            <div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1 m-0">🏆 ICC &amp; Global Championships</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">ICC Men's &amp; Women's World Cups, Champions Trophy, World Test Championship, and Asia Cup.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider mb-1 m-0">⚡ Franchise &amp; T20 Leagues</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">Indian Premier League (IPL), Big Bash League (BBL), Caribbean Premier League (CPL), and SA20.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1 m-0">🏏 Bilateral &amp; Domestic Trophies</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">Ranji Trophy, County Championship, Syed Mushtaq Ali, and bilateral ODI/Test tours.</p>
            </div>
        </div>
    </section>
</main>
@endsection
