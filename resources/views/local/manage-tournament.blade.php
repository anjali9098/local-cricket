@extends('layouts.local')

@section('content')
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10 pb-28">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-[#30363d]">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight uppercase m-0">
                {{ $tournament->name }}
            </h1>
            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs sm:text-sm">
                <span class="text-gray-400 font-semibold flex items-center gap-1">
                    <span class="text-rose-400">📍</span> {{ $tournament->city ?? 'Mumbai' }}
                </span>
                <span class="border border-[#30363d] bg-[#161b22] text-gray-300 px-2.5 py-0.5 rounded-full font-bold uppercase text-[11px] tracking-wide">
                    {{ $tournament->format }}
                </span>
                
                @if($tournament->status == 'completed')
                    <span class="bg-blue-500/15 border border-blue-500/30 text-sky-400 px-2.5 py-0.5 rounded-full font-bold uppercase text-[11px]">completed</span>
                @elseif($tournament->status == 'live')
                    <span class="bg-red-500/15 border border-red-500/30 text-red-400 px-2.5 py-0.5 rounded-full font-bold uppercase text-[11px] animate-pulse">⚡ live</span>
                @elseif($tournament->status == 'ongoing')
                    <span class="bg-amber-500/15 border border-amber-500/30 text-amber-400 px-2.5 py-0.5 rounded-full font-bold uppercase text-[11px]">ongoing</span>
                @else
                    <span class="bg-[#161b22] border border-[#30363d] text-gray-400 px-2.5 py-0.5 rounded-full font-bold uppercase text-[11px]">{{ $tournament->status }}</span>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap w-full sm:w-auto">
            <a href="{{ route('local.tournament.preview', $tournament->id) }}" class="bg-[#161b22] hover:bg-[#21262d] text-white border border-[#30363d] font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm flex-1 sm:flex-none">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                <span>Public Page</span>
            </a>
            
            @if($tournament->status == 'draft')
            <form method="POST" action="{{ route('local.publish-tournament', $tournament->id) }}" class="m-0 flex-1 sm:flex-none">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <span>🚀</span> Publish
                </button>
            </form>
            @elseif($tournament->status == 'published')
            <form method="POST" action="{{ route('local.mark-ongoing', $tournament->id) }}" class="m-0 flex-1 sm:flex-none">
                @csrf
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <span>⚡</span> Mark Ongoing
                </button>
            </form>
            @elseif($tournament->status == 'ongoing')
            <form method="POST" action="{{ route('local.mark-completed', $tournament->id) }}" class="m-0 flex-1 sm:flex-none">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <span>✓</span> Mark Completed
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="flex items-center gap-2 p-1.5 bg-[#0d1117] border border-[#30363d] rounded-xl mb-6 overflow-x-auto scrollbar-none w-full sm:w-auto">
        <button class="tab-btn active flex-1 sm:flex-none justify-center whitespace-nowrap px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold flex items-center gap-2 transition-all" onclick="switchTab('teams', event)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Teams ({{ count($teams) }})</span>
        </button>
        <button class="tab-btn flex-1 sm:flex-none justify-center whitespace-nowrap px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold flex items-center gap-2 transition-all" onclick="switchTab('players', event)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Players ({{ count($players) }})</span>
        </button>
        <button class="tab-btn flex-1 sm:flex-none justify-center whitespace-nowrap px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold flex items-center gap-2 transition-all" onclick="switchTab('matches', event)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span>Matches ({{ count($matches) }})</span>
        </button>
    </div>
    
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .tab-btn {
            background: transparent;
            color: var(--text-dim, #8b949e);
            border: none;
            cursor: pointer;
        }
        .tab-btn.active {
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .tab-btn:hover:not(.active) {
            color: #f8fafc;
            background: #161b22;
        }
    </style>

    <!-- TEAMS TAB -->
    <div id="tab-teams" class="tab-content active">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 shadow-md">
            <h3 class="text-base sm:text-lg font-black text-white uppercase tracking-tight mb-4 flex items-center gap-2">
                <span>🛡️</span> Manage Teams
            </h3>
            
            <form method="POST" action="{{ route('local.add-team', $tournament->id) }}" class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 mb-6">
                @csrf
                <input type="text" name="name" placeholder="Enter new team name..." required class="flex-1 bg-[#161b22] border border-[#30363d] text-white rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none placeholder-gray-500">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-black px-6 py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition-all shadow-md">
                    <span class="text-base">+</span> Add Team
                </button>
            </form>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @forelse($teams as $team)
                @php
                    $teamMatches = $matches->filter(fn($m) => $m->team1_id == $team->id || $m->team2_id == $team->id);
                    $p = 0; $w = 0; $pts = 0;
                    foreach($teamMatches as $m) {
                        if($m->status === 'completed') {
                            $p++;
                            if(($m->team1_id == $team->id && $m->team1_score > $m->team2_score) ||
                               ($m->team2_id == $team->id && $m->team2_score > $m->team1_score)) {
                                $w++; $pts += 2;
                            }
                        }
                    }
                @endphp
                <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-4 flex items-center justify-between hover:border-blue-500/40 transition-all">
                    <div>
                        <div class="font-extrabold text-sm sm:text-base text-white">{{ $team->name }}</div>
                        <div class="text-xs font-semibold text-gray-400 mt-1 uppercase">
                            P <span class="text-gray-200">{{ $p }}</span> &bull; W <span class="text-sky-400">{{ $w }}</span> &bull; Pts <span class="text-blue-400 font-bold">{{ $pts }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('local.delete-team', $team->id) }}" class="m-0" onsubmit="return confirm('Are you sure you want to delete this team?')">
                        @csrf
                        <button type="submit" class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Delete Team">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </form>
                </div>
                @empty
                <div class="col-span-full text-center py-8 text-gray-400 font-semibold text-sm">
                    No teams added yet. Type a team name above to create your first team!
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- PLAYERS TAB -->
    <div id="tab-players" class="tab-content">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 shadow-md">
            <h3 class="text-base sm:text-lg font-black text-white uppercase tracking-tight mb-4 flex items-center gap-2">
                <span>👥</span> Manage Players
            </h3>
            
            <form method="POST" action="{{ route('local.add-player', $tournament->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 mb-6 bg-[#161b22]/70 p-3.5 sm:p-4 rounded-xl border border-[#30363d]">
                @csrf
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Select Team</label>
                    <select name="team_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        <option value="" class="bg-[#161b22] text-gray-400">Select Team...</option>
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}" class="bg-[#161b22] text-white">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Player Name</label>
                    <input type="text" name="name" placeholder="Enter player name..." required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none placeholder-gray-500">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Playing Role</label>
                    <select name="role" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        <option value="Batsman" class="bg-[#161b22] text-white">Batsman</option>
                        <option value="Bowler" class="bg-[#161b22] text-white">Bowler</option>
                        <option value="All-Rounder" class="bg-[#161b22] text-white">All-Rounder</option>
                        <option value="Wicket Keeper" class="bg-[#161b22] text-white">Wicket Keeper</option>
                    </select>
                </div>
                <div class="sm:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black rounded-xl py-2.5 px-3 flex items-center justify-center text-lg transition-all shadow-md" title="Add Player">
                        +
                    </button>
                </div>
            </form>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($teams as $team)
                <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-4">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#30363d]">
                        <h4 class="font-black text-sm text-white uppercase tracking-tight m-0 flex items-center gap-2">
                            <span>🛡️</span> {{ $team->name }}
                        </h4>
                        <span class="text-xs font-bold text-gray-400 bg-[#0d1117] px-2 py-0.5 rounded-md border border-[#30363d]">
                            {{ $team->players->count() }} players
                        </span>
                    </div>
                    
                    @if($team->players->count() > 0)
                        <div class="flex flex-col gap-2">
                            @foreach($team->players as $player)
                                <div class="flex items-center justify-between p-2.5 bg-[#0d1117] border border-[#30363d]/60 rounded-lg hover:border-blue-500/40 transition-all">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="font-bold text-sm text-white truncate">{{ $player->name }}</span>
                                        <span class="bg-[#161b22] border border-[#30363d] px-2 py-0.5 rounded-full text-[10px] font-bold text-sky-400 flex-shrink-0">{{ $player->role }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('local.delete-player', $player->id) }}" class="m-0" onsubmit="return confirm('Delete player {{ addslashes($player->name) }}?')">
                                        @csrf
                                        <button type="submit" class="p-1 text-gray-500 hover:text-red-400 transition-colors" title="Delete player">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-xs text-gray-500 italic text-center py-4">No players added to this team yet.</div>
                    @endif
                </div>
                @empty
                <div class="col-span-full text-center py-8 text-gray-400 font-semibold text-sm">
                    No teams available. Please add teams first before adding players.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MATCHES TAB -->
    <div id="tab-matches" class="tab-content">
        <div class="bg-[#0d1117] border border-[#30363d] rounded-2xl p-4 sm:p-6 shadow-md">
            <h3 class="text-base sm:text-lg font-black text-white uppercase tracking-tight mb-4 flex items-center gap-2">
                <span>📅</span> Schedule & Manage Matches
            </h3>

            @if($teams->count() < 2)
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-5 sm:p-6 text-center mb-6">
                    <div class="text-3xl mb-2">🏏</div>
                    <h4 class="text-sm sm:text-base font-black text-amber-400 mb-1 uppercase">At Least 2 Teams Required</h4>
                    <p class="text-xs sm:text-sm text-gray-400 mb-4 max-w-md mx-auto">You cannot schedule a match without participating teams. Please add at least 2 teams to this tournament first.</p>
                    <button type="button" onclick="switchTab('teams', event)" class="bg-amber-500 hover:bg-amber-400 text-black font-black px-4 py-2 rounded-xl text-xs sm:text-sm inline-flex items-center gap-2 transition-all">
                        ➕ Add Teams Now
                    </button>
                </div>
            @else
                <form method="POST" action="{{ route('local.add-match', $tournament->id) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6 bg-[#161b22]/70 p-3.5 sm:p-4 rounded-xl border border-[#30363d]">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Team A *</label>
                        <select name="team1_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 outline-none">
                            <option value="" class="bg-[#161b22] text-gray-400">Select Team A...</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" class="bg-[#161b22] text-white">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Team B *</label>
                        <select name="team2_id" required class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 outline-none">
                            <option value="" class="bg-[#161b22] text-gray-400">Select Team B...</option>
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" class="bg-[#161b22] text-white">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                        <select name="status" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 outline-none">
                            <option value="scheduled" class="bg-[#161b22] text-white">⏳ Scheduled</option>
                            <option value="live" class="bg-[#161b22] text-white">⚡ Live (In Progress)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Venue / Ground</label>
                        <input type="text" name="venue" placeholder="e.g. Ground A, Indore" class="w-full bg-[#161b22] border border-[#30363d] text-white rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-500 outline-none placeholder-gray-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl py-2.5 px-4 text-sm flex items-center justify-center gap-2 transition-all shadow-md">
                            <span>+</span> Schedule Match
                        </button>
                    </div>
                </form>
            @endif

            <div class="flex flex-col gap-3">
                @forelse($matches as $index => $match)
                <div class="bg-[#161b22] border border-[#30363d] rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-blue-500/40 transition-all">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-xs font-bold text-gray-400 uppercase">Match {{ $index + 1 }}</span>
                            <span class="text-gray-500">&bull;</span>
                            <span class="font-extrabold text-sm sm:text-base text-white">
                                {{ $match->team1?->name ?? 'TBD' }} <span class="text-sky-400">vs</span> {{ $match->team2?->name ?? 'TBD' }}
                            </span>
                        </div>
                        <div class="text-xs font-semibold text-gray-400 flex items-center gap-2 flex-wrap">
                            <span>📅 {{ $match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('n/j/Y, g:i A') : 'TBD' }}</span>
                            @if($match->venue)
                                <span>&bull;</span>
                                <span>📍 {{ $match->venue }}</span>
                            @endif
                            @if($match->custom_note)
                                <span>&bull;</span>
                                <span class="text-gray-300">{{ $match->custom_note }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-end">
                        @if($match->status === 'live')
                            <span class="bg-red-500/15 border border-red-500/30 text-red-400 px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider animate-pulse">⚡ live</span>
                        @elseif($match->status === 'completed')
                            <span class="bg-blue-500/15 border border-blue-500/30 text-sky-400 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">completed</span>
                        @else
                            <span class="bg-[#0d1117] border border-[#30363d] text-gray-400 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">scheduled</span>
                        @endif

                        {{-- Quick Go Live / Pause toggle --}}
                        @if($match->status === 'scheduled')
                            <form method="POST" action="{{ route('local.match.update-status', $match->id) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="status" value="live">
                                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1 transition-all shadow-sm" title="Set Match to Live">
                                    ⚡ Go Live
                                </button>
                            </form>
                        @elseif($match->status === 'live')
                            <form method="POST" action="{{ route('local.match.update-status', $match->id) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="status" value="scheduled">
                                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg flex items-center gap-1 transition-all" title="Pause / Set Scheduled">
                                    ⏸ Scheduled
                                </button>
                            </form>
                        @endif

                        {{-- Score button: go to toss if not started, else directly to scorer --}}
                        @if($match->status === 'live')
                            <a href="{{ route('local.scorer', $match->id) }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition-all shadow-sm">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> Score
                            </a>
                        @else
                            <a href="{{ route('local.toss', $match->id) }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition-all shadow-sm">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> Score
                            </a>
                        @endif

                        <a href="{{ route('local.match.detail', $match->id) }}" class="bg-[#0d1117] hover:bg-[#161b22] border border-[#30363d] text-gray-300 hover:text-white font-bold text-xs px-3 py-1.5 rounded-lg transition-all">
                            View
                        </a>

                        <form method="POST" action="{{ route('local.delete-match', $match->id) }}" class="m-0" onsubmit="return confirm('Delete this match?')">
                            @csrf
                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Delete Match">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-gray-400 font-semibold text-sm">No matches scheduled yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</main>

<script>
    function switchTab(tabId, event) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        const targetTab = document.getElementById('tab-' + tabId);
        if (targetTab) targetTab.classList.add('active');
        
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        }
    }
</script>
@endsection
