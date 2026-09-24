@extends('layouts.app')

@section('pageTitle', 'Live Cricket Scores Today — Real-Time Ball by Ball Commentary | CricketKaScore')
@section('meta_description', 'Follow real-time live cricket scores, ball-by-ball commentary, today match live updates, run rate, scorecards, and player stats for international, domestic and local matches on CricketKaScore.')
@section('meta_keywords', 'live cricket score, today match live score, live cricket match today, ball by ball live score, live scorecard, live cricket commentary, IPL live score, ICC live score, local cricket live score, CricketKaScore live')
@section('canonical_url', route('live'))
@section('og_type', 'website')
@section('og_title', 'Live Cricket Scores Today — Real-Time Ball by Ball Commentary | CricketKaScore')
@section('og_description', 'Follow real-time live cricket scores, ball-by-ball commentary, and instant match scorecards on CricketKaScore.')
@section('og_url', route('live'))

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'CollectionPage',
    'name' => 'Live Cricket Scores Today — CricketKaScore',
    'url' => route('live'),
    'description' => 'Real-time live cricket scores, ball-by-ball commentary and scorecards for ongoing matches.',
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
<main class="container py-6 sm:py-10">
    <!-- Header with Primary H1 -->
    <div class="section-header mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="section-title text-xl sm:text-2xl font-black text-white flex items-center gap-3 m-0 tracking-tight">
                <span>LIVE CRICKET SCORES TODAY</span>
                <span class="badge-live animate-pulse">LIVE</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1 mb-0">Fastest real-time ball-by-ball scorecards, commentary &amp; match updates</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('matches') }}" class="tag-badge whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">All Matches &rarr;</a>
        </div>
    </div>

    <!-- Live Matches Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($liveMatches as $m)
            <a href="{{ route('matches.detail', $m->id) }}" class="match-card p-4 sm:p-5 no-underline block">
                <div class="match-card-header mb-3 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-gray-300 truncate max-w-[200px] m-0">
                        {{ $m->level_type ?? ($m->match_type . ' - ' . ($m->venue->name ?? 'WANKHEDE')) }}
                    </h2>
                    <span class="badge-live">LIVE</span>
                </div>

                <div class="match-card-teams flex flex-col gap-3 my-3">
                    <div class="team-row flex items-center justify-between gap-3">
                        <div class="team-info flex items-center gap-2">
                            <div class="team-avatar w-7 h-7 sm:w-8 sm:h-8 text-xs font-black flex items-center justify-center rounded-full" style="border-color: {{ $m->team1?->color_code ?? 'var(--primary)' }};">
                                {{ $m->team1?->short_name ?? ($m->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team1->name), 0, 3)) : '') }}
                            </div>
                            <h3 class="team-name text-sm sm:text-base font-semibold text-white m-0">{{ $m->team1?->name ?? '' }}</h3>
                        </div>
                        <div class="team-score text-sm sm:text-base font-bold text-emerald-400">
                            {{ $m->team1_score }}/{{ $m->team1_wickets }} <span class="text-xs text-gray-400">({{ $m->team1_overs }} ov)</span>
                        </div>
                    </div>

                    <div class="team-row flex items-center justify-between gap-3">
                        <div class="team-info flex items-center gap-2">
                            <div class="team-avatar w-7 h-7 sm:w-8 sm:h-8 text-xs font-black flex items-center justify-center rounded-full" style="border-color: {{ $m->team2?->color_code ?? '#38bdf8' }};">
                                {{ $m->team2?->short_name ?? ($m->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team2->name), 0, 3)) : '') }}
                            </div>
                            <h3 class="team-name text-sm sm:text-base font-semibold text-white m-0">{{ $m->team2?->name ?? '' }}</h3>
                        </div>
                        <div class="team-score text-sm sm:text-base font-bold text-sky-400">
                            @if($m->team2_score > 0)
                                {{ $m->team2_score }}/{{ $m->team2_wickets }} <span class="text-xs text-gray-400">({{ $m->team2_overs }} ov)</span>
                            @else
                                <span class="text-xs text-gray-400">Yet to bat</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="match-card-footer pt-3 flex items-center justify-between text-xs text-gray-400 border-t border-white/5">
                    <span class="truncate max-w-[150px]">📍 {{ $m->venue->name ?? 'Wankhede Stadium' }}</span>
                    <span class="text-sky-400 font-bold truncate max-w-[130px]">{{ $m->custom_note ?? 'Match in progress' }}</span>
                </div>
            </a>
        @empty
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-10 text-center col-span-full">
                <div class="text-3xl mb-2">⚡</div>
                <h2 class="text-lg font-bold text-white">No Live Matches Right Now</h2>
                <p class="text-sm text-gray-400 mt-1">Check scheduled matches to see upcoming games.</p>
                <a href="{{ route('matches') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white font-bold rounded-lg text-xs">View Schedule</a>
            </div>
        @endforelse
    </div>

    <!-- SEO Content & Live Scoring Highlights -->
    <section class="mt-12 p-6 sm:p-8 rounded-2xl border" style="background: var(--bg-card); border-color: var(--border-color);">
        <h2 class="text-base sm:text-lg font-bold text-white mb-3 flex items-center gap-2 m-0">
            <span>🏏</span> Fast Real-Time Live Cricket Scorecard &amp; Ball-by-Ball Updates
        </h2>
        <p class="text-xs sm:text-sm text-gray-400 leading-relaxed mb-4 mt-2">
            CricketKaScore offers an ultra-responsive, real-time live cricket score experience for international clashes, domestic leagues, franchise tournaments like IPL, PSL, BBL, and grassroots local cricket. Each ball bowled updates bowler statistics, batsman strike rates, current run rate (CRR), required run rate (RRR), and partnership records instantly.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t" style="border-color: var(--border-color);">
            <div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1 m-0">⚡ Instant Refresh</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Ball-by-ball automated score sync without needing manual page reloads.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider mb-1 m-0">📊 Complete Scorecard</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Detailed batting tables, bowling economy rates, extras, and fall of wickets.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1 m-0">🎯 Predictions &amp; Odds</h3>
                <p class="text-xs text-gray-400 mb-0 mt-1">Live match context, toss updates, pitch conditions, and expert fantasy cricket picks.</p>
            </div>
        </div>
    </section>
</main>
@endsection
