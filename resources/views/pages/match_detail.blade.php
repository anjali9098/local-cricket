@extends('layouts.app')

@php
    $t1Name = $match->team1?->name ?? 'Team A';
    $t2Name = $match->team2?->name ?? 'Team B';
    $mTitle = $match->title ?? ($t1Name . ' vs ' . $t2Name);
    $vName  = $match->venue?->name ?? (is_string($match->venue) ? $match->venue : 'Wankhede Stadium');
    
    $seoTitle = $mTitle . ' Live Scorecard & Match Details | CricketKaScore';
    $seoDesc  = 'Live cricket score, ball-by-ball commentary, scorecard, overs, run rates, win probability, venue info, and status for ' . $mTitle . ' at ' . $vName . ' on CricketKaScore.';
    $seoKeywords = strtolower($mTitle) . ', ' . strtolower($t1Name) . ' vs ' . strtolower($t2Name) . ', live cricket score, scorecard, commentary, ' . strtolower($vName) . ', CricketKaScore match center';
    $canonicalUrl = $match->url;
@endphp

@section('pageTitle', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords', $seoKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDesc)
@section('og_url', $canonicalUrl)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'SportsEvent',
    'name' => $mTitle,
    'url' => $canonicalUrl,
    'description' => $seoDesc,
    'startDate' => $match->match_date ?? date('c'),
    'eventStatus' => $match->status === 'live' ? 'https://schema.org/EventLive' : ($match->status === 'completed' ? 'https://schema.org/EventPostponed' : 'https://schema.org/EventScheduled'),
    'location' => [
        chr(64) . 'type' => 'Place',
        'name' => $vName
    ],
    'competitor' => [
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t1Name
        ],
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t2Name
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container py-6 sm:py-10 max-w-5xl mx-auto">

    <!-- Match Header Card -->
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 mb-6 shadow-md">
        
        <div class="flex items-center justify-between flex-wrap gap-2 mb-4 sm:mb-6">
            <div class="text-[11px] sm:text-xs font-extrabold text-gray-400 uppercase tracking-wide">
                {{ $match->match_type }} &bull; {{ $match->level_type ?? 'INTERNATIONAL' }} &bull; {{ $match->venue->name ?? 'WANKHEDE STADIUM' }}
            </div>
            @if($match->status === 'live')
                <span class="badge-live text-xs">🔴 LIVE</span>
            @else
                <span class="tag-badge text-xs">{{ strtoupper($match->status) }}</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 items-center mb-4 sm:mb-6 pb-4 sm:pb-6 border-b border-[#30363d]">
            <!-- Team 1 -->
            <div class="flex items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-xl border border-[#30363d] bg-[#161b22]/60 match-header-team-card">
                <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
                    <div class="team-avatar w-12 h-12 rounded-full overflow-hidden flex items-center justify-center font-black text-sm flex-shrink-0 border-2" style="border-color: {{ $match->team1?->color_code ?? 'var(--primary)' }};">
                        @if(!empty($match->team1?->logo))
                            <img src="{{ $match->team1->logo }}" alt="{{ $match->team1->name }}" class="w-full h-full object-contain p-0.5 bg-white" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="team-fallback-text font-black text-sm" style="display:none;">{{ $match->team1?->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team1?->name ?? 'T1'), 0, 3)) }}</span>
                        @else
                            <span class="team-fallback-text font-black text-sm">{{ $match->team1?->short_name ?? ($match->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team1->name), 0, 3)) : 'T1') }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team1?->name ?? '' }}</h2>
                    </div>
                </div>
                <div class="text-right ml-auto flex-shrink-0 pl-2">
                    <div class="font-heading text-lg sm:text-2xl font-black text-white">
                        {{ $match->team1_score }}/{{ $match->team1_wickets }}
                    </div>
                    <div class="text-xs text-gray-400">({{ $match->team1_overs }} ov)</div>
                </div>
            </div>

            <!-- Team 2 -->
            <div class="flex items-center justify-between gap-3 sm:gap-4 p-3.5 sm:p-4 rounded-xl border border-[#30363d] bg-[#161b22]/60 match-header-team-card">
                <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
                    <div class="team-avatar w-12 h-12 rounded-full overflow-hidden flex items-center justify-center font-black text-sm flex-shrink-0 border-2" style="border-color: {{ $match->team2?->color_code ?? '#38bdf8' }};">
                        @if(!empty($match->team2?->logo))
                            <img src="{{ $match->team2->logo }}" alt="{{ $match->team2->name }}" class="w-full h-full object-contain p-0.5 bg-white" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="team-fallback-text font-black text-sm" style="display:none;">{{ $match->team2?->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team2?->name ?? 'T2'), 0, 3)) }}</span>
                        @else
                            <span class="team-fallback-text font-black text-sm">{{ $match->team2?->short_name ?? ($match->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team2->name), 0, 3)) : 'T2') }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team2?->name ?? '' }}</h2>
                    </div>
                </div>
                <div class="text-right ml-auto flex-shrink-0 pl-2">
                    <div class="font-heading text-lg sm:text-2xl font-black text-white">
                        @if($match->team2_score > 0)
                            {{ $match->team2_score }}/{{ $match->team2_wickets }}
                        @else
                            &mdash;
                        @endif
                    </div>
                    @if($match->team2_score > 0)
                        <div class="text-xs text-gray-400">({{ $match->team2_overs }} ov)</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Match Status Note -->
        <div class="text-emerald-400 font-bold text-xs sm:text-sm pt-3 border-t border-white/5">
            {{ $match->custom_note ?? (($match->team1?->name && $match->team2?->name) ? $match->team1->name . ' vs ' . $match->team2->name . ' — in progress' : 'Match in progress') }}
        </div>
    </div>

    <!-- Dynamic Stats & Win Probability Widget -->
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 mb-6 shadow-md">
        
        <!-- Win Probability -->
        <h4 class="text-xs font-extrabold text-gray-400 uppercase mb-3 tracking-wider">Win Probability</h4>
        <div class="flex items-center justify-between mb-2 text-xs sm:text-sm font-bold text-white">
            <span>{{ $match->team1->short_name }} ({{ $stats['win_probability']['team1'] }}%)</span>
            @if($stats['win_probability']['draw'] > 0)
                <span>Draw ({{ $stats['win_probability']['draw'] }}%)</span>
            @endif
            <span>{{ $match->team2->short_name }} ({{ $stats['win_probability']['team2'] }}%)</span>
        </div>
        
        <!-- Segmented bar -->
        <div class="flex h-2.5 rounded-full overflow-hidden bg-white/5 mb-5">
            <div style="width: {{ $stats['win_probability']['team1'] }}%;" class="bg-gradient-to-r from-blue-600 to-blue-400"></div>
            @if($stats['win_probability']['draw'] > 0)
                <div style="width: {{ $stats['win_probability']['draw'] }}%;" class="bg-slate-500"></div>
            @endif
            <div style="width: {{ $stats['win_probability']['team2'] }}%;" class="bg-gradient-to-r from-sky-500 to-sky-400"></div>
        </div>

        <!-- Key Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-[#30363d] pt-4 text-xs sm:text-sm">
            <div>
                <span class="text-gray-400 block text-[11px] uppercase font-bold mb-0.5">CRR</span>
                <strong class="text-white text-sm sm:text-base">{{ $stats['crr'] }}</strong>
            </div>
            @if($stats['rrr'] > 0)
                <div>
                    <span class="text-gray-400 block text-[11px] uppercase font-bold mb-0.5">Required RR</span>
                    <strong class="text-white text-sm sm:text-base">{{ $stats['rrr'] }}</strong>
                </div>
            @endif
            <div>
                <span class="text-gray-400 block text-[11px] uppercase font-bold mb-0.5">Overs Left</span>
                <strong class="text-white text-sm sm:text-base">{{ $stats['overs_left'] }}</strong>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <span class="text-gray-400 block text-[11px] uppercase font-bold mb-0.5">Partnership</span>
                <strong class="text-white text-xs sm:text-sm">
                    {{ $stats['partnership']['runs'] }} r ({{ $stats['partnership']['balls'] }} b)
                </strong>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
        <button type="button" class="series-tab active" onclick="switchMatchTab('scorecard', this)">Scorecard</button>
        <button type="button" class="series-tab" onclick="switchMatchTab('commentary', this)">Commentary</button>
        <button type="button" class="series-tab" onclick="switchMatchTab('squads', this)">Squads</button>
        <button type="button" class="series-tab" onclick="switchMatchTab('overs', this)">Overs</button>
        <button type="button" class="series-tab" onclick="switchMatchTab('info', this)">Info</button>
    </div>

    <!-- TAB 1: SCORECARD -->
    <div id="tab-scorecard" class="match-tab-content">
        <!-- BATTER TABLE -->
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5 mb-5">
            <h4 class="text-xs font-extrabold text-gray-400 uppercase mb-3.5 tracking-wider">BATTERS</h4>
            <div class="table-responsive-wrapper">
                <table class="cricket-table min-w-[500px]">
                    <thead>
                        <tr>
                            <th class="text-left">BATTER</th>
                            <th>R</th>
                            <th>B</th>
                            <th>4S</th>
                            <th>6S</th>
                            <th>SR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($match->battingStats as $b)
                            <tr>
                                <td class="text-left font-semibold text-white">
                                    {{ $b->player_name }}
                                </td>
                                <td><strong>{{ $b->runs }}</strong></td>
                                <td>{{ $b->balls }}</td>
                                <td>{{ $b->fours }}</td>
                                <td>{{ $b->sixes }}</td>
                                <td>{{ $b->strike_rate }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-6">No batting stats recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BOWLER TABLE -->
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5">
            <h4 class="text-xs font-extrabold text-gray-400 uppercase mb-3.5 tracking-wider">BOWLERS</h4>
            <div class="table-responsive-wrapper">
                <table class="cricket-table min-w-[500px]">
                    <thead>
                        <tr>
                            <th class="text-left">BOWLER</th>
                            <th>O</th>
                            <th>R</th>
                            <th>W</th>
                            <th>ECON</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($match->bowlingStats as $bw)
                            <tr>
                                <td class="text-left"><strong>{{ $bw->player_name }}</strong></td>
                                <td>{{ $bw->overs }}</td>
                                <td>{{ $bw->runs }}</td>
                                <td><strong>{{ $bw->wickets }}</strong></td>
                                <td>{{ $bw->economy }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-6">No bowling stats recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: COMMENTARY -->
    <div id="tab-commentary" class="match-tab-content" style="display:none;">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
            <div class="flex flex-col gap-1">
                @forelse($balls as $ball)
                    <div class="flex gap-3 sm:gap-4 py-3.5 border-b border-white/5 items-start">
                        <!-- Delivery Over Badge -->
                        <div class="flex-shrink-0 pt-0.5">
                            <span class="inline-flex items-center justify-center font-heading font-black text-xs sm:text-sm px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-sky-400 min-w-[42px] tracking-tight">
                                {{ $ball->over_num }}
                            </span>
                        </div>

                        <!-- Commentary Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between flex-wrap gap-2 mb-1.5">
                                <div class="text-xs sm:text-sm font-bold text-white">
                                    <span class="text-gray-400 font-semibold">{{ $ball->bowler_name ?? 'Bowler' }}</span> 
                                    <span class="text-gray-500 font-medium mx-1">to</span> 
                                    <span class="text-white font-bold">{{ $ball->batsman_name ?? 'Batsman' }}</span>
                                </div>

                                <!-- Outcome / Run / Wicket Badge -->
                                <div>
                                    @if(strtoupper($ball->outcome) === 'W')
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black px-2.5 py-0.5 rounded-md bg-red-500/20 text-red-400 border border-red-500/40 uppercase tracking-wide">
                                            🔴 WICKET
                                        </span>
                                    @elseif($ball->outcome === '4')
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black px-2.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 uppercase tracking-wide">
                                            FOUR (4 Runs)
                                        </span>
                                    @elseif($ball->outcome === '6')
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black px-2.5 py-0.5 rounded-md bg-purple-500/20 text-purple-400 border border-purple-500/40 uppercase tracking-wide">
                                            SIX (6 Runs)
                                        </span>
                                    @elseif(str_contains(strtolower($ball->outcome), 'wide') || str_contains(strtolower($ball->outcome), 'no ball'))
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black px-2.5 py-0.5 rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/40 uppercase tracking-wide">
                                            {{ $ball->outcome }}
                                        </span>
                                    @elseif($ball->outcome === '0' || strtolower($ball->outcome) === 'dot' || strtolower($ball->outcome) === 'dot ball')
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold px-2.5 py-0.5 rounded-md bg-white/5 text-gray-400 border border-white/10">
                                            • Dot Ball (0 Run)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold px-2.5 py-0.5 rounded-md bg-sky-500/15 text-sky-400 border border-sky-500/30">
                                            {{ $ball->outcome }} {{ is_numeric($ball->outcome) ? ((int)$ball->outcome === 1 ? 'Run' : 'Runs') : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Descriptive Commentary Line in Easy English -->
                            <p class="text-xs sm:text-sm text-gray-300/90 leading-relaxed font-normal m-0">
                                @if(strtoupper($ball->outcome) === 'W')
                                    <strong class="text-red-400 font-bold">OUT!</strong> {{ $ball->batsman_name }} is out! {{ $ball->bowler_name }} takes the wicket.
                                @elseif($ball->outcome === '4')
                                    <strong class="text-emerald-400 font-bold">FOUR!</strong> {{ $ball->batsman_name }} hits a four.
                                @elseif($ball->outcome === '6')
                                    <strong class="text-purple-400 font-bold">SIX!</strong> Big six by {{ $ball->batsman_name }}!
                                @elseif(str_contains(strtolower($ball->outcome), 'wide'))
                                    <strong class="text-amber-400 font-bold">WIDE!</strong> Wide ball bowled by {{ $ball->bowler_name }}.
                                @elseif(str_contains(strtolower($ball->outcome), 'no ball'))
                                    <strong class="text-amber-400 font-bold">NO BALL!</strong> No ball by {{ $ball->bowler_name }}. Free hit next!
                                @elseif($ball->outcome === '0' || strtolower($ball->outcome) === 'dot' || strtolower($ball->outcome) === 'dot ball')
                                    No run. {{ $ball->batsman_name }} defends safely.
                                @elseif($ball->outcome === '1')
                                    1 run. {{ $ball->batsman_name }} takes a single.
                                @elseif($ball->outcome === '2')
                                    2 runs. {{ $ball->batsman_name }} runs two.
                                @elseif($ball->outcome === '3')
                                    3 runs. Good running between the wickets.
                                @elseif(is_numeric($ball->outcome))
                                    {{ $ball->outcome }} runs scored.
                                @else
                                    {{ $ball->bowler_name }} to {{ $ball->batsman_name }}, {{ $ball->outcome }}.
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">No commentary entries added yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: SQUADS -->
    <div id="tab-squads" class="match-tab-content" style="display:none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Team 1 Squad -->
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
                <h3 class="text-base font-bold text-white mb-4 border-b border-[#30363d] pb-2.5">
                    {{ $match->team1->name }} playing XI
                </h3>
                <div class="flex flex-col gap-2.5">
                    @forelse($match->team1->players as $p)
                        <div class="flex justify-between items-center text-sm py-1.5 border-b border-white/5">
                            <span class="font-semibold text-white">{{ $p->name }}</span>
                            <span class="text-xs text-gray-400 bg-white/5 px-2 py-0.5 rounded">{{ $p->role }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-xs">No players registered in team roster.</p>
                    @endforelse
                </div>
            </div>
            <!-- Team 2 Squad -->
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
                <h3 class="text-base font-bold text-white mb-4 border-b border-[#30363d] pb-2.5">
                    {{ $match->team2->name }} playing XI
                </h3>
                <div class="flex flex-col gap-2.5">
                    @forelse($match->team2->players as $p)
                        <div class="flex justify-between items-center text-sm py-1.5 border-b border-white/5">
                            <span class="font-semibold text-white">{{ $p->name }}</span>
                            <span class="text-xs text-gray-400 bg-white/5 px-2 py-0.5 rounded">{{ $p->role }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-xs">No players registered in team roster.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: OVERS -->
    <div id="tab-overs" class="match-tab-content" style="display:none;">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
            <h3 class="text-base font-bold text-white mb-5">Over-by-Over Summary</h3>
            <div class="flex flex-col gap-3.5">
                @forelse($overSummaries as $os)
                    <div class="flex gap-3.5 p-3 sm:p-4 bg-white/[0.02] border border-[#30363d] rounded-xl items-center">
                        <!-- Over Badge -->
                        <div class="w-10 h-10 rounded-full bg-emerald-500/15 text-emerald-400 flex items-center justify-center font-black font-heading text-xs sm:text-sm flex-shrink-0">
                            Ov {{ $os->over_num }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-1.5 flex-wrap gap-1">
                                <strong class="text-white text-xs sm:text-sm truncate">Bowler: {{ $os->bowler }}</strong>
                                <span class="text-xs bg-white/5 px-2 py-0.5 rounded text-gray-300 font-bold">
                                    Runs: {{ $os->runs }}
                                </span>
                            </div>
                            <div class="flex gap-1.5 items-center flex-wrap">
                                <span class="text-[11px] text-gray-400 uppercase tracking-wider font-bold">Deliveries:</span>
                                @foreach(explode(', ', $os->details) as $ballVal)
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px] font-bold 
                                        @if(strtolower($ballVal) === 'w') bg-red-500 text-white
                                        @elseif($ballVal === '4' || $ballVal === '6') bg-emerald-500 text-white
                                        @else bg-white/10 text-white
                                        @endif">
                                        {{ $ballVal }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">No completed overs recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 5: INFO -->
    <div id="tab-info" class="match-tab-content" style="display:none;">
        @php
            $matchAnalytics = $matchAnalytics ?? (new \App\Services\MatchService())->getMatchInformationAnalytics($match);
            $t1A = $matchAnalytics['team_form']['team1'];
            $t2A = $matchAnalytics['team_form']['team2'];
            $h2h = $matchAnalytics['head_to_head'];
            $comp = $matchAnalytics['team_comparison'];
            $wp = $matchAnalytics['weather_pitch'];
            $vs = $matchAnalytics['venue_stats'];
        @endphp

        @php
            $matchDateObj = !empty($match->match_date) ? \Carbon\Carbon::parse($match->match_date) : now();
            $formattedDate = $matchDateObj->format('l, j F Y');
            $formattedTime = $matchDateObj->copy()->setTimezone('Asia/Kolkata')->format('h:i A') . ' IST';
            $tossTime = $matchDateObj->copy()->subMinutes(30)->setTimezone('Asia/Kolkata')->format('h:i A') . ' IST';

            $seriesName = $match->tournament?->name ?? ($match->level_type ? ($match->level_type . ' ' . ($match->match_type ?? 'Cricket')) : 'International Cricket Series');
            $seriesUrl = $match->tournament?->url;

            $venueObj = $match->venue;
            $venueName = $venueObj?->name ?? ($match->venue_name ?? 'Wankhede Stadium, Mumbai');
            $venueUrl = $venueObj ? route('venue.show.slug', ['slug' => $venueObj->slug, 'id' => $venueObj->id]) : null;

            $city = $venueObj?->city ?: ($venueObj?->country ?: null);
            if (!$city && str_contains($venueName, ',')) {
                $parts = explode(',', $venueName);
                $city = trim(end($parts));
            }
            $city = $city ?: 'Mumbai';

            $streamingChannels = $match->where_to_watch ?: 'FanCode, Star Sports Network';
            $shortTitle = ($match->team1?->short_name ?: 'T1') . ' vs ' . ($match->team2?->short_name ?: 'T2');
        @endphp

        <!-- Match Information Table (Date, Time, Venue, Live Streaming & Live Score) -->
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl overflow-hidden mb-6 shadow-sm">
            <!-- Header bar -->
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-[#30363d] bg-[#161b22]/70 flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2 m-0">
                    <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $shortTitle }} Match Details: Date, Time, Venue &amp; Live Streaming</span>
                </h3>
            </div>

            <!-- 2-Column Responsive Details Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm border-collapse match-info-table">
                    <thead>
                        <tr class="border-b border-[#30363d] bg-white/[0.02] text-gray-400 text-[11px] sm:text-xs uppercase tracking-wider font-extrabold">
                            <th class="py-3 px-4 sm:px-6 w-1/3 sm:w-1/4">Details</th>
                            <th class="py-3 px-4 sm:px-6 w-2/3 sm:w-3/4">Information</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#30363d]/50">
                        <!-- Series -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Series</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                @if($seriesUrl)
                                    <a href="{{ $seriesUrl }}" class="text-blue-400 hover:text-blue-300 transition-colors inline-flex items-center gap-1.5 font-bold">
                                        <span>{{ $seriesName }}</span>
                                        <svg class="w-3.5 h-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    {{ $seriesName }}
                                @endif
                            </td>
                        </tr>

                        <!-- Match -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Match</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                {{ $match->team1?->name ?? 'Team 1' }} vs {{ $match->team2?->name ?? 'Team 2' }}
                                @if(!empty($match->match_type))
                                    <span class="text-gray-400 font-normal ml-1">({{ strtoupper($match->match_type) }})</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Date -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Date</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                {{ $formattedDate }}
                            </td>
                        </tr>

                        <!-- Time -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Time</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                {{ $formattedTime }}
                            </td>
                        </tr>

                        <!-- Toss Time -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Toss Time</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                {{ $tossTime }}
                            </td>
                        </tr>

                        <!-- Venue -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Venue</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                @if($venueUrl)
                                    <a href="{{ $venueUrl }}" class="text-blue-400 hover:text-blue-300 transition-colors inline-flex items-center gap-1.5 font-bold">
                                        <span>{{ $venueName }}</span>
                                        <svg class="w-3.5 h-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                @else
                                    {{ $venueName }}
                                @endif
                            </td>
                        </tr>

                        <!-- City -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">City</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                {{ $city }}
                            </td>
                        </tr>

                        <!-- Match Type / Format -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Match Type</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                <span class="tag-badge text-xs">{{ strtoupper($match->match_type ?? 'T20') }}</span>
                                @if(!empty($match->level_type))
                                    <span class="text-xs text-gray-400 ml-1.5 font-normal">&bull; {{ strtoupper($match->level_type) }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Status -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Status</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                @if($match->effective_status === 'live')
                                    <span class="badge-live text-xs">🔴 LIVE</span>
                                @elseif($match->effective_status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded text-xs font-extrabold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">COMPLETED</span>
                                @else
                                    <span class="tag-badge text-xs">{{ strtoupper($match->effective_status) }}</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Live Streaming (Broadcaster channels) -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Live Streaming</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @foreach(explode(',', $streamingChannels) as $channel)
                                        @php $trimmedCh = trim($channel); @endphp
                                        @if(!empty($trimmedCh))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                {{ $trimmedCh }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>

                        <!-- Live Score (CricketKaScore, Possible11) -->
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 sm:px-6 font-bold text-gray-400 match-info-label">Live Score</td>
                            <td class="py-3 px-4 sm:px-6 font-bold text-white match-info-value">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        CricketKaScore
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-blue-500/15 text-blue-400 border border-blue-500/30">
                                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                        Possible11
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 1: Team Form (Last 5 matches) -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-1.5">
                    <span>Team Form: {{ $matchAnalytics['team1_short'] }} &amp; {{ $matchAnalytics['team2_short'] }}</span>
                </h3>
                <span class="text-[11px] sm:text-xs text-gray-400 font-normal">(Last 5 matches)</span>
            </div>

            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5 space-y-4">
                <!-- Team 1 Form Row -->
                <div class="border-b border-[#30363d]/60 pb-4 last:border-b-0 last:pb-0">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            @if(!empty($t1A['team_logo']))
                                <img src="{{ $t1A['team_logo'] }}" alt="{{ $t1A['team_name'] }}" class="w-8 h-8 rounded-full object-cover border border-[#30363d]">
                            @else
                                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-xs text-white">
                                    {{ substr($t1A['team_short'], 0, 3) }}
                                </div>
                            @endif
                            <span class="font-bold text-sm sm:text-base text-white">{{ $t1A['team_name'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2 ml-auto">
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-[#161b22] border border-[#30363d] text-gray-400 font-bold text-xs flex items-center justify-center">&bull;</span>
                            @foreach($t1A['recent_badges'] as $badge)
                                @if($badge === 'W')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-sm">W</span>
                                @elseif($badge === 'L')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-rose-500 text-white font-black text-xs flex items-center justify-center shadow-sm">L</span>
                                @elseif($badge === 'T')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-amber-500 text-white font-black text-xs flex items-center justify-center shadow-sm">T</span>
                                @else
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-slate-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">NR</span>
                                @endif
                            @endforeach
                            <button type="button" onclick="toggleFormDetails('t1-form-details', this)" class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] text-gray-400 hover:text-white flex items-center justify-center transition-all ml-1" title="Toggle match details">
                                <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Team 1 Form Accordion List -->
                    <div id="t1-form-details" class="hidden mt-3 pt-3 border-t border-[#30363d]/50 space-y-2">
                        @foreach($t1A['matches'] as $fm)
                            <div class="bg-[#161b22] border border-[#30363d]/60 p-2.5 sm:p-3 rounded-xl flex items-center justify-between text-xs gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-5 h-5 rounded {{ $fm['outcome'] === 'W' ? 'bg-emerald-600' : ($fm['outcome'] === 'L' ? 'bg-rose-500' : 'bg-slate-600') }} text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">{{ $fm['outcome'] }}</span>
                                    <div class="truncate">
                                        <span class="text-white font-bold block truncate">vs {{ $fm['opponent_name'] }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $fm['series'] }} &bull; {{ $fm['date'] }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-white font-bold block text-xs">{{ $fm['my_score'] }} vs {{ $fm['opp_score'] }}</span>
                                    <span class="text-[10px] {{ $fm['outcome'] === 'W' ? 'text-emerald-400' : 'text-rose-400' }} font-medium">{{ $fm['result_text'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Team 2 Form Row -->
                <div>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            @if(!empty($t2A['team_logo']))
                                <img src="{{ $t2A['team_logo'] }}" alt="{{ $t2A['team_name'] }}" class="w-8 h-8 rounded-full object-cover border border-[#30363d]">
                            @else
                                <div class="w-8 h-8 rounded-full bg-sky-600 flex items-center justify-center font-bold text-xs text-white">
                                    {{ substr($t2A['team_short'], 0, 3) }}
                                </div>
                            @endif
                            <span class="font-bold text-sm sm:text-base text-white">{{ $t2A['team_name'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2 ml-auto">
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-[#161b22] border border-[#30363d] text-gray-400 font-bold text-xs flex items-center justify-center">&bull;</span>
                            @foreach($t2A['recent_badges'] as $badge)
                                @if($badge === 'W')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-sm">W</span>
                                @elseif($badge === 'L')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-rose-500 text-white font-black text-xs flex items-center justify-center shadow-sm">L</span>
                                @elseif($badge === 'T')
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-amber-500 text-white font-black text-xs flex items-center justify-center shadow-sm">T</span>
                                @else
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-slate-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">NR</span>
                                @endif
                            @endforeach
                            <button type="button" onclick="toggleFormDetails('t2-form-details', this)" class="w-6 h-6 sm:w-7 sm:h-7 rounded bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] text-gray-400 hover:text-white flex items-center justify-center transition-all ml-1" title="Toggle match details">
                                <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    </div>
                    <!-- Team 2 Form Accordion List -->
                    <div id="t2-form-details" class="hidden mt-3 pt-3 border-t border-[#30363d]/50 space-y-2">
                        @foreach($t2A['matches'] as $fm)
                            <div class="bg-[#161b22] border border-[#30363d]/60 p-2.5 sm:p-3 rounded-xl flex items-center justify-between text-xs gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-5 h-5 rounded {{ $fm['outcome'] === 'W' ? 'bg-emerald-600' : ($fm['outcome'] === 'L' ? 'bg-rose-500' : 'bg-slate-600') }} text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">{{ $fm['outcome'] }}</span>
                                    <div class="truncate">
                                        <span class="text-white font-bold block truncate">vs {{ $fm['opponent_name'] }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $fm['series'] }} &bull; {{ $fm['date'] }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-white font-bold block text-xs">{{ $fm['my_score'] }} vs {{ $fm['opp_score'] }}</span>
                                    <span class="text-[10px] {{ $fm['outcome'] === 'W' ? 'text-emerald-400' : 'text-rose-400' }} font-medium">{{ $fm['result_text'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Head to Head (Last 10 matches) -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-1.5">
                    <span>Head to Head: {{ $matchAnalytics['team1_short'] }} vs {{ $matchAnalytics['team2_short'] }}</span>
                </h3>
                <span class="text-[11px] sm:text-xs text-gray-400 font-normal">(Last 10 matches)</span>
            </div>

            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
                <!-- Big Tally Header Box -->
                <div class="bg-[#161b22] border border-[#30363d]/80 rounded-2xl p-4 sm:p-5 flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        @if(!empty($t1A['team_logo']))
                            <img src="{{ $t1A['team_logo'] }}" alt="{{ $matchAnalytics['team1_short'] }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-contain p-0.5 bg-white border border-gray-200/20 flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-600 flex items-center justify-center font-black text-xs text-white flex-shrink-0" style="display:none;">
                                {{ $matchAnalytics['team1_short'] }}
                            </div>
                        @else
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-600 flex items-center justify-center font-black text-xs text-white flex-shrink-0">
                                {{ $matchAnalytics['team1_short'] }}
                            </div>
                        @endif
                        <span class="font-extrabold text-sm sm:text-base text-white">{{ $matchAnalytics['team1_short'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <span class="text-2xl sm:text-4xl font-black text-indigo-400">{{ $h2h['t1_wins'] }}</span>
                        <span class="text-gray-500 font-bold text-lg sm:text-2xl">-</span>
                        <span class="text-2xl sm:text-4xl font-black text-indigo-400">{{ $h2h['t2_wins'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="font-extrabold text-sm sm:text-base text-white">{{ $matchAnalytics['team2_short'] }}</span>
                        @if(!empty($t2A['team_logo']))
                            <img src="{{ $t2A['team_logo'] }}" alt="{{ $matchAnalytics['team2_short'] }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-contain p-0.5 bg-white border border-gray-200/20 flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-sky-600 flex items-center justify-center font-black text-xs text-white flex-shrink-0" style="display:none;">
                                {{ $matchAnalytics['team2_short'] }}
                            </div>
                        @else
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-sky-600 flex items-center justify-center font-black text-xs text-white flex-shrink-0">
                                {{ $matchAnalytics['team2_short'] }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Direct Match Summaries -->
                @if(!empty($h2h['recent_encounters']))
                    <div class="space-y-3">
                        @foreach($h2h['recent_encounters'] as $hMatch)
                            <div class="grid grid-cols-3 items-center text-center text-xs sm:text-sm py-3 border-t border-[#30363d]/60 first:border-t-0">
                                <div class="text-left">
                                    <span class="font-bold text-white block">{{ $hMatch['team1_short'] }}</span>
                                    <span class="text-gray-400 text-[11px] sm:text-xs font-semibold">{{ $hMatch['team1_score'] }} <span class="text-[10px] font-normal text-gray-500">{{ $hMatch['team1_overs'] }}</span></span>
                                </div>
                                <div class="px-1">
                                    <strong class="text-indigo-400 block text-xs sm:text-sm font-black">{{ $hMatch['winner_text'] }}</strong>
                                    <span class="text-[10px] sm:text-[11px] text-gray-400 block leading-tight">{{ $hMatch['title'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-white block">{{ $hMatch['team2_short'] }}</span>
                                    <span class="text-gray-400 text-[11px] sm:text-xs font-semibold"><span class="text-[10px] font-normal text-gray-500">{{ $hMatch['team2_overs'] }}</span> {{ $hMatch['team2_score'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- SECTION 3: Team Comparison (Overall vs On Venue) -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-1.5">
                    <span>Team Comparison: {{ $matchAnalytics['team1_short'] }} vs {{ $matchAnalytics['team2_short'] }}</span>
                </h3>
                <div class="flex items-center gap-1 bg-[#161b22] border border-[#30363d] p-0.5 rounded-lg text-xs">
                    <button type="button" id="btn-comp-overall" onclick="switchComparisonTab('overall')" class="px-2.5 py-1 rounded font-bold text-xs transition-colors bg-blue-600 text-white">Overall</button>
                    <button type="button" id="btn-comp-venue" onclick="switchComparisonTab('on_venue')" class="px-2.5 py-1 rounded font-bold text-xs transition-colors text-gray-400 hover:text-white">On Venue</button>
                </div>
            </div>

            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
                <!-- Teams Bar inside Card -->
                <div class="bg-[#161b22] border border-[#30363d]/80 rounded-xl p-3 sm:p-4 flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        @if(!empty($t1A['team_logo']))
                            <img src="{{ $t1A['team_logo'] }}" alt="{{ $matchAnalytics['team1_short'] }}" class="w-8 h-8 rounded-full object-contain p-0.5 bg-white border border-gray-200/20 flex-shrink-0" onerror="this.style.display='none';">
                        @endif
                        <div>
                            <strong class="text-white font-extrabold text-xs sm:text-sm block">{{ $matchAnalytics['team1_short'] }}</strong>
                            <span id="comp-subtext-t1" class="text-[10px] sm:text-[11px] text-gray-400">vs all teams</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 text-right">
                        <div>
                            <strong class="text-white font-extrabold text-xs sm:text-sm block">{{ $matchAnalytics['team2_short'] }}</strong>
                            <span id="comp-subtext-t2" class="text-[10px] sm:text-[11px] text-gray-400">vs all teams</span>
                        </div>
                        @if(!empty($t2A['team_logo']))
                            <img src="{{ $t2A['team_logo'] }}" alt="{{ $matchAnalytics['team2_short'] }}" class="w-8 h-8 rounded-full object-contain p-0.5 bg-white border border-gray-200/20 flex-shrink-0" onerror="this.style.display='none';">
                        @endif
                    </div>
                </div>

                <!-- Comparison Rows -->
                @php
                    $ov = $comp['overall'];
                @endphp
                <div class="space-y-3 text-xs sm:text-sm">
                    <!-- Matches Played -->
                    <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                        <span id="comp-t1-matches" class="font-bold text-white w-16 text-left">{{ $ov['t1_matches'] }}</span>
                        <span class="text-gray-400 font-medium text-center flex-1">Matches Played</span>
                        <span id="comp-t2-matches" class="font-bold text-white w-16 text-right">{{ $ov['t2_matches'] }}</span>
                    </div>

                    <!-- Win % -->
                    <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                        <span id="comp-t1-win" class="font-bold {{ $ov['t1_win_pct'] >= $ov['t2_win_pct'] ? 'text-emerald-400' : 'text-white' }} w-16 text-left">{{ $ov['t1_win_pct'] }}%</span>
                        <span class="text-gray-400 font-medium text-center flex-1">Win</span>
                        <span id="comp-t2-win" class="font-bold {{ $ov['t2_win_pct'] >= $ov['t1_win_pct'] ? 'text-emerald-400' : 'text-white' }} w-16 text-right">{{ $ov['t2_win_pct'] }}%</span>
                    </div>

                    <!-- Avg Score -->
                    <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                        <span id="comp-t1-avg" class="font-bold {{ $ov['t1_avg_score'] >= $ov['t2_avg_score'] ? 'text-emerald-400' : 'text-white' }} w-16 text-left">{{ $ov['t1_avg_score'] }}</span>
                        <span class="text-gray-400 font-medium text-center flex-1">Avg Score</span>
                        <span id="comp-t2-avg" class="font-bold {{ $ov['t2_avg_score'] >= $ov['t1_avg_score'] ? 'text-emerald-400' : 'text-white' }} w-16 text-right">{{ $ov['t2_avg_score'] }}</span>
                    </div>

                    <!-- Highest Score -->
                    <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                        <span id="comp-t1-high" class="font-bold {{ $ov['t1_high_score'] >= $ov['t2_high_score'] ? 'text-emerald-400' : 'text-white' }} w-16 text-left">{{ $ov['t1_high_score'] }}</span>
                        <span class="text-gray-400 font-medium text-center flex-1">Highest Score</span>
                        <span id="comp-t2-high" class="font-bold {{ $ov['t2_high_score'] >= $ov['t1_high_score'] ? 'text-emerald-400' : 'text-white' }} w-16 text-right">{{ $ov['t2_high_score'] }}</span>
                    </div>

                    <!-- Lowest Score -->
                    <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                        <span id="comp-t1-low" class="font-bold text-white w-16 text-left">{{ $ov['t1_low_score'] }}</span>
                        <span class="text-gray-400 font-medium text-center flex-1">Lowest Score</span>
                        <span id="comp-t2-low" class="font-bold text-white w-16 text-right">{{ $ov['t2_low_score'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Weather & Pitch Report -->
        <div class="mb-6">
            <div class="mb-3">
                <h3 class="text-sm sm:text-base font-bold text-white mb-2">
                    Weather &amp; Pitch Report: {{ $matchAnalytics['team1_short'] }} vs {{ $matchAnalytics['team2_short'] }}
                </h3>
                <p class="text-xs sm:text-sm text-gray-400 leading-relaxed">
                    {{ $wp['summary_text'] }}
                </p>
            </div>

            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5">
                <div class="bg-[#161b22] border border-[#30363d]/80 rounded-xl p-4 flex items-center justify-between flex-wrap gap-4">
                    <!-- Left: Weather SVG Icon + Venue + Big Temp -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400 flex-shrink-0">
                            <!-- Weather Sun & Cloud with Lightning SVG -->
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-medium">{{ $wp['venue_display'] }}</span>
                            <div class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                {{ $wp['temperature'] }}°C
                            </div>
                        </div>
                    </div>

                    <!-- Right: Weather condition text, humidity, rain chance -->
                    <div class="flex flex-col sm:items-end gap-1.5 text-xs sm:text-sm">
                        <strong class="text-white font-bold text-sm">{{ $wp['condition'] }}</strong>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span class="flex items-center gap-1"><span class="text-sky-400">💧</span> {{ $wp['humidity'] }}% (Humidity)</span>
                            <span class="flex items-center gap-1"><span class="text-blue-400">🌧️</span> {{ $wp['rain_chance'] }} % Chance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Venue Stats -->
        <div class="mb-6">
            <div class="mb-3">
                <h3 class="text-sm sm:text-base font-bold text-white">
                    Venue Stats
                </h3>
            </div>

            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    
                    <!-- Left: Circular Donut Chart Card (md:col-span-5) -->
                    <div class="md:col-span-5 bg-[#161b22] border border-[#30363d]/80 rounded-2xl p-5 flex flex-col items-center justify-center shadow-inner">
                        <!-- SVG Donut Chart -->
                        <div class="relative w-36 h-36 flex items-center justify-center mb-4">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <!-- Background Circle -->
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#21262d" stroke-width="4.5" />
                                <!-- Red Arc (Win Bowl First) -->
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#ef4444" stroke-width="4.5"
                                    stroke-dasharray="100 100" stroke-linecap="round" />
                                <!-- Green Arc (Win Bat First) -->
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#22c55e" stroke-width="4.5"
                                    stroke-dasharray="{{ $vs['win_bat_first_pct'] }} 100" stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-2xl sm:text-3xl font-black text-white leading-none">{{ $vs['total_matches'] }}</span>
                                <span class="text-[11px] text-gray-400 font-semibold mt-1">Matches</span>
                            </div>
                        </div>

                        <!-- Donut Legend below -->
                        <div class="w-full space-y-2 text-xs sm:text-sm pt-2 border-t border-[#30363d]/50">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Win Bat first</span>
                                <strong class="text-emerald-400 font-black text-sm">{{ $vs['win_bat_first_pct'] }}%</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Win Bowl first</span>
                                <strong class="text-rose-400 font-black text-sm">{{ $vs['win_bowl_first_pct'] }}%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Innings Averages & Records (md:col-span-7) -->
                    <div class="md:col-span-7 space-y-4 text-xs sm:text-sm">
                        <!-- Avg 1st & 2nd Innings Score -->
                        <div class="grid grid-cols-2 gap-3 bg-[#161b22] border border-[#30363d]/80 rounded-xl p-3.5">
                            <div>
                                <span class="text-gray-400 text-[11px] block uppercase font-bold">Avg 1st Inns</span>
                                <strong class="text-white text-lg sm:text-xl font-black">{{ $vs['avg_1st_inns'] }}</strong>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[11px] block uppercase font-bold">Avg 2nd Inns</span>
                                <strong class="text-white text-lg sm:text-xl font-black">{{ $vs['avg_2nd_inns'] }}</strong>
                            </div>
                        </div>

                        <!-- Highest Total -->
                        <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                            <div>
                                <span class="font-bold text-white block">Highest Total</span>
                                <span class="text-[11px] text-gray-400">by {{ $vs['highest_total_by'] }}</span>
                            </div>
                            <span class="text-white font-black text-sm sm:text-base">{{ $vs['highest_total'] }}</span>
                        </div>

                        <!-- Lowest Total -->
                        <div class="flex items-center justify-between py-2 border-b border-[#30363d]/50">
                            <div>
                                <span class="font-bold text-white block">Lowest Total</span>
                                <span class="text-[11px] text-gray-400">by {{ $vs['lowest_total_by'] }}</span>
                            </div>
                            <span class="text-white font-black text-sm sm:text-base">{{ $vs['lowest_total'] }}</span>
                        </div>

                        <!-- Highest Chased -->
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <span class="font-bold text-white block">Highest Chased</span>
                                <span class="text-[11px] text-gray-400">by {{ $vs['highest_chased_by'] }}</span>
                            </div>
                            <span class="text-white font-black text-sm sm:text-base">{{ $vs['highest_chased'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pace vs Spin on Venue -->
                <div class="border-t border-[#30363d]/60 pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs sm:text-sm font-bold text-white">
                            Pace vs Spin on <span class="text-sky-400">{{ $vName }}</span>
                        </h4>
                        <div class="text-[11px] text-gray-400 flex items-center gap-3">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span> Pace: {{ $vs['pace_pct'] }}%</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Spin: {{ $vs['spin_pct'] }}%</span>
                        </div>
                    </div>
                    <!-- Segmented Progress Bar -->
                    <div class="flex h-3 rounded-full overflow-hidden bg-white/5 mb-2">
                        <div style="width: {{ $vs['pace_pct'] }}%;" class="bg-gradient-to-r from-sky-600 to-sky-400"></div>
                        <div style="width: {{ $vs['spin_pct'] }}%;" class="bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-400">
                        <span>Pacers: {{ $vs['pace_wkts'] }} wickets</span>
                        <span>Spinners: {{ $vs['spin_wkts'] }} wickets</span>
                    </div>
                </div>

                <!-- Match Playing Squads (Both Teams Under Pace vs Spin) -->
                <div class="border-t border-[#30363d]/60 pt-6 mt-6">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                        <div>
                            <span class="text-[11px] font-extrabold tracking-wider text-sky-400 uppercase block mb-0.5">
                                👥 MATCH PLAYING SQUADS
                            </span>
                            <h4 class="text-sm sm:text-base font-black text-white m-0 flex items-center gap-2">
                                <span>🏏</span> Squad Lineups &amp; Player Rosters
                            </h4>
                        </div>

                        <!-- Squad Filter Buttons & Search -->
                        <div class="flex items-center flex-wrap gap-2">
                            <input type="text" id="match-squad-search" oninput="filterMatchSquadPlayers()" placeholder="Search player..." class="text-xs bg-[#161b22] border border-[#30363d] text-white rounded-lg px-2.5 py-1.5 outline-none focus:border-sky-400 transition-all placeholder-gray-500 w-32 sm:w-40">
                            <button type="button" onclick="switchMatchSquadView('all')" id="squad-btn-all" class="match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-sky-500 text-white border border-sky-400 shadow-sm transition-all">
                                All Squads
                            </button>
                            @if($match->team1)
                            <button type="button" onclick="switchMatchSquadView('team1')" id="squad-btn-team1" class="match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-[#161b22] text-gray-300 border border-[#30363d] hover:border-sky-400 transition-all">
                                {{ $match->team1->short_name ?: $match->team1->name }} ({{ count($team1Squad ?? []) }})
                            </button>
                            @endif
                            @if($match->team2)
                            <button type="button" onclick="switchMatchSquadView('team2')" id="squad-btn-team2" class="match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-[#161b22] text-gray-300 border border-[#30363d] hover:border-sky-400 transition-all">
                                {{ $match->team2->short_name ?: $match->team2->name }} ({{ count($team2Squad ?? []) }})
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" id="match-squads-container">
                        
                        <!-- TEAM 1 SQUAD COLUMN -->
                        <div class="match-squad-col" id="squad-col-team1">
                            <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-4 mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if(!empty($match->team1?->logo))
                                        <img src="{{ $match->team1->logo }}" alt="{{ $match->team1->name }}" class="w-9 h-9 rounded-lg object-contain bg-[#0d1117] p-1 border border-[#30363d] flex-shrink-0" onerror="this.style.display='none';">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-sky-500/10 border border-sky-500/30 flex items-center justify-center font-black text-xs text-sky-400 flex-shrink-0">
                                            {{ strtoupper(substr($match->team1?->short_name ?: ($match->team1?->name ?? 'T1'), 0, 3)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h5 class="text-xs sm:text-sm font-bold text-white truncate m-0">
                                            {{ $match->team1?->name ?? 'Team 1' }}
                                        </h5>
                                        <span class="text-[11px] text-gray-400 font-medium">Squad Roster</span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-extrabold bg-sky-500/15 text-sky-400 border border-sky-500/30 px-2.5 py-1 rounded-full whitespace-nowrap">
                                    {{ count($team1Squad ?? []) }} Players
                                </span>
                            </div>

                            <!-- Team 1 Players List -->
                            <div class="space-y-2 max-h-[440px] overflow-y-auto pr-1" style="scrollbar-width: thin; scrollbar-color: #30363d transparent;">
                                @forelse($team1Squad ?? [] as $p)
                                    @php
                                        $pAvatar = $p->default_avatar;
                                        $pImg = !empty($p->profile_image) ? $p->profile_image : $pAvatar;
                                        $pBat = $match->battingStats->firstWhere('player_name', $p->name);
                                        $pBowl = $match->bowlingStats->firstWhere('player_name', $p->name);
                                    @endphp
                                    <div class="match-player-card flex items-center justify-between gap-3 bg-[#161b22]/70 hover:bg-[#161b22] border border-[#30363d]/70 hover:border-sky-500/40 rounded-xl p-2.5 transition-all" data-name="{{ strtolower($p->name . ' ' . ($p->role ?? '')) }}">
                                        <a href="{{ $p->url ?? route('player.profile.slug', ['slug' => \Illuminate\Support\Str::slug($p->name), 'id' => $p->id]) }}" class="flex items-center gap-3 min-w-0 text-decoration-none group">
                                            <img src="{{ $pImg }}" alt="{{ $p->name }}" class="w-8 h-8 rounded-full object-cover border border-[#30363d] group-hover:border-sky-400 bg-[#0d1117] flex-shrink-0 transition-colors" onerror="this.onerror=null; this.src='{{ $pAvatar }}';">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-white group-hover:text-sky-400 truncate transition-colors">
                                                    {{ $p->name }}
                                                </div>
                                                <div class="text-[10px] text-gray-400 truncate">
                                                    {{ $p->role ?: 'Player' }}
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Match Performance Badge (if played) -->
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            @if($pBat)
                                                <span class="text-[10px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 px-2 py-0.5 rounded">
                                                    🏏 {{ $pBat->runs }} ({{ $pBat->balls }})
                                                </span>
                                            @endif
                                            @if($pBowl && ($pBowl->overs > 0 || $pBowl->wickets > 0))
                                                <span class="text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded">
                                                    🎯 {{ $pBowl->wickets }}/{{ $pBowl->runs }}
                                                </span>
                                            @endif
                                            @if(!$pBat && (!$pBowl || ($pBowl->overs == 0 && $pBowl->wickets == 0)))
                                                <span class="text-[10px] text-gray-500 font-semibold px-1.5">
                                                    Squad
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-400 italic text-center py-6 bg-[#161b22]/40 rounded-xl border border-[#30363d]/40">
                                        No squad players recorded yet for {{ $match->team1?->name ?? 'Team 1' }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- TEAM 2 SQUAD COLUMN -->
                        <div class="match-squad-col" id="squad-col-team2">
                            <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-4 mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if(!empty($match->team2?->logo))
                                        <img src="{{ $match->team2->logo }}" alt="{{ $match->team2->name }}" class="w-9 h-9 rounded-lg object-contain bg-[#0d1117] p-1 border border-[#30363d] flex-shrink-0" onerror="this.style.display='none';">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center font-black text-xs text-emerald-400 flex-shrink-0">
                                            {{ strtoupper(substr($match->team2?->short_name ?: ($match->team2?->name ?? 'T2'), 0, 3)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h5 class="text-xs sm:text-sm font-bold text-white truncate m-0">
                                            {{ $match->team2?->name ?? 'Team 2' }}
                                        </h5>
                                        <span class="text-[11px] text-gray-400 font-medium">Squad Roster</span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-extrabold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded-full whitespace-nowrap">
                                    {{ count($team2Squad ?? []) }} Players
                                </span>
                            </div>

                            <!-- Team 2 Players List -->
                            <div class="space-y-2 max-h-[440px] overflow-y-auto pr-1" style="scrollbar-width: thin; scrollbar-color: #30363d transparent;">
                                @forelse($team2Squad ?? [] as $p)
                                    @php
                                        $pAvatar = $p->default_avatar;
                                        $pImg = !empty($p->profile_image) ? $p->profile_image : $pAvatar;
                                        $pBat = $match->battingStats->firstWhere('player_name', $p->name);
                                        $pBowl = $match->bowlingStats->firstWhere('player_name', $p->name);
                                    @endphp
                                    <div class="match-player-card flex items-center justify-between gap-3 bg-[#161b22]/70 hover:bg-[#161b22] border border-[#30363d]/70 hover:border-emerald-500/40 rounded-xl p-2.5 transition-all" data-name="{{ strtolower($p->name . ' ' . ($p->role ?? '')) }}">
                                        <a href="{{ $p->url ?? route('player.profile.slug', ['slug' => \Illuminate\Support\Str::slug($p->name), 'id' => $p->id]) }}" class="flex items-center gap-3 min-w-0 text-decoration-none group">
                                            <img src="{{ $pImg }}" alt="{{ $p->name }}" class="w-8 h-8 rounded-full object-cover border border-[#30363d] group-hover:border-emerald-400 bg-[#0d1117] flex-shrink-0 transition-colors" onerror="this.onerror=null; this.src='{{ $pAvatar }}';">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-white group-hover:text-emerald-400 truncate transition-colors">
                                                    {{ $p->name }}
                                                </div>
                                                <div class="text-[10px] text-gray-400 truncate">
                                                    {{ $p->role ?: 'Player' }}
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Match Performance Badge (if played) -->
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            @if($pBat)
                                                <span class="text-[10px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 px-2 py-0.5 rounded">
                                                    🏏 {{ $pBat->runs }} ({{ $pBat->balls }})
                                                </span>
                                            @endif
                                            @if($pBowl && ($pBowl->overs > 0 || $pBowl->wickets > 0))
                                                <span class="text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded">
                                                    🎯 {{ $pBowl->wickets }}/{{ $pBowl->runs }}
                                                </span>
                                            @endif
                                            @if(!$pBat && (!$pBowl || ($pBowl->overs == 0 && $pBowl->wickets == 0)))
                                                <span class="text-[10px] text-gray-500 font-semibold px-1.5">
                                                    Squad
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-400 italic text-center py-6 bg-[#161b22]/40 rounded-xl border border-[#30363d]/40">
                                        No squad players recorded yet for {{ $match->team2?->name ?? 'Team 2' }}.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visible On-Page SEO Feature Section -->
    <div class="mt-8 border-t border-[#30363d] pt-8">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-5 sm:p-6 shadow-sm">
            <h2 class="text-base sm:text-lg font-black text-white mb-2">
                {{ $mTitle }} — Live Cricket Match Center &amp; Scorecard Overview
            </h2>
            <p class="text-xs sm:text-sm text-gray-400 leading-relaxed mb-4">
                Stay updated with real-time score updates, detailed batting and bowling scorecards, ball-by-ball commentary, win probability estimates, and match information for <strong>{{ $mTitle }}</strong> played at <strong>{{ $vName }}</strong> on <strong>CricketKaScore</strong>.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs sm:text-sm">
                <div class="bg-[#161b22] border border-[#30363d] p-4 rounded-xl">
                    <h3 class="font-bold text-white mb-1 text-xs uppercase tracking-wide">⚡ Live Ball-by-Ball Scorecard</h3>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        Track current run rates, required run rates, boundaries (4s &amp; 6s), wickets, overs, and individual player performances live in real time.
                    </p>
                </div>
                <div class="bg-[#161b22] border border-[#30363d] p-4 rounded-xl">
                    <h3 class="font-bold text-white mb-1 text-xs uppercase tracking-wide">🎙️ Real-time Commentary</h3>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        Never miss a single delivery with interactive live commentary badges, over highlights, boundary alerts, and key match moments.
                    </p>
                </div>
                <div class="bg-[#161b22] border border-[#30363d] p-4 rounded-xl">
                    <h3 class="font-bold text-white mb-1 text-xs uppercase tracking-wide">📈 Win Probability &amp; Analytics</h3>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        Follow live match win predictions, run rate charts, and squad lineups across all major cricket tournaments on CricketKaScore.
                    </p>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
function switchMatchTab(tabName, btn) {
    document.querySelectorAll('.series-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.match-tab-content').forEach(c => c.style.display = 'none');
    const target = document.getElementById('tab-' + tabName);
    if (target) target.style.display = 'block';
}

function toggleFormDetails(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.classList.toggle('hidden');
    const svg = btn.querySelector('svg');
    if (svg) {
        svg.classList.toggle('rotate-180');
    }
}

const compAnalyticsData = @json($matchAnalytics['team_comparison'] ?? []);

function switchComparisonTab(mode) {
    const btnOverall = document.getElementById('btn-comp-overall');
    const btnVenue = document.getElementById('btn-comp-venue');
    const sub1 = document.getElementById('comp-subtext-t1');
    const sub2 = document.getElementById('comp-subtext-t2');
    
    if (!compAnalyticsData || !compAnalyticsData[mode]) return;
    const data = compAnalyticsData[mode];

    if (mode === 'overall') {
        if (btnOverall) btnOverall.className = 'px-2.5 py-1 rounded font-bold text-xs transition-colors bg-blue-600 text-white';
        if (btnVenue) btnVenue.className = 'px-2.5 py-1 rounded font-bold text-xs transition-colors text-gray-400 hover:text-white';
        if (sub1) sub1.innerText = 'vs all teams';
        if (sub2) sub2.innerText = 'vs all teams';
    } else {
        if (btnVenue) btnVenue.className = 'px-2.5 py-1 rounded font-bold text-xs transition-colors bg-blue-600 text-white';
        if (btnOverall) btnOverall.className = 'px-2.5 py-1 rounded font-bold text-xs transition-colors text-gray-400 hover:text-white';
        if (sub1) sub1.innerText = 'on this venue';
        if (sub2) sub2.innerText = 'on this venue';
    }

    const t1m = document.getElementById('comp-t1-matches');
    const t2m = document.getElementById('comp-t2-matches');
    if (t1m) t1m.innerText = data.t1_matches;
    if (t2m) t2m.innerText = data.t2_matches;

    const t1w = document.getElementById('comp-t1-win');
    const t2w = document.getElementById('comp-t2-win');
    if (t1w) {
        t1w.innerText = data.t1_win_pct + '%';
        t1w.className = 'font-bold w-16 text-left ' + (data.t1_win_pct >= data.t2_win_pct ? 'text-emerald-400' : 'text-white');
    }
    if (t2w) {
        t2w.innerText = data.t2_win_pct + '%';
        t2w.className = 'font-bold w-16 text-right ' + (data.t2_win_pct >= data.t1_win_pct ? 'text-emerald-400' : 'text-white');
    }

    const t1a = document.getElementById('comp-t1-avg');
    const t2a = document.getElementById('comp-t2-avg');
    if (t1a) {
        t1a.innerText = data.t1_avg_score;
        t1a.className = 'font-bold w-16 text-left ' + (data.t1_avg_score >= data.t2_avg_score ? 'text-emerald-400' : 'text-white');
    }
    if (t2a) {
        t2a.innerText = data.t2_avg_score;
        t2a.className = 'font-bold w-16 text-right ' + (data.t2_avg_score >= data.t1_avg_score ? 'text-emerald-400' : 'text-white');
    }

    const t1h = document.getElementById('comp-t1-high');
    const t2h = document.getElementById('comp-t2-high');
    if (t1h) {
        t1h.innerText = data.t1_high_score;
        t1h.className = 'font-bold w-16 text-left ' + (data.t1_high_score >= data.t2_high_score ? 'text-emerald-400' : 'text-white');
    }
    if (t2h) {
        t2h.innerText = data.t2_high_score;
        t2h.className = 'font-bold w-16 text-right ' + (data.t2_high_score >= data.t1_high_score ? 'text-emerald-400' : 'text-white');
    }

    const t1l = document.getElementById('comp-t1-low');
    const t2l = document.getElementById('comp-t2-low');
    if (t1l) t1l.innerText = data.t1_low_score;
    if (t2l) t2l.innerText = data.t2_low_score;
}

function switchMatchSquadView(view) {
    const col1 = document.getElementById('squad-col-team1');
    const col2 = document.getElementById('squad-col-team2');
    const btnAll = document.getElementById('squad-btn-all');
    const btn1 = document.getElementById('squad-btn-team1');
    const btn2 = document.getElementById('squad-btn-team2');
    const container = document.getElementById('match-squads-container');

    // Reset buttons
    document.querySelectorAll('.match-squad-tab-btn').forEach(b => {
        b.className = 'match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-[#161b22] text-gray-300 border border-[#30363d] hover:border-sky-400 transition-all';
    });

    if (view === 'team1') {
        if (col1) col1.style.display = 'block';
        if (col2) col2.style.display = 'none';
        if (btn1) btn1.className = 'match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-sky-500 text-white border border-sky-400 shadow-sm transition-all';
        if (container) container.className = 'grid grid-cols-1 gap-5';
    } else if (view === 'team2') {
        if (col1) col1.style.display = 'none';
        if (col2) col2.style.display = 'block';
        if (btn2) btn2.className = 'match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500 text-white border border-emerald-400 shadow-sm transition-all';
        if (container) container.className = 'grid grid-cols-1 gap-5';
    } else {
        if (col1) col1.style.display = 'block';
        if (col2) col2.style.display = 'block';
        if (btnAll) btnAll.className = 'match-squad-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-sky-500 text-white border border-sky-400 shadow-sm transition-all';
        if (container) container.className = 'grid grid-cols-1 lg:grid-cols-2 gap-5';
    }
}

function filterMatchSquadPlayers() {
    const search = (document.getElementById('match-squad-search').value || '').toLowerCase().trim();
    document.querySelectorAll('.match-player-card').forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        if (!search || name.includes(search)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
