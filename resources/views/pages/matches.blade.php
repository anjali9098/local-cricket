@extends('layouts.app')

@section('content')
<main class="container py-6 sm:py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight">MATCH SCHEDULE & RESULTS</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">All international, domestic and local matches</p>
        </div>
        
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
            <a href="{{ route('matches') }}" class="tag-badge {{ empty($status) ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">All</a>
            <a href="{{ route('matches', ['status' => 'live']) }}" class="tag-badge {{ $status === 'live' ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Live</a>
            <a href="{{ route('matches', ['status' => 'upcoming']) }}" class="tag-badge {{ in_array($status, ['upcoming', 'scheduled']) ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Upcoming</a>
            <a href="{{ route('matches', ['status' => 'completed']) }}" class="tag-badge {{ $status === 'completed' ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Completed</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($matches as $m)
            <a href="{{ route('matches.detail', $m->id) }}" class="match-card p-4 sm:p-5 no-underline block">
                <div class="match-card-header flex items-center justify-between mb-3 text-xs uppercase font-bold text-gray-400">
                    <span class="truncate max-w-[200px]">{{ $m->level_type ?? $m->match_type }}</span>
                    @if($m->status === 'live')
                        <span class="badge-live">LIVE</span>
                    @else
                        <span class="tag-badge text-[10px] px-2 py-0.5">{{ strtoupper($m->status) }}</span>
                    @endif
                </div>

                <div class="match-card-teams flex flex-col gap-3 my-3">
                    <div class="team-row flex items-center justify-between gap-3">
                        <div class="team-info flex items-center gap-2">
                            <div class="team-avatar w-7 h-7 text-xs font-black flex items-center justify-center rounded-full" style="border-color: {{ $m->team1?->color_code ?? 'var(--primary)' }};">
                                {{ $m->team1?->short_name ?? ($m->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team1->name), 0, 3)) : '') }}
                            </div>
                            <span class="team-name text-sm sm:text-base font-semibold text-white truncate max-w-[160px]">{{ $m->team1?->name ?? '' }}</span>
                        </div>
                        <div class="team-score text-sm font-bold text-white">
                            @if($m->status === 'completed' || $m->status === 'live' || $m->team1_score > 0)
                                {{ $m->team1_score }}/{{ $m->team1_wickets }} <span class="text-xs text-gray-400">({{ $m->team1_overs }} ov)</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="team-row flex items-center justify-between gap-3">
                        <div class="team-info flex items-center gap-2">
                            <div class="team-avatar w-7 h-7 text-xs font-black flex items-center justify-center rounded-full" style="border-color: {{ $m->team2?->color_code ?? '#38bdf8' }};">
                                {{ $m->team2?->short_name ?? ($m->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team2->name), 0, 3)) : '') }}
                            </div>
                            <span class="team-name text-sm sm:text-base font-semibold text-white truncate max-w-[160px]">{{ $m->team2?->name ?? '' }}</span>
                        </div>
                        <div class="team-score text-sm font-bold text-white">
                            @if($m->status === 'completed' || $m->status === 'live' || $m->team2_score > 0)
                                {{ $m->team2_score }}/{{ $m->team2_wickets }} <span class="text-xs text-gray-400">({{ $m->team2_overs }} ov)</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <div class="match-card-footer pt-3 flex items-center justify-between text-xs text-gray-400 border-t border-white/5">
                    <span class="truncate max-w-[150px]">📍 {{ $m->venue->name ?? 'Venue TBA' }}</span>
                    @if($m->status === 'completed')
                        <span class="text-emerald-400 font-bold truncate max-w-[140px]">🏆 {{ $m->winning_title ?? 'Completed' }}</span>
                    @else
                        <span class="truncate max-w-[140px]">{{ $m->custom_note ?? 'Match scheduled' }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-10 text-center col-span-full">
                <p class="text-gray-400">No matches found matching the criteria.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
