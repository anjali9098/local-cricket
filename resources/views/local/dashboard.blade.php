@extends('layouts.local')

@section('content')
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10 pb-28">

    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-blue-600/20 text-blue-400 border border-blue-500/40 text-[11px] font-black px-2.5 py-0.5 rounded uppercase tracking-wider">LOCAL CRICKET</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight uppercase m-0" style="color: var(--text-main);">LOCAL CRICKET PORTAL</h1>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-dim);">Manage your own tournaments, schedule matches, search by city/state & explore grassroots cricket</p>
        </div>

        <button type="button" onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm px-5 py-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-500/20 flex-shrink-0">
            <span class="text-base font-black">+</span>
            <span>Create Tournament</span>
        </button>
    </div>

    <!-- Stats Cards (For My Tournaments) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4 mb-6">
        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY UPCOMING / DRAFTS</div>
            <div class="text-2xl sm:text-3xl font-black" style="color: var(--text-main);">{{ $upcomingCount }}</div>
        </div>

        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY ONGOING / LIVE</div>
            <div class="text-2xl sm:text-3xl font-black flex items-center gap-2" style="color: var(--text-main);">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                <span>{{ $ongoingCount }}</span>
            </div>
        </div>

        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY COMPLETED</div>
            <div class="text-2xl sm:text-3xl font-black" style="color: #38bdf8;">{{ $completedCount }}</div>
        </div>
    </div>

    <!-- CITY / STATE & KEYWORD SEARCH FILTER BAR -->
    <div class="rounded-2xl p-4 sm:p-5 mb-6 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <form method="GET" action="{{ route('local.dashboard') }}" onsubmit="return prepareSearchFilterSubmit(this)" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <input type="hidden" name="tab" id="active-tab-input" value="{{ $activeTab ?? 'matches' }}">

            <!-- Search Tournament / Team / Match Dropdown -->
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Search Tournament / Team / Match</label>
                <select name="search" id="search-select" onchange="toggleCustomFilter(this, 'custom-search-container', 'custom-search-input')" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                    <option value="" class="bg-[#161b22] text-gray-400">All Tournaments & Teams (Select...)</option>
                    @if(isset($availableTournaments) && $availableTournaments->isNotEmpty())
                        <optgroup label="🏆 TOURNAMENTS" class="bg-[#0d1117] text-sky-400 font-bold">
                            @foreach($availableTournaments as $tName)
                                <option value="{{ $tName }}" class="bg-[#161b22] text-white" {{ ($search ?? '') === $tName ? 'selected' : '' }}>
                                    🏆 {{ $tName }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(isset($availableTeams) && $availableTeams->isNotEmpty())
                        <optgroup label="🛡️ TEAMS" class="bg-[#0d1117] text-amber-400 font-bold">
                            @foreach($availableTeams as $tmName)
                                <option value="{{ $tmName }}" class="bg-[#161b22] text-white" {{ ($search ?? '') === $tmName ? 'selected' : '' }}>
                                    🛡️ {{ $tmName }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(isset($availableMatches) && $availableMatches->isNotEmpty())
                        <optgroup label="⚔️ MATCHES" class="bg-[#0d1117] text-rose-400 font-bold">
                            @foreach($availableMatches as $mName)
                                <option value="{{ $mName }}" class="bg-[#161b22] text-white" {{ ($search ?? '') === $mName ? 'selected' : '' }}>
                                    ⚔️ {{ $mName }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold" {{ (!empty($search) && (!isset($availableTournaments) || !$availableTournaments->contains($search)) && (!isset($availableTeams) || !$availableTeams->contains($search)) && (!isset($availableMatches) || !$availableMatches->contains($search))) ? 'selected' : '' }}>
                        ➕ Other (Type Custom Keyword...)
                    </option>
                </select>
                <div id="custom-search-container" style="{{ (!empty($search) && (!isset($availableTournaments) || !$availableTournaments->contains($search)) && (!isset($availableTeams) || !$availableTeams->contains($search)) && (!isset($availableMatches) || !$availableMatches->contains($search))) ? 'display: block;' : 'display: none;' }}" class="mt-2">
                    <input type="text" name="custom_search" id="custom-search-input" value="{{ (!empty($search) && (!isset($availableTournaments) || !$availableTournaments->contains($search)) && (!isset($availableTeams) || !$availableTeams->contains($search)) && (!isset($availableMatches) || !$availableMatches->contains($search))) ? $search : '' }}" placeholder="Type tournament / team / match name..." class="w-full rounded-xl px-3 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>

            <!-- Filter City Dropdown -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">City (e.g. Indore)</label>
                <select name="city" id="city-select" onchange="toggleCustomFilter(this, 'custom-city-container', 'custom-city-input')" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                    <option value="" class="bg-[#161b22] text-gray-400">All Cities (Select...)</option>
                    @foreach($availableCities as $c)
                        <option value="{{ $c }}" class="bg-[#161b22] text-white" {{ ($filterCity ?? '') === $c ? 'selected' : '' }}>
                            📍 {{ $c }}
                        </option>
                    @endforeach
                    <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold" {{ (!empty($filterCity) && !$availableCities->contains($filterCity)) ? 'selected' : '' }}>
                        ➕ Other (Type Custom City...)
                    </option>
                </select>
                <div id="custom-city-container" style="{{ (!empty($filterCity) && !$availableCities->contains($filterCity)) ? 'display: block;' : 'display: none;' }}" class="mt-2">
                    <input type="text" name="custom_city" id="custom-city-input" value="{{ (!empty($filterCity) && !$availableCities->contains($filterCity)) ? $filterCity : '' }}" placeholder="Type custom city name..." class="w-full rounded-xl px-3 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>

            <!-- Filter State Dropdown -->
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">State (e.g. MP)</label>
                <select name="state" id="state-select" onchange="toggleCustomFilter(this, 'custom-state-container', 'custom-state-input')" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                    <option value="" class="bg-[#161b22] text-gray-400">All States (Select...)</option>
                    @foreach($availableStates as $s)
                        <option value="{{ $s }}" class="bg-[#161b22] text-white" {{ ($filterState ?? '') === $s ? 'selected' : '' }}>
                            🏛️ {{ $s }}
                        </option>
                    @endforeach
                    <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold" {{ (!empty($filterState) && !$availableStates->contains($filterState)) ? 'selected' : '' }}>
                        ➕ Other (Type Custom State...)
                    </option>
                </select>
                <div id="custom-state-container" style="{{ (!empty($filterState) && !$availableStates->contains($filterState)) ? 'display: block;' : 'display: none;' }}" class="mt-2">
                    <input type="text" name="custom_state" id="custom-state-input" value="{{ (!empty($filterState) && !$availableStates->contains($filterState)) ? $filterState : '' }}" placeholder="Type custom state name..." class="w-full rounded-xl px-3 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>

            <!-- Search Action Buttons -->
            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Search</span>
                </button>
                @if(!empty($filterCity) || !empty($filterState) || !empty($search))
                    <a href="{{ route('local.dashboard') }}" class="font-bold text-xs sm:text-sm py-2.5 px-3 rounded-xl transition-all" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-dim);" title="Clear all filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TAB HEADERS -->
    <div class="flex items-center gap-2 mb-6 border-b pb-3 overflow-x-auto scrollbar-none" style="border-color: var(--border-color);">
        <button type="button" onclick="switchDashboardTab('matches')" id="tab-btn-matches" class="dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 bg-blue-600 text-white shadow-sm">
            <span>📅</span>
            <span>SCHEDULED MATCHES</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20">{{ $scheduledMatches->count() }}</span>
        </button>

        <button type="button" onclick="switchDashboardTab('my-tournaments')" id="tab-btn-my-tournaments" class="dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 text-gray-400 hover:text-white" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <span>🏏</span>
            <span>MY TOURNAMENTS</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-[#161b22] text-gray-300">{{ $myTournaments->count() }}</span>
        </button>

        <button type="button" onclick="switchDashboardTab('community')" id="tab-btn-community" class="dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 text-gray-400 hover:text-white" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <span>🌐</span>
            <span>COMMUNITY TOURNAMENTS</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-[#161b22] text-gray-300">{{ $otherTournaments->count() }}</span>
        </button>
    </div>

    <!-- TAB 1: SCHEDULED LOCAL MATCHES -->
    <div id="dashboard-tab-matches" class="dashboard-tab-content">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-wider m-0" style="color: var(--text-main);">SCHEDULED LOCAL MATCHES</h2>
                <p class="text-xs mt-0.5" style="color: var(--text-dim);">Upcoming and scheduled grassroots cricket matches across all cities</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-600/15 text-blue-400 border border-blue-500/30">
                {{ $scheduledMatches->count() }} Matches
            </span>
        </div>

        @if($scheduledMatches->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach($scheduledMatches as $m)
                    @php
                        $t = $m->tournament;
                        $venueName = $m->venue?->name ?? (is_string($m->venue) ? $m->venue : null);
                        if (!$venueName && !empty($m->custom_note) && !in_array($m->custom_note, ['Match Scheduled', 'Match in progress'])) {
                            $venueName = $m->custom_note;
                        }
                        if (!$venueName && $t && !empty($t->venue)) {
                            $venueName = $t->venue;
                        }
                        $cityState = ($t?->city ? $t->city : '') . ($t?->state ? ', ' . $t->state : '');
                        $fullAddress = trim($venueName . ($cityState ? ' • ' . $cityState : ''));
                        if (empty($fullAddress)) {
                            $fullAddress = 'Local Cricket Ground, ' . ($t?->city ?? 'Local');
                        }
                        $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($fullAddress);
                    @endphp

                    <div class="rounded-2xl p-4 sm:p-5 flex flex-col justify-between transition-all shadow-md hover:border-blue-500/50" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                        <div>
                            <!-- Card Header: LOCAL Badge + Status -->
                            <div class="flex items-center justify-between gap-2 mb-3 pb-2.5" style="border-bottom: 1px solid var(--border-color);">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-blue-600 text-white font-black text-[10px] px-2 py-0.5 rounded tracking-widest uppercase shadow-sm">
                                        LOCAL
                                    </span>
                                    @if($t?->city)
                                        <span class="text-[11px] font-semibold text-gray-400">
                                            📍 {{ $t->city }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if($m->status === 'live')
                                        <span class="bg-red-500/15 border border-red-500/30 text-red-500 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase animate-pulse">⚡ Live</span>
                                    @else
                                        <span class="bg-amber-500/15 border border-amber-500/30 text-amber-400 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">⏳ Scheduled</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Teams Row -->
                            <div class="flex flex-col gap-2.5 my-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-black text-xs text-white flex-shrink-0" style="background: {{ $m->team1?->color_code ?? '#2563eb' }};">
                                            {{ $m->team1?->short_name ?? ($m->team1?->name ? strtoupper(substr($m->team1->name, 0, 3)) : 'T1') }}
                                        </div>
                                        <span class="font-extrabold text-sm sm:text-base truncate" style="color: var(--text-main);">
                                            {{ $m->team1?->name ?? 'Team 1' }}
                                        </span>
                                    </div>
                                    <div class="text-xs font-bold text-gray-400">
                                        {{ $m->team1_score > 0 ? $m->team1_score . '/' . $m->team1_wickets : '' }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-black text-xs text-white flex-shrink-0" style="background: {{ $m->team2?->color_code ?? '#0284c7' }};">
                                            {{ $m->team2?->short_name ?? ($m->team2?->name ? strtoupper(substr($m->team2->name, 0, 3)) : 'T2') }}
                                        </div>
                                        <span class="font-extrabold text-sm sm:text-base truncate" style="color: var(--text-main);">
                                            {{ $m->team2?->name ?? 'Team 2' }}
                                        </span>
                                    </div>
                                    <div class="text-xs font-bold text-gray-400">
                                        {{ $m->team2_score > 0 ? $m->team2_score . '/' . $m->team2_wickets : '' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Series Name with Hyperlink -->
                            <div class="mt-3 pt-2.5 flex items-center gap-1.5 text-xs" style="border-top: 1px solid var(--border-color);">
                                <span class="text-gray-400">🏆 Series:</span>
                                @if($t)
                                    <a href="{{ route('tournament.public', $t->id) }}" class="font-bold text-sky-400 hover:text-sky-300 hover:underline truncate" title="View Tournament Page">
                                        {{ $t->name }} ({{ $t->format ?? 'T20' }})
                                    </a>
                                @else
                                    <span class="font-semibold text-gray-300">Local Series</span>
                                @endif
                            </div>

                            <!-- Venue / Address with Hyperlink -->
                            <div class="mt-1.5 flex items-start gap-1.5 text-xs">
                                <span class="text-gray-400 flex-shrink-0">📍 Address:</span>
                                <a href="{{ $mapsUrl }}" target="_blank" class="font-semibold text-emerald-400 hover:text-emerald-300 hover:underline inline-flex items-center gap-1 truncate" title="Click to view address on Google Maps">
                                    <span class="truncate">{{ $fullAddress }}</span>
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </div>

                            <!-- Match Time -->
                            <div class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-400">
                                <span class="text-gray-400">🕒 Time:</span>
                                <span class="font-bold text-amber-300">
                                    {{ $m->match_date ? \Carbon\Carbon::parse($m->match_date)->format('D, d M Y • h:i A') : 'Time TBD' }} (IST)
                                </span>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-4 pt-3 flex items-center gap-2" style="border-top: 1px solid var(--border-color);">
                            @if($t && Auth::check() && (Auth::id() == $t->user_id || Auth::user()->role === 'superadmin'))
                                <a href="{{ route('local.manage-tournament', $t->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 px-3 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all shadow-sm">
                                    <span>⚙️ Manage / Score</span>
                                </a>
                            @endif
                            <a href="{{ route('local.match.detail', $m->id) }}" class="flex-1 font-bold text-xs py-2 px-3 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                                <span>View Match &rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl p-8 sm:p-12 text-center shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="text-4xl mb-3">📅</div>
                <h3 class="text-base sm:text-lg font-black mb-2" style="color: var(--text-main);">No scheduled matches found</h3>
                <p class="text-xs sm:text-sm mb-4 max-w-sm mx-auto" style="color: var(--text-dim);">No upcoming local matches match your city/state search filters. Try resetting the filters or schedule a match in your tournaments!</p>
                <a href="{{ route('local.dashboard') }}" class="inline-flex items-center gap-1.5 font-bold text-xs px-4 py-2 rounded-xl text-sky-400 bg-blue-500/10 border border-blue-500/30">
                    Clear Search Filters
                </a>
            </div>
        @endif
    </div>

    <!-- TAB 2: MY TOURNAMENTS (Full Management Control) -->
    <div id="dashboard-tab-my-tournaments" class="dashboard-tab-content" style="display: none;">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🏏</span>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-wider m-0" style="color: var(--text-main);">MY TOURNAMENTS</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: var(--bg-card-secondary); color: var(--text-dim); border: 1px solid var(--border-color);">{{ $myTournaments->count() }}</span>
            </div>
            <span class="text-[11px] font-semibold" style="color: var(--text-dim);">Full Management Access</span>
        </div>

        @if($myTournaments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($myTournaments as $t)
                    <div class="rounded-2xl p-4 sm:p-5 flex flex-col justify-between transition-all shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <h3 class="font-extrabold text-base sm:text-lg m-0 truncate" style="color: var(--text-main);">{{ $t->name }}</h3>
                                <div class="flex-shrink-0">
                                    @if(!$t->is_approved)
                                        <span class="bg-amber-500/15 border border-amber-500/30 text-amber-500 font-bold text-[10px] px-2 py-0.5 rounded-full">⏳ Pending Approval</span>
                                    @elseif($t->status == 'completed')
                                        <span class="bg-blue-500/15 border border-blue-500/30 text-sky-400 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">completed</span>
                                    @elseif($t->status == 'ongoing' || $t->status == 'live')
                                        <span class="bg-red-500/15 border border-red-500/30 text-red-500 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase animate-pulse">⚡ live</span>
                                    @else
                                        <span class="font-bold text-[10px] px-2 py-0.5 rounded-full uppercase" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-dim);">{{ $t->status }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 text-xs mb-2" style="color: var(--text-dim);">
                                <span style="color: #f43f5e;">📍</span> {{ $t->city ?? 'Local' }}{{ $t->state ? ', ' . $t->state : '' }}
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded-md font-bold text-[11px]" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">{{ $t->format }}</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->teams->count() }} Teams</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->matches->count() }} Matches</span>
                            </div>

                            @if($t->venue)
                                <div class="text-xs text-gray-400 truncate mb-2">
                                    <span>🏟️ {{ $t->venue }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-4 pt-3 flex flex-col gap-2" style="border-top: 1px solid var(--border-color);">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('local.manage-tournament', $t->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm py-2.5 px-3 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all shadow-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                    <span>Manage</span>
                                </a>
                                
                                <a href="{{ route('local.tournament.preview', $t->id) }}" class="font-bold text-xs sm:text-sm py-2.5 px-3.5 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <span>Preview</span>
                                </a>
                            </div>
                            
                            <div>
                                @if($t->delete_requested)
                                    <button disabled class="w-full bg-red-500/10 text-red-400 border border-red-500/30 font-bold text-xs py-1.5 px-2 rounded-lg cursor-not-allowed text-center">
                                        ⏳ Delete Pending Approval...
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('local.request-delete', $t->id) }}" onsubmit="return confirm('Are you sure you want to request deletion of tournament \'{{ addslashes($t->name) }}\'? This will send a deletion request to Super Admin.');" class="m-0 w-full">
                                        @csrf
                                        <button type="submit" class="w-full bg-transparent hover:bg-red-500/10 text-gray-500 hover:text-red-400 border border-[#30363d] hover:border-red-500/40 font-bold text-xs py-1.5 px-2 rounded-lg transition-all text-center">
                                            🗑️ Request Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl p-8 sm:p-12 text-center shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <div class="text-4xl sm:text-5xl mb-3">🏏</div>
                <h3 class="text-base sm:text-lg font-black mb-2" style="color: var(--text-main);">No tournaments found</h3>
                <p class="text-xs sm:text-sm mb-6 max-w-sm mx-auto" style="color: var(--text-dim);">You haven't created any tournaments matching the current city/state search.</p>
                <button type="button" onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-all shadow-md">
                    + Create Tournament
                </button>
            </div>
        @endif
    </div>

    <!-- TAB 3: COMMUNITY & OTHER LOCAL TOURNAMENTS (View Only) -->
    <div id="dashboard-tab-community" class="dashboard-tab-content" style="display: none;">
        <div class="flex items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🌐</span>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-wider m-0" style="color: var(--text-main);">COMMUNITY TOURNAMENTS</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: var(--bg-card-secondary); color: var(--text-dim); border: 1px solid var(--border-color);">{{ $otherTournaments->count() }}</span>
            </div>
            <span class="text-[11px] font-semibold flex items-center gap-1" style="color: var(--text-dim);">
                <span>🔒</span> View Only
            </span>
        </div>

        @if($otherTournaments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($otherTournaments as $t)
                    <div class="rounded-2xl p-4 sm:p-5 flex flex-col justify-between transition-all shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="font-extrabold text-base sm:text-lg m-0 truncate" style="color: var(--text-main);">{{ $t->name }}</h3>
                                <div class="flex-shrink-0">
                                    @if($t->status == 'completed')
                                        <span class="bg-blue-500/15 border border-blue-500/30 text-sky-400 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">completed</span>
                                    @elseif($t->status == 'ongoing' || $t->status == 'live')
                                        <span class="bg-red-500/15 border border-red-500/30 text-red-500 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase animate-pulse">⚡ live</span>
                                    @else
                                        <span class="font-bold text-[10px] px-2 py-0.5 rounded-full uppercase" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-dim);">{{ $t->status }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Organizer info -->
                            <div class="text-[11px] font-semibold mb-2 flex items-center gap-1.5" style="color: var(--text-muted);">
                                <span>👤 Organizer:</span>
                                <strong style="color: var(--text-main);">{{ $t->user->name ?? 'Local Organizer' }}</strong>
                            </div>
                            
                            <div class="flex items-center gap-2 text-xs mb-2" style="color: var(--text-dim);">
                                <span style="color: #f43f5e;">📍</span> {{ $t->city ?? 'Local' }}{{ $t->state ? ', ' . $t->state : '' }}
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded-md font-bold text-[11px]" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">{{ $t->format }}</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->teams->count() }} Teams</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->matches->count() }} Matches</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-3 flex items-center justify-between gap-2" style="border-top: 1px solid var(--border-color);">
                            <a href="{{ route('local.tournament.preview', $t->id) }}" class="w-full font-bold text-xs sm:text-sm py-2.5 px-4 rounded-xl inline-flex items-center justify-center gap-2 transition-all hover:border-blue-500" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: #38bdf8;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <span>View Matches &amp; Points Table</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl p-8 sm:p-12 text-center shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <p class="text-xs sm:text-sm text-gray-400">No community tournaments found matching the filters.</p>
            </div>
        @endif
    </div>

