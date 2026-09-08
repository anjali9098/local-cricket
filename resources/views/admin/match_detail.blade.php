@extends(isset($isLocal) && $isLocal ? 'layouts.local' : 'layouts.admin')

@section('content')
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-10 pb-28">

    <!-- Match Header Card -->
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 mb-6 shadow-md">
        
        <div class="flex items-center justify-between flex-wrap gap-2 mb-4 sm:mb-6">
            <div class="text-[11px] sm:text-xs font-black text-gray-400 uppercase tracking-wider">
                {{ $match->match_type }} &bull; {{ $match->level_type ?? 'LOCAL' }} &bull; {{ $match->venue?->name ?? ($match->venue ?? 'WANKHEDE STADIUM') }}
            </div>
            @if($match->status === 'live')
                <span class="bg-red-500/20 border border-red-500/40 text-red-400 text-xs font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider animate-pulse">🔴 LIVE</span>
            @elseif($match->status === 'completed')
                <span class="bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-black px-2.5 py-0.5 rounded-full uppercase">✓ COMPLETED</span>
            @else
                <span class="bg-[#161b22] border border-[#30363d] text-gray-300 text-xs font-bold px-2.5 py-0.5 rounded-full uppercase">{{ $match->status }}</span>
            @endif
        </div>

        <!-- Teams Section (Mobile-Friendly Responsive Layout) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 items-center mb-4 sm:mb-6 pb-4 sm:pb-6 border-b border-[#30363d]">
            <!-- Team 1 -->
            <div class="flex items-center justify-between sm:justify-start gap-3 sm:gap-4 bg-[#161b22]/50 sm:bg-transparent p-3 sm:p-0 rounded-xl border border-[#30363d] sm:border-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0 border-2" style="background: rgba(37,99,235,0.2); border-color: {{ $match->team1?->color_code ?? '#3b82f6' }};">
                        {{ $match->team1?->short_name ?? ($match->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team1->name), 0, 3)) : 'T1') }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team1?->name ?? 'Team 1' }}</h2>
                        <div class="text-xs text-gray-400 font-semibold mt-0.5">
                            Run rate {{ $match->team1_overs > 0 ? number_format($match->team1_score / $match->team1_overs, 2) : '0.0' }}
                        </div>
                    </div>
                </div>
                <div class="text-right sm:ml-auto flex-shrink-0">
                    <div class="text-xl sm:text-2xl font-black text-white">
                        {{ $match->team1_score }}/{{ $match->team1_wickets }}
                    </div>
                    <div class="text-xs text-gray-400 font-semibold">({{ $match->team1_overs }} ov)</div>
                </div>
            </div>

            <!-- Team 2 -->
            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 bg-[#161b22]/50 sm:bg-transparent p-3 sm:p-0 rounded-xl border border-[#30363d] sm:border-0">
                <!-- Team 2 Info & Avatar (Symmetrical Left-to-Right on Mobile, Mirrored on Desktop) -->
                <div class="flex items-center gap-3 min-w-0 sm:order-2 sm:justify-end">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-sm text-white flex-shrink-0 border-2 sm:order-2" style="background: rgba(56,189,248,0.2); border-color: {{ $match->team2?->color_code ?? '#38bdf8' }};">
                        {{ $match->team2?->short_name ?? ($match->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $match->team2->name), 0, 3)) : 'T2') }}
                    </div>
                    <div class="min-w-0 sm:text-right sm:order-1">
                        <h2 class="text-base sm:text-lg font-black text-white truncate m-0 leading-tight">{{ $match->team2?->name ?? 'Team 2' }}</h2>
                        @if($match->team2_score > 0 && $match->team2_overs > 0)
                            <div class="text-xs text-gray-400 font-semibold mt-0.5">
                                Run rate {{ number_format($match->team2_score / $match->team2_overs, 2) }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Team 2 Score -->
                <div class="text-right sm:order-1 flex-shrink-0 sm:mr-2">
                    <div class="text-xl sm:text-2xl font-black text-white">
                        @if($match->team2_score > 0)
                            {{ $match->team2_score }}/{{ $match->team2_wickets }}
                        @else
                            &mdash;
                        @endif
                    </div>
                    @if($match->team2_score > 0)
                        <div class="text-xs text-gray-400 font-semibold">({{ $match->team2_overs }} ov)</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Match Status Note -->
        <div class="text-sky-400 font-bold text-xs sm:text-sm">
            {{ $match->custom_note ?? (($match->team1?->name && $match->team2?->name) ? $match->team1->name . ' vs ' . $match->team2->name . ' — in progress' : 'Match in progress') }}
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
        <button type="button" class="series-tab active px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all" onclick="switchMatchTab('scorecard', this)">Scorecard</button>
        <button type="button" class="series-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all" onclick="switchMatchTab('commentary', this)">Commentary</button>
        <button type="button" class="series-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all" onclick="switchMatchTab('info', this)">Info</button>
    </div>

    <!-- TAB 1: SCORECARD -->
    <div id="tab-scorecard" class="match-tab-content">
        <!-- BATTER TABLE -->
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5 mb-5 shadow-md">
            <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3.5">BATTERS</h4>
            <div class="table-responsive-wrapper overflow-x-auto">
                <table class="cricket-table min-w-[500px] w-full text-left">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-3 text-xs font-black text-white uppercase text-left">BATTER</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">R</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">B</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">4S</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">6S</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">SR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($match->battingStats as $b)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-2.5 px-3 text-sm">
                                    <strong class="text-white">{{ $b->player_name }}</strong>
                                    <span class="text-gray-400 text-xs ml-1.5">{{ $b->status_text }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-sm text-center font-black text-emerald-400">{{ $b->runs }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-gray-300">{{ $b->balls }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-gray-300">{{ $b->fours }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-gray-300">{{ $b->sixes }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-sky-400 font-bold">{{ $b->strike_rate }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-6 text-xs font-semibold">No batting stats recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($isLocal) && $isLocal)
                <div class="mt-4 pt-4 border-t border-[#30363d]">
                    <form method="POST" action="{{ route('local.add-scorecard-stat') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                        @csrf
                        <input type="hidden" name="match_id" value="{{ $match->id }}">
                        <input type="hidden" name="stat_type" value="BATTER">
                        
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-bold text-gray-400 mb-1">Batter Name</label>
                            <input type="text" name="player_name" placeholder="Batter Name" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3 py-2 text-xs focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2 grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Runs</label>
                                <input type="number" name="runs" placeholder="0" min="0" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Balls</label>
                                <input type="number" name="balls" placeholder="0" min="1" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                        </div>
                        <div class="sm:col-span-2 grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">4s</label>
                                <input type="number" name="fours" placeholder="0" min="0" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">6s</label>
                                <input type="number" name="sixes" placeholder="0" min="0" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-400 mb-1">Dismissal</label>
                            <select name="status_text" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none">
                                <option value="not out">not out</option>
                                <option value="out">out</option>
                                <option value="run out">run out</option>
                                <option value="bowled">bowled</option>
                                <option value="caught">caught</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 px-3 rounded-xl transition-all shadow-sm">
                                + Add Stat
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- BOWLER TABLE -->
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5 shadow-md">
            <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3.5">BOWLERS</h4>
            <div class="table-responsive-wrapper overflow-x-auto">
                <table class="cricket-table min-w-[500px] w-full text-left">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-3 text-xs font-black text-white uppercase text-left">BOWLER</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">O</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">R</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">W</th>
                            <th class="py-2.5 px-3 text-xs font-black text-white text-center uppercase">ECON</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($match->bowlingStats as $bw)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-2.5 px-3 text-sm font-bold text-white">{{ $bw->player_name }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-gray-300">{{ $bw->overs }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-gray-300">{{ $bw->runs }}</td>
                                <td class="py-2.5 px-3 text-sm text-center font-black text-red-400">{{ $bw->wickets }}</td>
                                <td class="py-2.5 px-3 text-sm text-center text-sky-400 font-bold">{{ $bw->economy }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-6 text-xs font-semibold">No bowling stats recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($isLocal) && $isLocal)
                <div class="mt-4 pt-4 border-t border-[#30363d]">
                    <form method="POST" action="{{ route('local.add-scorecard-stat') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                        @csrf
                        <input type="hidden" name="match_id" value="{{ $match->id }}">
                        <input type="hidden" name="stat_type" value="BOWLER">
                        
                        <div class="sm:col-span-5">
                            <label class="block text-[11px] font-bold text-gray-400 mb-1">Bowler Name</label>
                            <input type="text" name="player_name" placeholder="Bowler Name" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3 py-2 text-xs focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-5 grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Overs</label>
                                <input type="number" name="overs" placeholder="4.0" step="0.1" min="0" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Runs</label>
                                <input type="number" name="runs" placeholder="0" min="0" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1">Wickets</label>
                                <input type="number" name="wickets" placeholder="0" min="0" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-2.5 py-2 text-xs focus:border-blue-500 outline-none text-center">
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 px-3 rounded-xl transition-all shadow-sm">
                                + Add Stat
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: COMMENTARY -->
    <div id="tab-commentary" class="match-tab-content" style="display:none;">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 shadow-md">
            @forelse($balls as $ball)
                <div class="flex gap-3.5 py-3 border-b border-white/5 items-start">
                    <div class="font-black text-xs sm:text-sm text-sky-400 min-w-[36px] pt-0.5">
                        {{ $ball->over_num }}
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-bold text-white flex items-center gap-2">
                            <span>{{ $ball->over_num }} &mdash;</span>
                            <span class="px-2 py-0.5 rounded text-xs font-black {{ $ball->outcome === 'W' ? 'bg-red-500/20 text-red-400' : ($ball->outcome == '4' || $ball->outcome == '6' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-[#161b22] text-white') }}">
                                {{ $ball->outcome }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $ball->bowler_name }} to {{ $ball->batsman_name }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-8 text-xs sm:text-sm font-semibold">No commentary entries added yet.</p>
            @endforelse
        </div>
    </div>

    <!-- TAB 3: INFO -->
    <div id="tab-info" class="match-tab-content" style="display:none;">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 shadow-md">
            <h3 class="text-sm sm:text-base font-black text-white uppercase tracking-tight mb-4">Match Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <div class="bg-[#161b22] border border-[#30363d] p-3.5 rounded-xl">
                    <span class="text-gray-400 block text-[11px] font-bold uppercase mb-1">SERIES / TOURNAMENT</span>
                    <strong class="text-white">{{ $match->tournament->name ?? 'Local Tournament Series' }}</strong>
                </div>
                <div class="bg-[#161b22] border border-[#30363d] p-3.5 rounded-xl">
                    <span class="text-gray-400 block text-[11px] font-bold uppercase mb-1">VENUE</span>
                    <strong class="text-white">{{ $match->venue?->name ?? ($match->venue ?? 'Wankhede Stadium, Mumbai') }}</strong>
                </div>
                <div class="bg-[#161b22] border border-[#30363d] p-3.5 rounded-xl">
                    <span class="text-gray-400 block text-[11px] font-bold uppercase mb-1">MATCH TYPE</span>
                    <strong class="text-white">{{ $match->match_type }}</strong>
                </div>
                <div class="bg-[#161b22] border border-[#30363d] p-3.5 rounded-xl">
                    <span class="text-gray-400 block text-[11px] font-bold uppercase mb-1">STATUS</span>
                    <strong class="text-white uppercase">{{ $match->status }}</strong>
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
