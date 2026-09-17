@extends('layouts.app')

@section('content')
<main class="container mx-auto px-4 sm:px-6 py-6 sm:py-10 pb-24 font-['Inter',sans-serif]">

    <!-- Header -->
    <div class="text-center mb-8">
        <span class="text-xs font-black tracking-widest text-sky-400 uppercase block mb-1.5">
            CRICKET DIRECTORY
        </span>
        <h1 class="text-2xl sm:text-3xl font-black text-white uppercase tracking-tight m-0 mb-2">
            All Popular Players
        </h1>
        <p class="text-xs sm:text-sm text-gray-400 max-w-xl mx-auto m-0">
            Explore cricket players, batting &amp; bowling profiles, and head-to-head career stats.
        </p>
    </div>

    <!-- Search & Filter Bar (Fully Mobile Optimized) -->
    <div class="rounded-2xl p-4 sm:p-5 mb-8 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <form method="GET" action="{{ route('players') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            
            <!-- Search by Name / Keyword Input -->
            <div class="sm:col-span-6">
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">
                    Search Player
                </label>
                <div class="relative">
                    <input type="text" name="search" id="player-search-input" value="{{ $search ?? '' }}" 
                        placeholder="🔍 Type player name, role, country..." 
                        oninput="filterPlayersLive(this.value)"
                        class="w-full rounded-xl px-3.5 py-2.5 text-xs sm:text-sm outline-none transition-all" 
                        style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>

            <!-- Filter by Team Dropdown (Deduplicated) -->
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">
                    Filter By Team
                </label>
                <select name="team" id="player-team-select" onchange="this.form.submit()" 
                    class="w-full rounded-xl px-3 py-2.5 text-xs sm:text-sm outline-none cursor-pointer" 
                    style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                    <option value="" class="bg-[#161b22] text-gray-400">All Teams (Select...)</option>
                    @foreach($teams as $tm)
                        <option value="{{ $tm->id }}" class="bg-[#161b22] text-white" {{ ($teamId ?? request('team')) == $tm->id ? 'selected' : '' }}>
                            🛡️ {{ $tm->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <span>Search</span>
                </button>
                @if(!empty($search) || !empty($teamId) || request('team'))
                    <a href="{{ route('players') }}" class="font-bold text-xs sm:text-sm py-2.5 px-3 rounded-xl transition-all" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-dim);" title="Reset Filters">
                        ✕
                    </a>
                @endif
            </div>

        </form>

        <!-- Showing Players Count Badge -->
        <div class="mt-3 pt-3 flex items-center justify-between text-xs text-gray-400 border-t" style="border-color: var(--border-color);">
            <div>
                Showing <strong id="visible-player-count" class="text-white">{{ $allPlayers->count() }}</strong> of {{ $allPlayers->count() }} players
            </div>
            @if(!empty($search) || !empty($teamId) || request('team'))
                <span class="text-[11px] text-sky-400 font-semibold">Filtered results</span>
            @endif
        </div>
    </div>

    <!-- Players Grid -->
    <div id="players-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
        @forelse($allPlayers as $player)
            <div class="player-card rounded-2xl p-4 sm:p-5 text-center flex flex-col justify-between transition-all duration-200 shadow-md relative group"
                data-name="{{ strtolower($player->name) }}"
                data-role="{{ strtolower($player->role ?? '') }}"
                data-team="{{ strtolower($player->team?->name ?? '') }}"
                data-country="{{ strtolower($player->country ?? '') }}"
                style="background: var(--bg-card); border: 1px solid var(--border-color);"
                onmouseover="this.style.borderColor='rgba(56, 189, 248, 0.4)'; this.style.transform='translateY(-2px)';"
                onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateY(0)';"
            >
                <div>
                    <!-- Avatar or Photo -->
                    <a href="{{ route('player.profile', $player->id) }}" class="block mx-auto mb-3 w-16 h-16 no-underline">
                        @if(!empty($player->profile_image))
                            <img src="{{ $player->profile_image }}" alt="{{ $player->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-sky-400 block mx-auto shadow-md" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-16 h-16 rounded-full border border-sky-400/40 hidden items-center justify-center font-black text-sky-400 text-lg mx-auto" style="background: var(--bg-card-secondary);">
                                {{ strtoupper(substr($player->name, 0, 2)) }}
                            </div>
                        @else
                            <div class="w-16 h-16 rounded-full border border-sky-400/40 flex items-center justify-center font-black text-sky-400 text-lg mx-auto" style="background: var(--bg-card-secondary);">
                                {{ strtoupper(substr($player->name, 0, 2)) }}
                            </div>
                        @endif
                    </a>

                    <!-- Player Name -->
                    <h3 class="font-extrabold text-base text-white mb-1 tracking-tight group-hover:text-sky-400 transition-colors truncate">
                        <a href="{{ route('player.profile', $player->id) }}" class="text-inherit no-underline">
                            {{ $player->name }}
                        </a>
                    </h3>

                    <!-- Role & Team Badges -->
                    <div class="flex items-center justify-center gap-1.5 flex-wrap mb-3">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-400 border border-sky-500/30">
                            {{ $player->role ?? 'Player' }}
                        </span>
                        @if($player->team)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md text-gray-300 truncate max-w-[130px]" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color);" title="{{ $player->team->name }}">
                                🛡️ {{ $player->team->short_name ?? $player->team->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Batting & Bowling Specs -->
                    <div class="rounded-xl p-2.5 text-xs text-left mb-3.5 space-y-1" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color);">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">🏏 Batting:</span>
                            <strong class="text-gray-200 text-[11px] truncate max-w-[120px]">{{ $player->batting_style ?? 'Right-hand' }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">🎯 Bowling:</span>
                            <strong class="text-gray-200 text-[11px] truncate max-w-[120px]">{{ $player->bowling_style ?? 'Right-arm' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Profile Action Button -->
                <div class="pt-2 border-t" style="border-color: var(--border-color);">
                    <a href="{{ route('player.profile', $player->id) }}" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-sm text-decoration-none">
                        <span>View Profile &rarr;</span>
                    </a>
                </div>

            </div>
        @empty
            <div class="rounded-2xl p-8 sm:p-12 text-center col-span-full shadow-sm my-4" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="text-4xl mb-3">🏏</div>
                <h3 class="text-lg font-black text-white mb-2">No players found</h3>
                <p class="text-xs sm:text-sm text-gray-400 max-w-sm mx-auto mb-4">No players match your search filter. Try clearing the search or choosing another team.</p>
                <a href="{{ route('players') }}" class="inline-flex items-center gap-2 bg-blue-600/20 text-blue-400 border border-blue-500/40 font-bold text-xs px-4 py-2 rounded-xl">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>

</main>

<script>
    // Live instant search as user types
    function filterPlayersLive(val) {
        val = val.trim().toLowerCase();
        const cards = document.querySelectorAll('.player-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const role = card.getAttribute('data-role') || '';
            const team = card.getAttribute('data-team') || '';
            const country = card.getAttribute('data-country') || '';

            if (val === '' || name.includes(val) || role.includes(val) || team.includes(val) || country.includes(val)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countElem = document.getElementById('visible-player-count');
        if (countElem) {
            countElem.textContent = visibleCount;
        }
    }
</script>
@endsection
