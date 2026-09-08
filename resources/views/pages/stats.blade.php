@extends('layouts.app')

@section('content')
<main class="container py-6 sm:py-10">
    <div class="section-header mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight">STATISTICS & RANKINGS</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Team rankings, batting & bowling leaderboards</p>
        </div>
    </div>

    <!-- Team Rankings Table -->
    <div class="mb-8">
        <h2 class="text-base sm:text-lg font-bold text-white mb-3.5 uppercase flex items-center gap-2">
            <span>🏆</span> Global Team Standings
        </h2>
        <div class="table-responsive-wrapper">
            <table class="cricket-table min-w-[540px]">
                <thead>
                    <tr>
                        <th class="rank-col">RANK</th>
                        <th class="team-col">TEAM</th>
                        <th>MAT</th>
                        <th>WON</th>
                        <th>NRR</th>
                        <th class="pts-col">POINTS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamRankings as $tr)
                        <tr>
                            <td class="rank-col">{{ $tr->rank_num }}</td>
                            <td class="team-col"><strong>{{ $tr->team_name }}</strong></td>
                            <td>{{ $tr->matches_played }}</td>
                            <td>{{ $tr->won }}</td>
                            <td>{{ $tr->nrr }}</td>
                            <td class="pts-col">{{ $tr->points }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Player Rankings Dual Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="ranking-box bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5">
            <div class="ranking-box-header text-xs sm:text-sm font-extrabold text-white uppercase tracking-wider mb-4 border-b border-[#30363d] pb-2.5">
                BATTING &mdash; MOST RUNS
            </div>
            <div class="flex flex-col gap-2">
                @foreach($battingRankings as $br)
                    <div class="ranking-item flex justify-between items-center py-2 border-b border-white/5 text-xs sm:text-sm">
                        <div class="ranking-item-left flex items-center gap-2.5">
                            <span class="rank-num font-black text-gray-400 w-5">{{ $br->rank_num }}</span>
                            <div class="player-badge text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">{{ $br->badge_text }}</div>
                            <span class="player-name font-semibold text-white">{{ $br->player_name }}</span>
                        </div>
                        <span class="stat-val font-black text-white">{{ $br->stat_value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="ranking-box bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-5">
            <div class="ranking-box-header text-xs sm:text-sm font-extrabold text-white uppercase tracking-wider mb-4 border-b border-[#30363d] pb-2.5">
                BOWLING &mdash; MOST WICKETS
            </div>
            <div class="flex flex-col gap-2">
                @foreach($bowlingRankings as $bow)
                    <div class="ranking-item flex justify-between items-center py-2 border-b border-white/5 text-xs sm:text-sm">
                        <div class="ranking-item-left flex items-center gap-2.5">
                            <span class="rank-num font-black text-gray-400 w-5">{{ $bow->rank_num }}</span>
                            <div class="player-badge text-xs font-bold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded">{{ $bow->badge_text }}</div>
                            <span class="player-name font-semibold text-white">{{ $bow->player_name }}</span>
                        </div>
                        <span class="stat-val font-black text-white">{{ $bow->stat_value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</main>
@endsection
