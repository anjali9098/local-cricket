@extends(isset($isLocal) && $isLocal ? 'layouts.local' : 'layouts.admin')

@section('content')
<main style="background: var(--local-bg, var(--bg-main, #000000)); color: var(--text-main, #f8fafc); min-height: 100vh; padding: 24px 14px 80px; font-family: var(--font-body, 'Inter', sans-serif);" class="sm:px-6 sm:py-8">
    <div style="max-width: 860px; margin: 0 auto;">
        
        @php
            $maxOvers = (int)($match->tournament->overs ?? ($match->match_type === 'T10' ? 10 : ($match->match_type === 'ODI' ? 50 : 20)));
            if ($maxOvers <= 0) $maxOvers = 20;

            $innings = $match->current_innings ?? 1;

            $battingTeam = $innings == 1 ? $match->team1 : $match->team2;
            $bowlingTeam = $innings == 1 ? $match->team2 : $match->team1;

            if (!empty($match->result_text) && str_starts_with($match->result_text, 'toss:')) {
                $parts = explode(':', $match->result_text);
                $tossWinnerId = (int)($parts[1] ?? 0);
                $decision = $parts[2] ?? 'bat';
                $team1BattedFirst = ($decision === 'bat' && $tossWinnerId == $match->team1_id) || ($decision === 'field' && $tossWinnerId == $match->team2_id);
                if (!$team1BattedFirst) {
                    $battingTeam = $innings == 1 ? $match->team2 : $match->team1;
                    $bowlingTeam = $innings == 1 ? $match->team1 : $match->team2;
                }
            }

            $currentScore = $innings == 1 ? $match->team1_score : $match->team2_score;
            $currentWickets = $innings == 1 ? $match->team1_wickets : $match->team2_wickets;
            $currentOvers = (float)($innings == 1 ? $match->team1_overs : $match->team2_overs);

            $ballsBowled = floor($currentOvers) * 6 + round(($currentOvers - floor($currentOvers)) * 10);
            $crr = $ballsBowled > 0 ? round(($currentScore / $ballsBowled) * 6, 2) : 0.00;

            $target = $match->team1_score + 1;
            $runsNeeded = max(0, $target - $match->team2_score);
            $ballsLeft = max(0, ($maxOvers * 6) - $ballsBowled);
            $rrr = $ballsLeft > 0 ? round(($runsNeeded / $ballsLeft) * 6, 2) : 0.00;

            // Active Striker, Non-Striker, Bowler
            $striker = $match->battingStats->where('status_text', 'striker')->first() 
                ?? $match->battingStats->whereIn('status_text', ['not out', 'not out *'])->first();
            
            $nonStriker = $match->battingStats->where('status_text', 'non-striker')->first()
                ?? $match->battingStats->where('id', '!=', $striker?->id)->whereIn('status_text', ['not out', 'not out *'])->first();

            $bowler = $match->bowlingStats->sortByDesc('id')->first();

            $strikerName = $striker ? $striker->player_name : ($battingTeam->players->first()->name ?? 'Striker');
            $strikerRuns = $striker ? $striker->runs : 0;
            $strikerBalls = $striker ? $striker->balls : 0;
            $strikerFours = $striker ? $striker->fours : 0;
            $strikerSixes = $striker ? $striker->sixes : 0;
            $strikerSR = $striker ? $striker->strike_rate : '0.00';

            $nonStrikerName = $nonStriker ? $nonStriker->player_name : ($battingTeam->players->skip(1)->first()->name ?? 'Non-Striker');
            $nonStrikerRuns = $nonStriker ? $nonStriker->runs : 0;
            $nonStrikerBalls = $nonStriker ? $nonStriker->balls : 0;
            $nonStrikerFours = $nonStriker ? $nonStriker->fours : 0;
            $nonStrikerSixes = $nonStriker ? $nonStriker->sixes : 0;
            $nonStrikerSR = $nonStriker ? $nonStriker->strike_rate : '0.00';

            $bowlerName = $bowler ? $bowler->player_name : ($bowlingTeam->players->first()->name ?? 'Bowler');
            $bowlerOvers = $bowler ? $bowler->overs : '0.0';
            $bowlerRuns = $bowler ? $bowler->runs : 0;
            $bowlerWickets = $bowler ? $bowler->wickets : 0;
            $bowlerEcon = $bowler ? $bowler->economy : '0.00';

            // Separate Innings 1 and Innings 2 balls
            $innings1Balls = collect();
            $innings2Balls = collect();
            $inSecondInnings = false;
            $prevOver = -1.0;

            if (isset($allBalls) && $allBalls->isNotEmpty()) {
                foreach ($allBalls as $b) {
                    $ov = (float)$b->over_num;
                    if ($prevOver >= 1.0 && $ov < 1.0) {
                        $inSecondInnings = true;
                    }
                    if ($inSecondInnings) {
                        $innings2Balls->push($b);
                    } else {
                        $innings1Balls->push($b);
                    }
                    $prevOver = $ov;
                }
            }

            $currentInningsBalls = ($innings == 2) ? $innings2Balls : $innings1Balls;

            // Current Over Balls (balls bowled in current ongoing over)
            $currOverInt = (int)floor($currentOvers);
            $thisOverBalls = $currentInningsBalls->filter(function($b) use ($currOverInt) {
                return (int)floor((float)$b->over_num) === $currOverInt;
            });

            // Recent balls of current innings
            $recentBalls = $currentInningsBalls->take(-12);
        @endphp

        <!-- Top Header & Navigation -->
        <div class="flex justify-between items-center mb-5 flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    @if($match->status === 'completed')
                        <span class="bg-emerald-500 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">✓ COMPLETED</span>
                    @else
                        <span class="bg-red-500 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider animate-pulse">&bull; LIVE SCORER</span>
                    @endif
                    <span class="text-gray-400 font-bold text-xs sm:text-sm">{{ $match->tournament->name ?? 'Tournament Match' }} &bull; {{ $maxOvers }} Overs Match</span>
                </div>
                <h1 class="text-lg sm:text-2xl font-black text-white uppercase tracking-tight m-0">
                    {{ $match->team1->name ?? 'TEAM 1' }} <span class="text-gray-400 font-medium">vs</span> {{ $match->team2->name ?? 'TEAM 2' }}
                </h1>
            </div>
            
            <div class="flex gap-2 items-center">
                <a href="{{ isset($isLocal) && $isLocal ? route('local.match.detail', $match->id) : route('admin.match.detail', $match->id) }}" target="_blank" class="bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] text-white text-xs sm:text-sm font-bold px-3.5 py-2 rounded-xl inline-flex items-center gap-1.5 transition-all shadow-sm">
                    <span>📊</span> <span class="hidden sm:inline">Full</span> Scorecard
                </a>
                <a href="{{ isset($isLocal) && $isLocal ? route('local.manage-tournament', $match->tournament_id ?? 1) : route('admin.dashboard') }}" class="bg-[#161b22] hover:bg-[#21262d] text-gray-300 hover:text-white border border-[#30363d] text-xs sm:text-sm font-bold px-3 py-2 rounded-xl transition-all">
                    ← Back
                </a>
            </div>
        </div>

        <!-- Match Result Banner (If Completed) -->
        @if($match->status === 'completed' && !empty($match->result_text))
            <div class="bg-gradient-to-r from-blue-900 to-blue-600 text-white rounded-2xl p-4 sm:p-5 mb-5 flex items-center justify-between shadow-lg shadow-blue-500/20">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🏆</span>
                    <div>
                        <div class="text-[11px] font-black uppercase text-blue-200 tracking-wider">MATCH RESULT</div>
                        <div class="text-base sm:text-lg font-black">{{ $match->result_text }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Innings 2 Target Header Bar (If 2nd Innings) -->
        @if($innings == 2)
            <div class="bg-[#0d1117] border border-[#30363d] text-white rounded-2xl p-3.5 sm:p-4 mb-4 flex justify-between items-center flex-wrap gap-2.5 shadow-md">
                <div class="text-xs sm:text-sm">
                    <span class="text-gray-400">1st Innings: </span>
                    <strong class="text-sky-400">{{ $bowlingTeam->short_name ?? $bowlingTeam->name }} {{ $match->team1_score }}/{{ $match->team1_wickets }} ({{ $match->team1_overs }} ov)</strong>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="bg-blue-600 text-white font-black text-xs px-3 py-1 rounded-lg">
                        Target: {{ $target }} runs
                    </span>
                    @if($match->status !== 'completed')
                        <span class="text-xs sm:text-sm text-amber-300 font-bold">
                            Need {{ $runsNeeded }} off {{ $ballsLeft }} balls (RRR: {{ $rrr }})
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Main Live Scoreboard Card (Dark Emerald Green) -->
        <div class="bg-gradient-to-br from-[#02381f] to-[#064e3b] border border-white/10 rounded-2xl p-4 sm:p-6 text-white mb-6 relative overflow-hidden shadow-2xl">
            <!-- Background Glow -->
            <div class="absolute -top-16 -right-16 w-52 h-52 bg-white/5 rounded-full pointer-events-none blur-xl"></div>
            
            <div class="relative z-10">
                <!-- Batting Team & Innings Indicator -->
                <div class="flex justify-between items-center mb-2">
                    <div class="text-xs sm:text-sm font-black text-emerald-300 tracking-wide flex items-center gap-2">
                        <span>🏏</span> {{ strtoupper($battingTeam->name) }} &bull; INNINGS {{ $innings }}
                    </div>
                    <div class="text-xs font-bold text-white/90 bg-black/40 px-2.5 py-1 rounded-lg border border-white/10">
                        CRR: <span class="text-emerald-300">{{ $crr }}</span>
                    </div>
                </div>
                
                <!-- Large Runs & Wickets Display -->
                <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-4xl sm:text-6xl font-black leading-none tracking-tight">{{ $currentScore }}</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-white/70">/ {{ $currentWickets }}</span>
                </div>
                
                <!-- Overs Count with Max Overs of Game -->
                <div class="text-sm sm:text-base font-bold text-white/90 mb-4">
                    Overs <span class="text-emerald-300 font-black text-base sm:text-lg">{{ $currentOvers }}</span> <span class="text-white/50 font-semibold">/ {{ $maxOvers }}</span>
                    <span class="text-xs text-white/70 font-medium ml-2">({{ $maxOvers * 6 - $ballsBowled }} balls left)</span>
                </div>

                <!-- ⚡ PROMINENT "THIS OVER" LIVE BALL STRIP ⚡ -->
                <div class="mb-5 pt-3.5 border-t border-white/15">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <span class="text-[11px] sm:text-xs font-black text-emerald-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span>⚡</span> THIS OVER (Over {{ floor($currentOvers) + 1 }})
                        </span>
                        <span class="text-[11px] font-bold text-gray-300 bg-black/30 px-2 py-0.5 rounded border border-white/10">
                            {{ $thisOverBalls->count() }}/6 balls bowled
                        </span>
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        @if($thisOverBalls->count() > 0)
                            @foreach($thisOverBalls as $ball)
                                @php
                                    $out = $ball->outcome;
                                    $bgClass = 'bg-[#161b22] text-white border-white/20';
                                    if ($out === '4') {
                                        $bgClass = 'bg-emerald-500 text-white font-black border-emerald-400 shadow-md shadow-emerald-500/30';
                                    } elseif ($out === '6') {
                                        $bgClass = 'bg-emerald-700 text-white font-black border-emerald-500 shadow-md shadow-emerald-700/30';
                                    } elseif ($out === 'W') {
                                        $bgClass = 'bg-red-600 text-white font-black border-red-500 shadow-md shadow-red-500/30 animate-pulse';
                                    } elseif (in_array($out, ['2', '3'])) {
                                        $bgClass = 'bg-amber-600 text-white font-bold border-amber-500';
                                    } elseif (in_array($out, ['1', '5'])) {
                                        $bgClass = 'bg-sky-600 text-white font-bold border-sky-500';
                                    } elseif (str_contains($out, 'Wide') || str_contains($out, 'No ball') || str_contains($out, 'Bye')) {
                                        $bgClass = 'bg-amber-500/30 text-amber-300 font-bold border-amber-500/50';
                                    }
                                @endphp
                                <div title="Ball {{ $ball->over_num }} &bull; {{ $ball->bowler_name }} to {{ $ball->batsman_name }}" class="h-8 min-w-[32px] px-2 rounded-full border flex items-center justify-center text-xs font-black transition-transform hover:scale-110 {{ $bgClass }}">
                                    {{ str_replace('Dot ball', '0', $out) }}
                                </div>
                            @endforeach

                            <!-- Remaining Placeholder Slots in This Over -->
                            @for($i = $thisOverBalls->count(); $i < 6; $i++)
                                <div class="w-8 h-8 rounded-full border border-dashed border-white/30 bg-black/20 flex items-center justify-center text-white/40 text-xs font-bold" title="Ball {{ $i + 1 }} pending">
                                    &bull;
                                </div>
                            @endfor
                        @else
                            <!-- Over Starting: Show all 6 pending slots -->
                            @for($i = 0; $i < 6; $i++)
                                <div class="w-8 h-8 rounded-full border border-dashed border-white/30 bg-black/20 flex items-center justify-center text-white/40 text-xs font-bold" title="Ball {{ $i + 1 }} pending">
                                    &bull;
                                </div>
                            @endfor
                            <span class="text-xs font-semibold text-emerald-200/80 ml-2 italic">Ready for 1st ball</span>
                        @endif
                    </div>
                </div>

                <!-- Striker, Non-striker, and Bowler Stats Cards (Responsive Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 border-t border-white/15 pt-4">
                    <!-- Striker -->
                    <div class="bg-black/30 border border-emerald-500/40 rounded-xl p-3 backdrop-blur-sm">
                        <div class="text-[10px] font-black text-emerald-300 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>STRIKER *</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </div>
                        <div class="font-black text-sm text-white truncate mb-1" title="{{ $strikerName }}">
                            {{ $strikerName }} *
                        </div>
                        <div class="text-xs font-bold text-white/90">
                            <span class="text-yellow-300 font-black text-sm">{{ $strikerRuns }}</span> <span class="text-gray-300">({{ $strikerBalls }} b)</span>
                        </div>
                        <div class="text-[11px] text-gray-300 mt-1">
                            4s: {{ $strikerFours }} &bull; 6s: {{ $strikerSixes }} &bull; SR: {{ $strikerSR }}
                        </div>
                    </div>

                    <!-- Non-Striker -->
                    <div class="bg-black/30 border border-white/10 rounded-xl p-3 backdrop-blur-sm">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">NON-STRIKER</div>
                        <div class="font-extrabold text-sm text-white truncate mb-1" title="{{ $nonStrikerName }}">
                            {{ $nonStrikerName }}
                        </div>
                        <div class="text-xs font-bold text-white/90">
                            <span class="text-yellow-300 font-black text-sm">{{ $nonStrikerRuns }}</span> <span class="text-gray-300">({{ $nonStrikerBalls }} b)</span>
                        </div>
                        <div class="text-[11px] text-gray-300 mt-1">
                            4s: {{ $nonStrikerFours }} &bull; 6s: {{ $nonStrikerSixes }} &bull; SR: {{ $nonStrikerSR }}
                        </div>
                    </div>

                    <!-- Bowler -->
                    <div class="bg-black/30 border border-sky-500/40 rounded-xl p-3 backdrop-blur-sm">
                        <div class="text-[10px] font-black text-sky-300 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>BOWLER</span>
                            <span class="text-[10px] font-bold text-sky-400">Overs</span>
                        </div>
                        <div class="font-black text-sm text-white truncate mb-1" title="{{ $bowlerName }}">
                            {{ $bowlerName }}
                        </div>
                        <div class="text-xs font-bold text-white/90">
                            {{ $bowlerOvers }} ov &bull; {{ $bowlerRuns }} r &bull; <span class="text-red-400 font-black">{{ $bowlerWickets }} w</span>
                        </div>
                        <div class="text-[11px] text-gray-300 mt-1">
                            Econ: <span class="text-sky-300 font-bold">{{ $bowlerEcon }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Change Players Trigger -->
                <div class="mt-3.5 pt-3 border-t border-dashed border-white/15 flex justify-end">
                    <button type="button" onclick="document.getElementById('changePlayersModal').style.display='flex';" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <span>⚙️</span> Change Striker / Bowler
                    </button>
                </div>
            </div>
        </div>

        <!-- Innings Switch Bar / Control -->
        @if($innings == 1 && $match->status !== 'completed')
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 mb-5 flex items-center justify-between flex-wrap gap-3 shadow-md">
                <div>
                    <strong class="text-white text-xs sm:text-sm">1st Innings Ongoing:</strong>
                    <span class="text-gray-400 text-xs sm:text-sm ml-1">{{ $currentOvers }} of {{ $maxOvers }} overs bowled</span>
                </div>
                <button type="button" onclick="document.getElementById('switchInningsModal').style.display='flex';" class="bg-blue-600 hover:bg-blue-500 text-white font-black text-xs sm:text-sm px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-md shadow-blue-500/20">
                    <span>🔄</span> End 1st Innings & Start 2nd Innings
                </button>
            </div>
        @endif

        <!-- Ball Recording Control Pad -->
        <div class="bg-[#0d1117] rounded-2xl border border-[#30363d] p-4 sm:p-6 mb-6 shadow-md">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h3 class="text-sm sm:text-base font-black text-white m-0 uppercase tracking-tight flex items-center gap-2">
                    <span>🏏</span> Record Ball Delivery
                </h3>
                <span class="text-xs text-gray-400 font-semibold">
                    Striker: <strong class="text-white">{{ $strikerName }}</strong> | Bowler: <strong class="text-sky-400">{{ $bowlerName }}</strong>
                </span>
            </div>

            <!-- Live Over Tracker Strip directly inside the Pad -->
            <div class="mb-4 p-3 bg-[#161b22] border border-[#30363d] rounded-xl flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2 overflow-x-auto pb-0.5">
                    <span class="text-xs font-black text-emerald-400 uppercase tracking-wider flex items-center gap-1 flex-shrink-0">
                        <span>⚡</span> Over {{ floor($currentOvers) + 1 }}:
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($thisOverBalls->count() > 0)
                            @foreach($thisOverBalls as $b)
                                @php
                                    $out = $b->outcome;
                                    $c = 'bg-slate-700 text-white';
                                    if ($out === '4') $c = 'bg-emerald-600 text-white font-black';
                                    elseif ($out === '6') $c = 'bg-emerald-800 text-white font-black';
                                    elseif ($out === 'W') $c = 'bg-red-600 text-white font-black';
                                    elseif (str_contains($out, 'Wide') || str_contains($out, 'No ball')) $c = 'bg-amber-600 text-white font-bold';
                                @endphp
                                <span class="h-6 min-w-[24px] px-1.5 rounded-full flex items-center justify-center text-[11px] font-black {{ $c }}">
                                    {{ str_replace('Dot ball', '0', $out) }}
                                </span>
                            @endforeach
                            @for($i = $thisOverBalls->count(); $i < 6; $i++)
                                <span class="w-6 h-6 rounded-full border border-dashed border-gray-600 flex items-center justify-center text-[10px] text-gray-500 font-bold">&bull;</span>
                            @endfor
                        @else
                            @for($i = 0; $i < 6; $i++)
                                <span class="w-6 h-6 rounded-full border border-dashed border-gray-600 flex items-center justify-center text-[10px] text-gray-500 font-bold">&bull;</span>
                            @endfor
                            <span class="text-[11px] text-gray-400 italic ml-1">Over starting</span>
                        @endif
                    </div>
                </div>
                <div class="text-xs font-bold text-gray-300">
                    Total in Over: <strong class="text-emerald-400">{{ $thisOverBalls->count() }} b</strong>
                </div>
            </div>
            
            <form id="ballDeliveryForm" method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.score-update') : route('admin.score-update') }}">
                @csrf
                <input type="hidden" name="match_id" value="{{ $match->id }}">
                <input type="hidden" name="striker_name" value="{{ $strikerName }}">
                <input type="hidden" name="non_striker_name" value="{{ $nonStrikerName }}">
                <input type="hidden" name="bowler_name" value="{{ $bowlerName }}">
                <input type="hidden" name="runs" id="input_runs" value="0">
                <input type="hidden" name="extras" id="input_extras" value="">

                <!-- Active Combination Banner (appears when an extra is selected) -->
                <div id="combination_indicator" style="display: none;" class="bg-gradient-to-r from-sky-950/40 to-emerald-950/30 border border-sky-500/40 border-l-4 border-l-sky-400 rounded-xl p-3.5 mb-4 shadow-md">
                    <div class="flex justify-between items-center flex-wrap gap-3 w-full">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/20 border border-sky-500/40 flex items-center justify-center text-sm flex-shrink-0">
                                ⚡
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[10px] font-bold uppercase text-gray-400">Active Extra</span>
                                    <span id="indicator_badge" class="bg-sky-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase">
                                        WIDE
                                    </span>
                                </div>
                                <div class="text-xs text-white font-medium">
                                    Tap runs <span class="text-sky-400 font-bold">(0 to 6)</span> below to record (e.g. <span id="indicator_example" class="text-emerald-400 font-bold">Wide + 4</span>)
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button" onclick="submitQuickExtraOnly()" id="quick_extra_btn" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3 py-2 rounded-lg transition-all shadow-sm">
                                <span>✓</span> <span id="quick_extra_label">Record Wide (+1)</span>
                            </button>
                            <button type="button" onclick="clearExtraSelection()" title="Cancel Extra" class="bg-red-500/15 hover:bg-red-500/25 border border-red-500/30 text-red-400 font-bold text-xs px-2.5 py-2 rounded-lg transition-all">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Runs Grid (0 to 6) -->
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5 mb-4">
                    <button type="button" onclick="handleRunClick(0)" id="btn_run_0" class="bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black text-white cursor-pointer transition-all active:scale-95 shadow-sm">0</button>
                    <button type="button" onclick="handleRunClick(1)" id="btn_run_1" class="bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black text-white cursor-pointer transition-all active:scale-95 shadow-sm">1</button>
                    <button type="button" onclick="handleRunClick(2)" id="btn_run_2" class="bg-orange-600 hover:bg-orange-500 text-white rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black cursor-pointer transition-all active:scale-95 shadow-md shadow-orange-600/20">2</button>
                    <button type="button" onclick="handleRunClick(3)" id="btn_run_3" class="bg-orange-600 hover:bg-orange-500 text-white rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black cursor-pointer transition-all active:scale-95 shadow-md shadow-orange-600/20">3</button>
                    <button type="button" onclick="handleRunClick(4)" id="btn_run_4" class="bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black cursor-pointer transition-all active:scale-95 shadow-md shadow-emerald-600/20">4</button>
                    <button type="button" onclick="handleRunClick(5)" id="btn_run_5" class="bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black text-white cursor-pointer transition-all active:scale-95 shadow-sm">5</button>
                    <button type="button" onclick="handleRunClick(6)" id="btn_run_6" class="bg-emerald-800 hover:bg-emerald-700 text-white rounded-xl py-3.5 sm:py-4 text-base sm:text-xl font-black cursor-pointer transition-all active:scale-95 shadow-md shadow-emerald-800/20">6</button>
                </div>

                <!-- Extras Row (Click to toggle/combine with runs) -->
                <div class="grid grid-cols-4 gap-1.5 sm:gap-2.5 mb-5">
                    <button type="button" onclick="toggleExtra('wide')" id="btn_extra_wide" class="bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all">Wide</button>
                    <button type="button" onclick="toggleExtra('no_ball')" id="btn_extra_no_ball" class="bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all">No Ball</button>
                    <button type="button" onclick="toggleExtra('bye')" id="btn_extra_bye" class="bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all">Bye</button>
                    <button type="button" onclick="toggleExtra('leg_bye')" id="btn_extra_leg_bye" class="bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all">Leg Bye</button>
                </div>

                <!-- Wicket & Undo Actions -->
                <div class="flex gap-2.5 sm:gap-3">
                    <button type="button" onclick="openWicketModal()" class="flex-1 bg-red-600 hover:bg-red-500 text-white font-black py-3.5 sm:py-4 rounded-xl text-xs sm:text-sm uppercase tracking-wider cursor-pointer transition-all shadow-lg shadow-red-600/20 flex items-center justify-center gap-2">
                        <span>⚡</span> WICKET FALLEN
                    </button>
                    <button type="submit" formaction="{{ isset($isLocal) && $isLocal ? route('local.undo-score') : route('admin.undo-score') }}" class="bg-[#161b22] hover:bg-[#21262d] border border-[#30363d] text-white font-bold py-3.5 sm:py-4 px-4 sm:px-6 rounded-xl text-xs sm:text-sm cursor-pointer transition-all flex items-center gap-1.5 shadow-sm">
                        <span>↩</span> Undo
                    </button>
                </div>
            </form>
        </div>

        <!-- Complete Ball-By-Ball History (All Balls Bowled) -->
        <div class="bg-[#0d1117] rounded-2xl border border-[#30363d] p-4 sm:p-6 shadow-md mb-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h3 class="text-sm sm:text-base font-black text-white m-0 uppercase tracking-tight flex items-center gap-2">
                    <span>🏏</span> Match Balls Timeline ({{ isset($allBalls) ? count($allBalls) : 0 }} Total Deliveries)
                </h3>
                <span class="text-xs text-gray-400 font-semibold">Scroll horizontally to inspect balls &rarr;</span>
            </div>

            <!-- Timeline Filter Pills -->
            <div class="flex items-center gap-2 mb-3 overflow-x-auto pb-1 scrollbar-none">
                <button type="button" onclick="filterBallsTimeline('all', this)" class="timeline-tab-btn active px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold border border-blue-500">
                    All Deliveries ({{ count($allBalls) }})
                </button>
                <button type="button" onclick="filterBallsTimeline('inn1', this)" class="timeline-tab-btn px-3 py-1 bg-[#161b22] text-gray-300 rounded-lg text-xs font-bold border border-[#30363d]">
                    Innings 1 ({{ $innings1Balls->count() }})
                </button>
                @if($innings2Balls->count() > 0 || $innings == 2)
                <button type="button" onclick="filterBallsTimeline('inn2', this)" class="timeline-tab-btn px-3 py-1 bg-[#161b22] text-gray-300 rounded-lg text-xs font-bold border border-[#30363d]">
                    Innings 2 ({{ $innings2Balls->count() }})
                </button>
                @endif
            </div>
            
            <!-- Horizontal Scrollable Timeline of Deliveries -->
            <div class="flex gap-2 overflow-x-auto py-3 scrollbar-none" id="timelineBallsContainer">
                @if(isset($allBalls) && count($allBalls) > 0)
                    @foreach($allBalls as $index => $ball)
                        @php
                            $out = $ball->outcome;
                            $bgClass = 'bg-[#161b22] text-white border-[#30363d]';
                            if ($out === '4') {
                                $bgClass = 'bg-emerald-600 text-white font-black border-emerald-500 shadow-sm';
                            } elseif ($out === '6') {
                                $bgClass = 'bg-emerald-800 text-white font-black border-emerald-600 shadow-sm';
                            } elseif ($out === 'W') {
                                $bgClass = 'bg-red-600 text-white font-black border-red-500 shadow-sm';
                            } elseif (in_array($out, ['2', '3'])) {
                                $bgClass = 'bg-amber-600 text-white font-bold border-amber-500';
                            } elseif (in_array($out, ['1', '5'])) {
                                $bgClass = 'bg-sky-600 text-white font-bold border-sky-500';
                            } elseif (str_contains($out, 'Wide') || str_contains($out, 'No ball') || str_contains($out, 'Bye')) {
                                $bgClass = 'bg-amber-500/20 text-amber-300 font-bold border-amber-500/40';
                            }
                            $isInn2 = $innings2Balls->contains('id', $ball->id);
                        @endphp
                        <div data-inn="{{ $isInn2 ? 'inn2' : 'inn1' }}" title="Ball {{ $ball->over_num }} &bull; Bowler: {{ $ball->bowler_name }} &bull; Batsman: {{ $ball->batsman_name }}" class="timeline-ball-item h-9 min-w-[36px] px-2.5 rounded-xl border flex items-center justify-center text-xs font-black flex-shrink-0 transition-transform hover:scale-110 cursor-pointer {{ $bgClass }}">
                            {{ str_replace('Dot ball', '0', $out) }}
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-400 text-xs italic py-3">No deliveries recorded yet for this match.</div>
                @endif
            </div>

            <!-- Over by Over Summary Breakdown -->
            @if(isset($allBalls) && count($allBalls) > 0)
                <div class="mt-5 pt-4 border-t border-[#30363d]">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Over-By-Over Breakdown</h4>
                    
                    @php
                        $oversGrouped = [];
                        foreach ($allBalls as $b) {
                            $overIdx = floor((float)$b->over_num);
                            $oversGrouped[$overIdx][] = $b;
                        }
                    @endphp

                    <div class="flex flex-col gap-2.5">
                        @foreach($oversGrouped as $ovIdx => $ballsInOver)
                            @php
                                $overRuns = 0;
                                foreach ($ballsInOver as $b) {
                                    if ($b->outcome === '4') $overRuns += 4;
                                    elseif ($b->outcome === '6') $overRuns += 6;
                                    elseif (in_array($b->outcome, ['1', '2', '3', '5'])) $overRuns += (int)$b->outcome;
                                    elseif (str_contains($b->outcome, 'Wide') || str_contains($b->outcome, 'No ball') || str_contains($b->outcome, 'Bye')) {
                                        $overRuns += 1;
                                        if (str_contains($b->outcome, '+')) {
                                            $p = explode('+', $b->outcome);
                                            $overRuns += (int)($p[1] ?? 0);
                                        }
                                    }
                                }
                                $lastBowler = end($ballsInOver)->bowler_name ?? 'Bowler';
                            @endphp
                            <div class="flex items-center justify-between bg-[#161b22] border border-[#30363d] p-3 rounded-xl flex-wrap gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="font-black text-xs sm:text-sm text-white min-w-[65px]">Over {{ $ovIdx + 1 }}:</span>
                                    <div class="flex gap-1.5 items-center flex-wrap">
                                        @foreach($ballsInOver as $b)
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-black {{ $b->outcome === 'W' ? 'bg-red-500/20 text-red-400 border border-red-500/40' : ($b->outcome == '4' || $b->outcome == '6' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-[#0d1117] text-white border border-[#30363d]') }}">
                                                {{ $b->outcome }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 font-semibold">
                                    <strong class="text-white">{{ $overRuns }} runs</strong> &bull; Bowler: <span class="text-sky-400">{{ $lastBowler }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>
</main>

<!-- Modal 1: Wicket Modal -->
<div id="wicketModal" style="display:none;" class="fixed inset-0 bg-black/80 z-50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h2 class="text-lg font-black text-red-400 mb-4 flex items-center gap-2">
            <span>⚡</span> Wicket Fallen!
        </h2>
        
        <form method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.score-update') : route('admin.score-update') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="match_id" value="{{ $match->id }}">
            <input type="hidden" name="is_wicket" value="1">
            <input type="hidden" name="runs" value="0">
            <input type="hidden" name="bowler_name" value="{{ $bowlerName }}">

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Who got out? *</label>
                <select name="striker_name" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    <option value="{{ $strikerName }}">Striker: {{ $strikerName }} ({{ $strikerRuns }} runs)</option>
                    <option value="{{ $nonStrikerName }}">Non-Striker: {{ $nonStrikerName }} ({{ $nonStrikerRuns }} runs)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Next Incoming Batsman</label>
                <select name="new_batsman_name" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($battingTeam->players as $p)
                        @if($p->name !== $strikerName && $p->name !== $nonStrikerName)
                            <option value="{{ $p->name }}">{{ $p->name }} ({{ $p->role }})</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2.5 justify-end pt-2">
                <button type="button" onclick="document.getElementById('wicketModal').style.display='none';" class="bg-transparent hover:bg-white/5 border border-[#30363d] text-gray-400 font-bold px-4 py-2 rounded-xl text-xs">Cancel</button>
                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white font-black px-5 py-2 rounded-xl text-xs shadow-md">Confirm Wicket</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Change / Swap Active Players -->
<div id="changePlayersModal" style="display:none;" class="fixed inset-0 bg-black/80 z-50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h2 class="text-base sm:text-lg font-black text-white mb-4 flex items-center gap-2">
            <span>⚙️</span> Change Active On-Crease Players
        </h2>
        
        <form method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.change-players', $match->id) : route('admin.change-players', $match->id) }}" class="flex flex-col gap-3.5">
            @csrf
            
            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Select Striker (*)</label>
                <select name="striker_name" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($battingTeam->players as $p)
                        <option value="{{ $p->name }}" {{ $p->name === $strikerName ? 'selected' : '' }}>{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Select Non-Striker</label>
                <select name="non_striker_name" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($battingTeam->players as $p)
                        <option value="{{ $p->name }}" {{ $p->name === $nonStrikerName ? 'selected' : '' }}>{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Select Current Bowler</label>
                <select name="bowler_name" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($bowlingTeam->players as $p)
                        <option value="{{ $p->name }}" {{ $p->name === $bowlerName ? 'selected' : '' }}>{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2.5 justify-end pt-2">
                <button type="button" onclick="document.getElementById('changePlayersModal').style.display='none';" class="bg-transparent hover:bg-white/5 border border-[#30363d] text-gray-400 font-bold px-4 py-2 rounded-xl text-xs">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-black px-5 py-2 rounded-xl text-xs shadow-md">Save Players</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Start / Switch to 2nd Innings -->
<div id="switchInningsModal" style="display:none;" class="fixed inset-0 bg-black/80 z-50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h2 class="text-base sm:text-lg font-black text-white mb-2 flex items-center gap-2">
            <span>🔄</span> Start 2nd Innings
        </h2>
        <p class="text-xs text-gray-400 mb-4">
            1st Innings Score: <strong class="text-white">{{ $match->team1_score }}/{{ $match->team1_wickets }}</strong>. Target: <strong class="text-sky-400">{{ $match->team1_score + 1 }}</strong> runs.
        </p>
        
        <form method="POST" action="{{ isset($isLocal) && $isLocal ? route('local.switch-innings', $match->id) : route('admin.switch-innings', $match->id) }}" class="flex flex-col gap-3.5">
            @csrf
            <input type="hidden" name="innings" value="2">
            
            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">2nd Innings Striker ({{ $bowlingTeam->name }})</label>
                <select name="striker_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($bowlingTeam->players as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">2nd Innings Non-Striker</label>
                <select name="non_striker_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($bowlingTeam->players as $idx => $p)
                        <option value="{{ $p->id }}" {{ $idx == 1 ? 'selected' : '' }}>{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-gray-300 uppercase tracking-wider mb-1">Opening Bowler ({{ $battingTeam->name }})</label>
                <select name="bowler_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm font-bold focus:border-blue-500 outline-none">
                    @foreach($battingTeam->players as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->role }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2.5 justify-end pt-2">
                <button type="button" onclick="document.getElementById('switchInningsModal').style.display='none';" class="bg-transparent hover:bg-white/5 border border-[#30363d] text-gray-400 font-bold px-4 py-2 rounded-xl text-xs">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-5 py-2 rounded-xl text-xs shadow-md">Start 2nd Innings</button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedExtra = null;

function toggleExtra(extraType) {
    const indicator = document.getElementById('combination_indicator');
    const badge = document.getElementById('indicator_badge');
    const example = document.getElementById('indicator_example');
    const quickLabel = document.getElementById('quick_extra_label');
    const allExtraBtns = ['wide', 'no_ball', 'bye', 'leg_bye'];

    // Reset styles on all extra buttons
    allExtraBtns.forEach(type => {
        const btn = document.getElementById('btn_extra_' + type);
        if (btn) {
            btn.className = 'bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all';
        }
    });

    if (selectedExtra === extraType) {
        // Toggle OFF if clicked again
        selectedExtra = null;
        document.getElementById('input_extras').value = '';
        if (indicator) indicator.style.display = 'none';
    } else {
        // Toggle ON
        selectedExtra = extraType;
        document.getElementById('input_extras').value = extraType;
        const btn = document.getElementById('btn_extra_' + extraType);
        if (btn) {
            btn.className = 'bg-emerald-600 border-2 border-emerald-300 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-black text-white shadow-lg shadow-emerald-500/40 cursor-pointer transition-all';
        }

        const label = extraType === 'wide' ? 'Wide' : (extraType === 'no_ball' ? 'No Ball' : (extraType === 'bye' ? 'Bye' : 'Leg Bye'));
        if (indicator) indicator.style.display = 'block';
        if (badge) badge.innerText = label;
        if (example) example.innerText = label + ' + 4';
        if (quickLabel) quickLabel.innerText = 'Record ' + label + (extraType === 'bye' || extraType === 'leg_bye' ? ' (1 Run)' : ' (+1 Run)');
    }
}

function clearExtraSelection() {
    selectedExtra = null;
    document.getElementById('input_extras').value = '';
    const indicator = document.getElementById('combination_indicator');
    if (indicator) indicator.style.display = 'none';
    const allExtraBtns = ['wide', 'no_ball', 'bye', 'leg_bye'];
    allExtraBtns.forEach(type => {
        const btn = document.getElementById('btn_extra_' + type);
        if (btn) {
            btn.className = 'bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-xl py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-emerald-400 cursor-pointer transition-all';
        }
    });
}

function handleRunClick(runVal) {
    const form = document.getElementById('ballDeliveryForm');
    document.getElementById('input_runs').value = runVal;
    
    if (selectedExtra) {
        document.getElementById('input_extras').value = selectedExtra;
    } else {
        document.getElementById('input_extras').value = '';
    }

    form.submit();
}

function submitQuickExtraOnly() {
    if (!selectedExtra) return;
    const form = document.getElementById('ballDeliveryForm');
    document.getElementById('input_runs').value = (selectedExtra === 'bye' || selectedExtra === 'leg_bye') ? 1 : 0;
    document.getElementById('input_extras').value = selectedExtra;
    form.submit();
}

function openWicketModal() {
    document.getElementById('wicketModal').style.display = 'flex';
}

function filterBallsTimeline(type, btn) {
    document.querySelectorAll('.timeline-tab-btn').forEach(b => {
        b.className = 'timeline-tab-btn px-3 py-1 bg-[#161b22] text-gray-300 rounded-lg text-xs font-bold border border-[#30363d]';
    });
    btn.className = 'timeline-tab-btn active px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold border border-blue-500';

    const items = document.querySelectorAll('.timeline-ball-item');
    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'flex';
        } else if (item.getAttribute('data-inn') === type) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endsection
