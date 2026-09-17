@extends('layouts.app')

@section('content')
<main class="container py-6 sm:py-10">
    <!-- Header & Category Badges -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight">MATCH SCHEDULE &amp; RESULTS</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">All international, domestic and local cricket matches</p>
        </div>
        
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
            <a href="{{ route('matches') }}" class="tag-badge {{ empty($status) && empty($category) ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">All</a>
            <a href="{{ route('matches', ['status' => 'live']) }}" class="tag-badge {{ ($status ?? '') === 'live' ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Live</a>
            <a href="{{ route('matches', ['status' => 'upcoming']) }}" class="tag-badge {{ in_array(($status ?? ''), ['upcoming', 'scheduled']) ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Upcoming</a>
            <a href="{{ route('matches', ['status' => 'completed']) }}" class="tag-badge {{ ($status ?? '') === 'completed' ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full">Completed</a>
            <a href="{{ route('matches', ['category' => 'local']) }}" class="tag-badge {{ ($category ?? '') === 'local' ? 'primary' : '' }} whitespace-nowrap px-3.5 py-1.5 text-xs font-bold rounded-full bg-blue-600/20 text-blue-400 border border-blue-500/40">🏏 Local Matches</a>
        </div>
    </div>

    <!-- Match Search Bar (Mobile & Desktop) -->
    <div class="rounded-2xl p-3 sm:p-4 mb-6 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <form method="GET" action="{{ route('matches') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if(!empty($category))
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="🔍 Search match by team name, tournament, ground..." class="w-full rounded-xl px-3.5 py-2 text-xs sm:text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm py-2 px-4 rounded-xl transition-all shadow-md">
                    Search
                </button>
                @if(!empty($search))
                    <a href="{{ route('matches', array_filter(['status' => $status, 'category' => $category])) }}" class="font-bold text-xs py-2 px-3 rounded-xl transition-all" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-dim);" title="Clear Search">
                        ✕ Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($matches as $m)
            @php
                $isLocal = ($m->level_type === 'LOCAL') || ($m->tournament?->category === 'local');
                $venueText = $m->venue?->name ?? (is_string($m->venue) ? $m->venue : null);
                if (!$venueText && !empty($m->custom_note) && !in_array($m->custom_note, ['Match Scheduled', 'Match in progress'])) {
                    $venueText = $m->custom_note;
                }
                if (!$venueText && $m->tournament?->venue) {
                    $venueText = $m->tournament->venue;
                }
                $city = $m->tournament?->city ?? $m->venue?->city ?? '';
                $addressQuery = trim($venueText . ($city ? ', ' . $city : ''));
            @endphp
            <div class="match-card p-4 sm:p-5 no-underline block relative">
                <div class="match-card-header flex items-center justify-between mb-3 text-xs uppercase font-bold text-gray-400">
                    <div class="flex items-center gap-1.5 truncate max-w-[200px]">
                        @if($isLocal)
                            <span class="bg-blue-600 text-white font-black text-[10px] px-1.5 py-0.5 rounded tracking-widest uppercase flex-shrink-0">LOCAL</span>
                        @endif
                        @if($m->tournament)
                            <a href="{{ route('tournament.public', $m->tournament->id) }}" class="text-sky-400 hover:underline truncate" title="{{ $m->tournament->name }}">
                                {{ $m->tournament->short_name ?? Str::limit($m->tournament->name, 16) }}
                            </a>
                        @else
                            <span class="truncate">{{ $m->level_type ?? $m->match_type }}</span>
                        @endif
                    </div>
                    @if($m->status === 'live')
                        <span class="badge-live">LIVE</span>
                    @else
                        <span class="tag-badge text-[10px] px-2 py-0.5">{{ strtoupper($m->status) }}</span>
                    @endif
                </div>

                <a href="{{ route('matches.detail', $m->id) }}" class="block no-underline">
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
                </a>

                <div class="match-card-footer pt-3 flex items-center justify-between text-xs text-gray-400 border-t border-white/5 gap-2">
                    @if($addressQuery)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($addressQuery) }}" target="_blank" class="truncate max-w-[170px] text-gray-400 hover:text-sky-300 hover:underline" title="View address: {{ $addressQuery }}">
                            📍 {{ $venueText ?? 'Venue TBA' }}
                        </a>
                    @else
                        <span class="truncate max-w-[150px]">📍 Venue TBA</span>
                    @endif

                    @if($m->status === 'completed')
                        <span class="text-emerald-400 font-bold truncate max-w-[140px]">🏆 {{ $m->winning_title ?? 'Completed' }}</span>
                    @elseif($m->match_date)
                        <span class="truncate max-w-[140px] text-amber-300/90 font-semibold" title="{{ \Carbon\Carbon::parse($m->match_date)->format('d M Y, h:i A') }}">
                            🕒 {{ \Carbon\Carbon::parse($m->match_date)->format('d M, h:i A') }}
                        </span>
                    @else
                        <span class="truncate max-w-[140px]">{{ $m->custom_note ?? 'Match scheduled' }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-10 text-center col-span-full">
                <p class="text-gray-400">No matches found matching the criteria.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
