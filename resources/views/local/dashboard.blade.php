@extends('layouts.local')

@section('content')
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10 pb-28">

    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight uppercase m-0" style="color: var(--text-main);">LOCAL CRICKET</h1>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-dim);">Manage your own tournaments, schedule matches, and explore community tournaments</p>
        </div>

        <button type="button" onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm px-5 py-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-500/20 flex-shrink-0">
            <span class="text-base font-black">+</span>
            <span>Create Tournament</span>
        </button>
    </div>

    <!-- Stats Cards (For My Tournaments) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4 mb-8">
        <!-- Card 1 -->
        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY UPCOMING / DRAFTS</div>
            <div class="text-2xl sm:text-3xl font-black" style="color: var(--text-main);">{{ $upcomingCount }}</div>
        </div>

        <!-- Card 2 -->
        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY ONGOING / LIVE</div>
            <div class="text-2xl sm:text-3xl font-black flex items-center gap-2" style="color: var(--text-main);">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                <span>{{ $ongoingCount }}</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="rounded-2xl p-4 sm:p-5 shadow-md" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: var(--text-dim);">MY COMPLETED</div>
            <div class="text-2xl sm:text-3xl font-black" style="color: #38bdf8;">{{ $completedCount }}</div>
        </div>
    </div>

    <!-- SECTION 1: MY TOURNAMENTS (Full Management Control) -->
    <div class="mb-8">
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
                                <span style="color: #f43f5e;">📍</span> {{ $t->city ?? 'Local' }}
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded-md font-bold text-[11px]" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">{{ $t->format }}</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->teams->count() }} Teams</span>
                                <span>&bull;</span>
                                <span class="text-[11px]">{{ $t->matches->count() }} Matches</span>
                            </div>
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
                <h3 class="text-base sm:text-lg font-black mb-2" style="color: var(--text-main);">You haven't created any tournaments yet</h3>
                <p class="text-xs sm:text-sm mb-6 max-w-sm mx-auto" style="color: var(--text-dim);">Start hosting your own local cricket tournament with live scoring, teams, and automatic points tables!</p>
                <button type="button" onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-all shadow-md">
                    + Create Your First Tournament
                </button>
            </div>
        @endif
    </div>

    <!-- SECTION 2: COMMUNITY & OTHER LOCAL TOURNAMENTS (View Only) -->
    @if($otherTournaments->count() > 0)
    <div>
        <div class="flex items-center justify-between gap-2 mb-4 pt-6" style="border-top: 1px solid var(--border-color);">
            <div class="flex items-center gap-2">
                <span class="text-lg">🌐</span>
                <h2 class="text-sm sm:text-base font-black uppercase tracking-wider m-0" style="color: var(--text-main);">COMMUNITY TOURNAMENTS</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: var(--bg-card-secondary); color: var(--text-dim); border: 1px solid var(--border-color);">{{ $otherTournaments->count() }}</span>
            </div>
            <span class="text-[11px] font-semibold flex items-center gap-1" style="color: var(--text-dim);">
                <span>🔒</span> View Only
            </span>
        </div>

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
                            <span style="color: #f43f5e;">📍</span> {{ $t->city ?? 'Local' }}
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
    </div>
    @endif

</main>

<!-- Create Tournament Modal -->
<div id="createModal" style="display:none;" class="fixed inset-0 bg-black/80 z-50 backdrop-blur-sm items-center justify-center p-4">
    <div class="rounded-2xl max-w-lg w-full max-h-[90vh] flex flex-col p-5 sm:p-6 shadow-2xl relative" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <div class="flex items-center justify-between pb-3 mb-4" style="border-bottom: 1px solid var(--border-color);">
            <h2 class="text-lg font-black uppercase tracking-tight m-0" style="color: var(--text-main);">Create Tournament</h2>
            <button type="button" onclick="closeCreateModal()" class="text-xl font-bold p-1" style="color: var(--text-dim); background:none; border:none; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('local.create-tournament') }}" class="overflow-y-auto pr-1 flex-1 flex flex-col gap-3.5">
            @csrf
            
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Tournament Name *</label>
                <input type="text" name="name" required placeholder="e.g. Premier Cricket League" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">City *</label>
                    <input type="text" name="city" required placeholder="e.g. Indore" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">State</label>
                    <input type="text" name="state" placeholder="e.g. MP" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                </div>
            </div>
            
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--text-dim);">Venue</label>
                <input type="text" name="venue" placeholder="e.g. Holkar Cricket Stadium" class="w-full rounded-xl px-3.5 py-2.5 text-sm outline-none" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
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
</script>

@endsection
