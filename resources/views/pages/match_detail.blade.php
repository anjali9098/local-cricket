@extends('layouts.app')

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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 items-center mb-4 sm:mb-6 pb-4 sm:pb-6 border-b border-[#30363d]">
            <!-- Team 1 -->
            <div class="flex items-center justify-between sm:justify-start gap-3 sm:gap-4 bg-[#161b22]/50 sm:bg-transparent p-3 sm:p-0 rounded-xl border border-[#30363d] sm:border-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="team-avatar w-12 h-12 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0 border-2" style="border-color: {{ $match->team1?->color_code ?? 'var(--primary)' }};">
                        {{ $match->team1?->short_name ?? ($match->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team1->name), 0, 3)) : 'T1') }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team1?->name ?? '' }}</h2>
                    </div>
                </div>
                <div class="text-right sm:ml-auto flex-shrink-0">
                    <div class="font-heading text-lg sm:text-2xl font-black text-white">
                        {{ $match->team1_score }}/{{ $match->team1_wickets }}
                    </div>
                    <div class="text-xs text-gray-400">({{ $match->team1_overs }} ov)</div>
                </div>
            </div>

            <!-- Team 2 -->
            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 bg-[#161b22]/50 sm:bg-transparent p-3 sm:p-0 rounded-xl border border-[#30363d] sm:border-0">
                <!-- Team 2 Info & Avatar (Symmetrical Left-to-Right on Mobile, Mirrored on Desktop) -->
                <div class="flex items-center gap-3 min-w-0 sm:order-2 sm:justify-end">
                    <div class="team-avatar w-12 h-12 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0 border-2 sm:order-2" style="border-color: {{ $match->team2?->color_code ?? '#38bdf8' }};">
                        {{ $match->team2?->short_name ?? ($match->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team2->name), 0, 3)) : 'T2') }}
                    </div>
                    <div class="min-w-0 sm:text-right sm:order-1">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team2?->name ?? '' }}</h2>
                    </div>
                </div>

                <!-- Team 2 Score -->
                <div class="text-right sm:order-1 flex-shrink-0 sm:mr-2">
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
                                <td class="text-left">
                                    <strong>{{ $b->player_name }}</strong>
                                    <span class="text-gray-400 font-normal text-xs ml-1.5">{{ $b->status_text }}</span>
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

                            <!-- Descriptive Commentary Line -->
                            <p class="text-xs sm:text-sm text-gray-300/90 leading-relaxed font-normal m-0">
                                @if(strtoupper($ball->outcome) === 'W')
                                    <strong class="text-red-400 font-bold">OUT!</strong> {{ $ball->batsman_name }} is dismissed off the delivery from {{ $ball->bowler_name }}.
                                @elseif($ball->outcome === '4')
                                    <strong class="text-emerald-400 font-bold">FOUR!</strong> {{ $ball->batsman_name }} cracks a gorgeous boundary off {{ $ball->bowler_name }}.
                                @elseif($ball->outcome === '6')
                                    <strong class="text-purple-400 font-bold">SIX!</strong> {{ $ball->batsman_name }} lofts it high over the boundary ropes for a maximum off {{ $ball->bowler_name }}!
                                @elseif(str_contains(strtolower($ball->outcome), 'wide'))
                                    <strong class="text-amber-400 font-bold">WIDE!</strong> {{ $ball->bowler_name }} slips down the leg/off side, wide signalled by the umpire.
                                @elseif(str_contains(strtolower($ball->outcome), 'no ball'))
                                    <strong class="text-amber-400 font-bold">NO BALL!</strong> {{ $ball->bowler_name }} oversteps the crease, free hit upcoming.
                                @elseif($ball->outcome === '0' || strtolower($ball->outcome) === 'dot')
                                    {{ $ball->bowler_name }} bowls a disciplined delivery to {{ $ball->batsman_name }}, defended safely, no run.
                                @elseif(is_numeric($ball->outcome))
                                    {{ $ball->bowler_name }} to {{ $ball->batsman_name }}, {{ $ball->outcome }} {{ (int)$ball->outcome === 1 ? 'run taken smartly' : 'runs scored' }}.
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
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6">
            <h3 class="text-base font-bold text-white mb-4">Match Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <div>
                    <span class="text-gray-400 block text-[11px]">SERIES</span>
                    <strong class="text-white">{{ $match->tournament->name ?? 'International T20 Series' }}</strong>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">VENUE</span>
                    <strong class="text-white">{{ $match->venue->name ?? 'Wankhede Stadium, Mumbai' }}</strong>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">MATCH TYPE</span>
                    <strong class="text-white">{{ $match->match_type }}</strong>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">STATUS</span>
                    <strong class="text-white">{{ strtoupper($match->status) }}</strong>
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
</script>
@endsection