</main>

<!-- Create Tournament Modal with Reusable Series, Venues, City & State Datalists -->
<div id="createModal" style="display:none;" class="fixed inset-0 bg-black/80 z-50 backdrop-blur-sm items-center justify-center p-4">
    <div class="rounded-2xl max-w-lg w-full max-h-[90vh] flex flex-col p-5 sm:p-6 shadow-2xl relative" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <div class="flex items-center justify-between pb-3 mb-4" style="border-bottom: 1px solid var(--border-color);">
            <h2 class="text-lg font-black uppercase tracking-tight m-0" style="color: var(--text-main);">Create Tournament</h2>
            <button type="button" onclick="closeCreateModal()" class="text-xl font-bold p-1" style="color: var(--text-dim); background:none; border:none; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('local.create-tournament') }}" onsubmit="return prepareModalSubmit(this)" class="overflow-y-auto pr-1 flex-1 flex flex-col gap-3.5">
            @csrf
            
            <!-- Series Name: Select Existing Template or Type New -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">
                    Tournament / Series Name * 
                    <span class="text-[10px] text-gray-500 font-normal lowercase">(pick existing template or type new)</span>
                </label>
                <input type="text" name="name" list="existing-series-modal-list" required placeholder="e.g. Premier Cricket League" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                <datalist id="existing-series-modal-list">
                    @foreach($existingSeriesTemplates as $st)
                        <option value="{{ $st->name }} ({{ $st->format ?? 'T20' }})">
                    @endforeach
                </datalist>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">City *</label>
                    <select name="city" id="modal-city-select" onchange="toggleCustomFilter(this, 'modal-city-custom-wrap', 'modal-city-custom-input')" required class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                        <option value="" class="bg-[#161b22] text-gray-400">Select City...</option>
                        @foreach($availableCities as $c)
                            <option value="{{ $c }}" class="bg-[#161b22] text-white">📍 {{ $c }}</option>
                        @endforeach
                        <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold">➕ Other (Type Custom City...)</option>
                    </select>
                    <div id="modal-city-custom-wrap" style="display: none;" class="mt-2">
                        <input type="text" name="custom_city" id="modal-city-custom-input" placeholder="Type city name..." class="w-full rounded-xl px-3.5 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">State</label>
                    <select name="state" id="modal-state-select" onchange="toggleCustomFilter(this, 'modal-state-custom-wrap', 'modal-state-custom-input')" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                        <option value="" class="bg-[#161b22] text-gray-400">Select State...</option>
                        @foreach($availableStates as $s)
                            <option value="{{ $s }}" class="bg-[#161b22] text-white">🏛️ {{ $s }}</option>
                        @endforeach
                        <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold">➕ Other (Type Custom State...)</option>
                    </select>
                    <div id="modal-state-custom-wrap" style="display: none;" class="mt-2">
                        <input type="text" name="custom_state" id="modal-state-custom-input" placeholder="Type state name..." class="w-full rounded-xl px-3.5 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                    </div>
                </div>
            </div>
            
            <!-- Venue / Address with Reusable Selection -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">
                    Venue / Ground Address
                </label>
                <select name="venue" id="modal-venue-select" onchange="toggleCustomFilter(this, 'modal-venue-custom-wrap', 'modal-venue-custom-input')" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none cursor-pointer" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 600;">
                    <option value="" class="bg-[#161b22] text-gray-400">Select Venue / Ground...</option>
                    @foreach($existingVenues as $ev)
                        <option value="{{ $ev->name }}{{ $ev->city ? ', ' . $ev->city : '' }}" class="bg-[#161b22] text-white">
                            📍 {{ $ev->name }}{{ $ev->city ? ', ' . $ev->city : '' }}
                        </option>
                    @endforeach
                    <option value="custom" class="bg-[#161b22] text-emerald-400 font-bold">➕ Other (Type Custom Ground...)</option>
                </select>
                <div id="modal-venue-custom-wrap" style="display: none;" class="mt-2">
                    <input type="text" name="custom_venue" id="modal-venue-custom-input" placeholder="Type custom ground or stadium name..." class="w-full rounded-xl px-3.5 py-2 text-xs outline-none" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Format *</label>
                    <select name="format" required class="w-full rounded-xl px-3 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                        <option value="T20">T20</option>
                        <option value="ODI">ODI</option>
                        <option value="Test">Test</option>
                        <option value="T10">T10</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Overs / Innings</label>
                    <input type="number" name="overs" value="20" required class="w-full rounded-xl px-3 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Type</label>
                    <select name="type" class="w-full rounded-xl px-3 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                        <option value="Knockout">Knockout</option>
                        <option value="League">League</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Start Date</label>
                    <input type="date" name="start_date" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">End Date</label>
                    <input type="date" name="end_date" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>
            
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Description</label>
                <textarea name="description" rows="2" placeholder="Rules, prizes or tournament overview..." class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none resize-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);"></textarea>
            </div>
            
            <div class="flex items-center justify-end gap-2.5 pt-3 mt-2" style="border-top: 1px solid var(--border-color);">
                <button type="button" onclick="closeCreateModal()" class="font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-dim);">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all shadow-md">Create Tournament</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.style.display = 'flex';
    }
    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.style.display = 'none';
    }

    function switchDashboardTab(tabName) {
        // Tab contents
        document.getElementById('dashboard-tab-matches').style.display = tabName === 'matches' ? 'block' : 'none';
        document.getElementById('dashboard-tab-my-tournaments').style.display = tabName === 'my-tournaments' ? 'block' : 'none';
        document.getElementById('dashboard-tab-community').style.display = tabName === 'community' ? 'block' : 'none';

        // Tab buttons
        const btnMatches = document.getElementById('tab-btn-matches');
        const btnMy = document.getElementById('tab-btn-my-tournaments');
        const btnComm = document.getElementById('tab-btn-community');

        btnMatches.className = 'dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 ' + 
            (tabName === 'matches' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-400 hover:text-white');
        btnMy.className = 'dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 ' + 
            (tabName === 'my-tournaments' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-400 hover:text-white');
        btnComm.className = 'dashboard-tab-btn px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all flex items-center gap-2 ' + 
            (tabName === 'community' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-400 hover:text-white');

        const tabInput = document.getElementById('active-tab-input');
        if (tabInput) {
            tabInput.value = tabName;
        }
    }

    function toggleCustomFilter(select, containerId, inputId) {
        const container = document.getElementById(containerId);
        const input = document.getElementById(inputId);
        if (select && select.value === 'custom') {
            if (container) container.style.display = 'block';
            if (input) {
                input.focus();
                input.required = true;
            }
        } else {
            if (container) container.style.display = 'none';
            if (input) {
                input.required = false;
                input.value = '';
            }
        }
    }

    function prepareSearchFilterSubmit(form) {
        const searchSelect = document.getElementById('search-select');
        const customSearch = document.getElementById('custom-search-input');
        if (searchSelect && searchSelect.value === 'custom') {
            if (customSearch && customSearch.value.trim() !== '') {
                searchSelect.name = '';
                customSearch.name = 'search';
            }
        }

        const citySelect = document.getElementById('city-select');
        const customCity = document.getElementById('custom-city-input');
        if (citySelect && citySelect.value === 'custom') {
            if (customCity && customCity.value.trim() !== '') {
                citySelect.name = '';
                customCity.name = 'city';
            }
        }

        const stateSelect = document.getElementById('state-select');
        const customState = document.getElementById('custom-state-input');
        if (stateSelect && stateSelect.value === 'custom') {
            if (customState && customState.value.trim() !== '') {
                stateSelect.name = '';
                customState.name = 'state';
            }
        }
        return true;
    }

    function prepareModalSubmit(form) {
        const citySelect = document.getElementById('modal-city-select');
        const customCity = document.getElementById('modal-city-custom-input');
        if (citySelect && citySelect.value === 'custom') {
            if (customCity && customCity.value.trim() !== '') {
                citySelect.name = '';
                customCity.name = 'city';
            }
        }

        const stateSelect = document.getElementById('modal-state-select');
        const customState = document.getElementById('modal-state-custom-input');
        if (stateSelect && stateSelect.value === 'custom') {
            if (customState && customState.value.trim() !== '') {
                stateSelect.name = '';
                customState.name = 'state';
            }
        }

        const venueSelect = document.getElementById('modal-venue-select');
        const customVenue = document.getElementById('modal-venue-custom-input');
        if (venueSelect && venueSelect.value === 'custom') {
            if (customVenue && customVenue.value.trim() !== '') {
                venueSelect.name = '';
                customVenue.name = 'venue';
            }
        }
        return true;
    }

    // Set initial tab from server parameter
    document.addEventListener('DOMContentLoaded', function() {
        const initialTab = "{{ $activeTab ?? 'matches' }}";
        switchDashboardTab(initialTab);
    });
</script>
@endsection
