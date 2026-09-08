@extends('layouts.app')

@section('content')
<main class="container py-6 sm:py-10">
    <div class="section-header mb-6">
        <div class="section-title text-xl sm:text-2xl flex items-center gap-3">
            <span>LIVE MATCHES NOW</span>
            <span class="badge-live">LIVE</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($liveMatches as $m)
            <a href="{{ route('matches.detail', $m->id) }}" class="match-card p-4 sm:p-5 no-underline block">
                <div class="match-card-header mb-3 flex items-center justify-between">
                    <span class="truncate max-w-[200px]">{{ $m->level_type ?? ($m->match_type . ' - ' . ($m->venue->name ?? 'WANKHEDE')) }}</span>
                    <span class="badge-live">LIVE</span>
                </div>

                <div class="match-card-teams flex flex-col gap-3 my-3">
                    <div class="team-row flex items-center justify-between gap-3">
                        <div class="team-info flex items-center gap-2">
                            <div class="team-avatar w-7 h-7 sm:w-8 sm:h-8 text-xs font-black flex items-center justify-center rounded-full" style="border-color: {{ $m->team1?->color_code ?? 'var(--primary)' }};">
                                {{ $m->team1?->short_name ?? ($m->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team1->name), 0, 3)) : '') }}
                            </div>
                            <span class="team-name text-sm sm:text-base font-semibold text-white">{{ $m->team1?->name ?? '' }}</span>
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
                            <span class="team-name text-sm sm:text-base font-semibold text-white">{{ $m->team2?->name ?? '' }}</span>
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
                <h3 class="text-lg font-bold text-white">No Live Matches Right Now</h3>
                <p class="text-sm text-gray-400 mt-1">Check scheduled matches to see upcoming games.</p>
                <a href="{{ route('matches') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white font-bold rounded-lg text-xs">View Schedule</a>
            </div>
        @endforelse
    </div>
</main>
@endsection
