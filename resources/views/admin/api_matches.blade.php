@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto pb-20">

    <!-- Top Breadcrumbs & Heading -->
    <div class="flex justify-between items-start mb-6 flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                <span>Cricket Data Integration</span> 
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight uppercase m-0">
                API Live, Scheduled Matches & Series
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-semibold mt-1">
                Automated live, scheduled international matches & series sync from CricketData.org with full publishing control.
            </p>
        </div>

        <!-- Sync Action Button -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <form method="POST" action="{{ route('admin.api-matches.fetch') }}" class="m-0">
                @csrf
                <button type="submit" onclick="this.disabled=true; this.innerText='⏳ Fetching from API...'; this.form.submit();" class="bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-black px-5 py-3 rounded-xl text-xs sm:text-sm transition-all shadow-md shadow-blue-500/20 flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Fetch Latest Matches
                </button>
            </form>
        </div>
    </div>

    <!-- Main Navigation Tabs (Matches vs Upcoming Series) -->
    <div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => 'all', 'approval' => 'all']) }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all {{ $tab !== 'series' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <span>⚡</span> API Matches Feed
            <span class="px-2 py-0.5 rounded-full text-[11px] {{ $tab !== 'series' ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">{{ $totalApiMatches }}</span>
        </a>
        <a href="{{ route('admin.api-matches', ['tab' => 'series', 'status' => 'all']) }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-black transition-all {{ $tab === 'series' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <span>🏆</span> Upcoming & Active Series
            <span class="px-2 py-0.5 rounded-full text-[11px] {{ $tab === 'series' ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-700' }}">{{ $upcomingSeriesCount }}</span>
        </a>
    </div>

    <!-- API Quota & Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: API Quota Usage -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[11px] font-black uppercase text-slate-400 tracking-wider">API Hits Today</span>
                <span class="bg-sky-50 text-sky-700 font-bold text-[10px] px-2 py-0.5 rounded-full border border-sky-200">Free Tier</span>
            </div>
            <div class="flex items-baseline gap-1.5 mb-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ $stats['hitsToday'] ?? 0 }}</span>
                <span class="text-xs sm:text-sm font-bold text-slate-400">/ {{ $stats['hitsLimit'] ?? 100 }} hits</span>
            </div>
            <!-- Quota Progress Bar -->
            @php
                $hits = (int)($stats['hitsToday'] ?? 0);
                $limit = (int)($stats['hitsLimit'] ?? 100);
                $pct = $limit > 0 ? min(100, round(($hits / $limit) * 100)) : 0;
            @endphp
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-1.5">
                <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $pct }}%;"></div>
            </div>
            <div class="text-[10px] text-slate-400 font-semibold flex justify-between">
                <span>{{ 100 - $hits }} hits remaining today</span>
                <span>{{ $pct }}%</span>
            </div>
        </div>

        <!-- Card 2: Approved / Live on Site -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">Live on Website</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mb-1">{{ $approvedCount }}</div>
            <div class="text-xs text-slate-500 font-semibold">Approved by Admin</div>
        </div>

        <!-- Card 3: Scheduled Matches -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[11px] font-black uppercase text-slate-400 tracking-wider">Scheduled Matches</span>
                <span class="bg-blue-50 text-blue-700 font-bold text-[10px] px-2 py-0.5 rounded-full border border-blue-200">Upcoming</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-blue-600 mb-1">{{ $scheduledCount }}</div>
            <div class="text-xs text-slate-500 font-semibold">Upcoming / In Schedule</div>
        </div>

        <!-- Card 4: Total API Matches -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">Total Fetched</div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mb-1">{{ $totalApiMatches }}</div>
            <div class="text-xs text-slate-500 font-semibold">
                {{ $liveCount }} Live &bull; {{ $completedCount }} Completed
            </div>
        </div>
    </div>

    <!-- Filter Pills & Search Bar with Clear/Cross Button -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 shadow-sm">
        <div class="flex justify-between items-center flex-wrap gap-4">
            
            @if($tab === 'series')
                <!-- Series Filters -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
                    <span class="text-xs font-bold text-slate-400 uppercase mr-1">Status:</span>
                    <a href="{{ route('admin.api-matches', ['tab' => 'series', 'status' => 'all', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        All Series
                    </a>
                    <a href="{{ route('admin.api-matches', ['tab' => 'series', 'status' => 'upcoming', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100 border border-blue-200' }}">
                        📅 Upcoming Series
                    </a>
                    <a href="{{ route('admin.api-matches', ['tab' => 'series', 'status' => 'ongoing', 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'ongoing' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}">
                        🔴 Ongoing Series
                    </a>
                </div>
            @else
                <!-- Matches Status & Approval Filters -->
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none flex-nowrap">
                        <span class="text-xs font-bold text-slate-400 uppercase mr-1">Status:</span>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => 'all', 'approval' => $approvalFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All ({{ $totalApiMatches }})
                        </a>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => 'live', 'approval' => $approvalFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'live' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-800 hover:bg-red-100 border border-red-200' }}">
                            🔴 Live ({{ $liveCount }})
                        </a>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => 'scheduled', 'approval' => $approvalFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'scheduled' || $statusFilter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100 border border-blue-200' }}">
                            📅 Scheduled ({{ $scheduledCount }})
                        </a>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => 'completed', 'approval' => $approvalFilter, 'search' => $search]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'completed' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            ✓ Completed ({{ $completedCount }})
                        </a>
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto scrollbar-none flex-nowrap">
                        <span class="text-xs font-bold text-slate-400 uppercase mr-1">Approval:</span>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => $statusFilter, 'approval' => 'all', 'search' => $search]) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $approvalFilter === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All
                        </a>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => $statusFilter, 'approval' => 'pending', 'search' => $search]) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $approvalFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                            Pending Approval ({{ $pendingCount }})
                        </a>
                        <a href="{{ route('admin.api-matches', ['tab' => 'matches', 'status' => $statusFilter, 'approval' => 'approved', 'search' => $search]) }}" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $approvalFilter === 'approved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}">
                            Approved / Live ({{ $approvedCount }})
                        </a>
                    </div>
                </div>
            @endif

            <!-- Search Box with Clear (Cross ✕) Button -->
            <form method="GET" action="{{ route('admin.api-matches') }}" id="apiSearchForm" class="m-0 flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="approval" value="{{ $approvalFilter }}">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <div class="relative flex items-center">
                    <input 
                        type="text" 
                        name="search" 
                        id="apiSearchInput" 
                        value="{{ $search }}" 
                        placeholder="{{ $tab === 'series' ? 'Search series name, category...' : 'Search team, venue, series...' }}" 
                        autocomplete="off"
                        class="bg-slate-50 border border-slate-300 text-slate-900 rounded-xl pl-3 pr-8 py-2 text-xs font-semibold focus:border-blue-500 focus:bg-white outline-none w-52 sm:w-72 transition-all"
                    >

                    <!-- Cross (Clear) Button -->
                    <button 
                        type="button" 
                        id="clearSearchBtn" 
                        onclick="clearSearchQuery()" 
                        class="{{ empty($search) ? 'hidden' : 'flex' }} absolute right-2.5 text-slate-400 hover:text-slate-700 bg-slate-200/80 hover:bg-slate-300 w-5 h-5 rounded-full items-center justify-center text-xs font-black cursor-pointer transition-all"
                        title="Clear search"
                    >
                        ✕
                    </button>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                    Search
                </button>
            </form>
        </div>
    </div>

    @if($tab === 'series')
        <!-- UPCOMING & ACTIVE SERIES TABLE -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            @if($series->count() > 0)
                <div class="table-responsive-wrapper overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Series / Tournament</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Category & Format</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Schedule & Dates</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase text-center">Matches / Teams</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($series as $s)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-extrabold text-slate-900 leading-tight">
                                            {{ $s->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-semibold mt-0.5">
                                            Code: <span class="text-slate-600 font-bold">{{ $s->short_name ?: 'N/A' }}</span> &bull; Slug: <span class="font-mono text-[10px]">{{ $s->slug }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="bg-blue-50 border border-blue-200 text-blue-700 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                {{ $s->format ?: 'T20' }}
                                            </span>
                                            <span class="bg-slate-100 border border-slate-200 text-slate-700 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                {{ $s->category ?: 'International' }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-semibold">
                                            Type: {{ strtoupper($s->series_type ?: 'GLOBAL') }}
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($s->status === 'upcoming')
                                                <span class="bg-blue-500/10 border border-blue-500/30 text-blue-700 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                    📅 Upcoming
                                                </span>
                                            @elseif($s->status === 'ongoing')
                                                <span class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                    🔴 Ongoing
                                                </span>
                                            @else
                                                <span class="bg-slate-100 text-slate-600 font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                    {{ ucfirst($s->status) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 font-semibold">
                                            @if($s->start_date)
                                                {{ \Carbon\Carbon::parse($s->start_date)->format('M d, Y') }}
                                                @if($s->end_date)
                                                    &ndash; {{ \Carbon\Carbon::parse($s->end_date)->format('M d, Y') }}
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic">Dates to be announced</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        <div class="inline-flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1 text-xs font-bold text-slate-700">
                                            <span>🏏 {{ $s->matches_count }} Matches</span>
                                            <span class="text-slate-300">|</span>
                                            <span>👥 {{ $s->teams_count }} Teams</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.series') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold px-3 py-1.5 rounded-xl text-xs transition-all">
                                                Manage Series
                                            </a>
                                            <a href="{{ route('tournament.public', $s->slug ?: $s->id) }}" target="_blank" class="bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold px-2.5 py-1.5 rounded-xl text-xs transition-all" title="View Public Page">
                                                View &rarr;
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center flex-wrap gap-2">
                    <div class="text-xs text-slate-500 font-semibold">
                        Showing {{ $series->firstItem() ?? 0 }} to {{ $series->lastItem() ?? 0 }} of {{ $series->total() }} series
                    </div>
                    <div>
                        {{ $series->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-16 px-4">
                    <div class="text-4xl mb-3">🏆</div>
                    <h3 class="text-base sm:text-lg font-black text-slate-800 mb-1">No Series Found</h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mb-5">
                        {{ !empty($search) ? 'No series match your search query.' : 'No upcoming or active series currently in the database.' }}
                    </p>
                    <a href="{{ route('admin.series') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow-md inline-flex items-center gap-2">
                        <span>+</span> Create New Series
                    </a>
                </div>
            @endif
        </div>

    @else
        <!-- API MATCHES TABLE -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            @if($matches->count() > 0)
                <div class="table-responsive-wrapper overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Match / Teams</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Format & Venue</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase">Scores / Status</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase text-center">Live Site Status</th>
                                <th class="py-3 px-4 text-xs font-black text-slate-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($matches as $m)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Teams & Logos -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center -space-x-2 flex-shrink-0">
                                                @if($m->team1?->logo_url)
                                                    <img src="{{ $m->team1->logo_url }}" alt="{{ $m->team1->name }}" class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 object-contain p-0.5" onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-black text-xs flex items-center justify-center border-2 border-slate-200">
                                                        {{ substr($m->team1?->name ?? 'T1', 0, 2) }}
                                                    </div>
                                                @endif

                                                @if($m->team2?->logo_url)
                                                    <img src="{{ $m->team2->logo_url }}" alt="{{ $m->team2->name }}" class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 object-contain p-0.5" onerror="this.src='{{ asset('images/placeholder.png') }}'">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center border-2 border-slate-200">
                                                        {{ substr($m->team2?->name ?? 'T2', 0, 2) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-extrabold text-slate-900 leading-tight">
                                                    {{ $m->team1?->name ?? 'Team 1' }} <span class="text-slate-400 font-semibold">vs</span> {{ $m->team2?->name ?? 'Team 2' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-semibold truncate mt-0.5 max-w-xs">
                                                    {{ $m->tournament?->name ?? 'International Cricket' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Format & Venue -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="bg-slate-100 border border-slate-300 text-slate-800 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                {{ $m->match_type }}
                                            </span>
                                            <span class="text-xs text-slate-500 font-semibold">
                                                {{ \Carbon\Carbon::parse($m->match_date)->format('M d, Y') }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 font-medium truncate max-w-[200px]" title="{{ $m->venue?->name }}">
                                            📍 {{ $m->venue?->name ?? 'Stadium Ground' }}
                                        </div>
                                    </td>

                                    <!-- Scores & Status -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($m->status === 'live')
                                                <span class="bg-red-500/10 border border-red-500/30 text-red-600 font-black text-[10px] px-2 py-0.5 rounded-full uppercase animate-pulse">
                                                    🔴 LIVE
                                                </span>
                                            @elseif($m->status === 'completed')
                                                <span class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                    ✓ Completed
                                                </span>
                                            @else
                                                <span class="bg-blue-500/10 border border-blue-500/30 text-blue-700 font-black text-[10px] px-2 py-0.5 rounded-full uppercase">
                                                    📅 Scheduled
                                                </span>
                                            @endif
                                        </div>

                                        @if($m->status === 'live' || $m->status === 'completed')
                                            <div class="text-xs font-bold text-slate-800">
                                                {{ $m->team1?->short_name ?? 'T1' }}: <strong class="text-slate-900">{{ $m->team1_score }}/{{ $m->team1_wickets }}</strong> ({{ $m->team1_overs }} ov)
                                            </div>
                                            @if($m->team2_score > 0 || $m->team2_overs > 0)
                                                <div class="text-xs font-bold text-slate-800">
                                                    {{ $m->team2?->short_name ?? 'T2' }}: <strong class="text-slate-900">{{ $m->team2_score }}/{{ $m->team2_wickets }}</strong> ({{ $m->team2_overs }} ov)
                                                </div>
                                            @endif
                                        @else
                                            <!-- Scheduled Match Time & Awaiting info -->
                                            <div class="text-xs font-bold text-slate-700">
                                                🕒 {{ \Carbon\Carbon::parse($m->match_date)->format('h:i A') }} (Local Time)
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-semibold">
                                                Match Scheduled &bull; Upcoming
                                            </div>
                                        @endif

                                        @if($m->custom_note)
                                            <div class="text-[11px] text-slate-500 italic mt-0.5 truncate max-w-[220px]">
                                                {{ $m->custom_note }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Live Site Visibility Badge -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if($m->is_approved)
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-300 font-black text-[11px] px-3 py-1 rounded-full shadow-sm">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                Live on Site
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-300 font-bold text-[11px] px-3 py-1 rounded-full">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Pending Approval
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Toggle Approval Button -->
                                            <form method="POST" action="{{ route('admin.api-matches.toggle-approval', $m->id) }}" class="m-0">
                                                @csrf
                                                @if($m->is_approved)
                                                    <button type="submit" class="bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-bold px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer" title="Hide this match from live site">
                                                        Hide from Site
                                                    </button>
                                                @else
                                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black px-3.5 py-1.5 rounded-xl text-xs transition-all shadow-sm cursor-pointer" title="Approve and show on live site">
                                                        ✓ Approve & Publish
                                                    </button>
                                                @endif
                                            </form>

                                            <!-- Details Link -->
                                            <a href="{{ route('admin.match.detail', $m->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold px-2.5 py-1.5 rounded-xl text-xs transition-all" title="View Match Scorecard">
                                                Details &rarr;
                                            </a>

                                            <!-- Delete Button -->
                                            <form method="POST" action="{{ route('admin.api-matches.delete', $m->id) }}" onsubmit="return confirm('Delete this API match record?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 font-bold px-2.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer" title="Delete Match">
                                                    ✕
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center flex-wrap gap-2">
                    <div class="text-xs text-slate-500 font-semibold">
                        Showing {{ $matches->firstItem() ?? 0 }} to {{ $matches->lastItem() ?? 0 }} of {{ $matches->total() }} matches
                    </div>
                    <div>
                        {{ $matches->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-16 px-4">
                    <div class="text-4xl mb-3">📡</div>
                    <h3 class="text-base sm:text-lg font-black text-slate-800 mb-1">No API Matches Found</h3>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mb-5">
                        {{ !empty($search) ? 'No matches matched your search criteria.' : 'Click the "Fetch Latest Matches" button above to pull current live and upcoming cricket matches from CricketData.org.' }}
                    </p>
                    <form method="POST" action="{{ route('admin.api-matches.fetch') }}" class="m-0 inline-block">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow-md cursor-pointer">
                            ⚡ Fetch Matches Now
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

</div>

<!-- Interactive Search Input & Clear (Cross) Handler -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('apiSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const searchForm = document.getElementById('apiSearchForm');

        if (searchInput && clearBtn) {
            // Monitor typing in input
            searchInput.addEventListener('input', function() {
                if (this.value.trim().length > 0) {
                    clearBtn.classList.remove('hidden');
                    clearBtn.classList.add('flex');
                } else {
                    clearBtn.classList.add('hidden');
                    clearBtn.classList.remove('flex');
                }
            });

            // ESC key to clear
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    clearSearchQuery();
                }
            });
        }
    });

    function clearSearchQuery() {
        const searchInput = document.getElementById('apiSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const searchForm = document.getElementById('apiSearchForm');

        if (searchInput) {
            const hadValue = searchInput.value.trim().length > 0;
            searchInput.value = '';
            if (clearBtn) {
                clearBtn.classList.add('hidden');
                clearBtn.classList.remove('flex');
            }

            // If a search was already performed via query string, re-submit form to reset view
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search') && urlParams.get('search') !== '') {
                urlParams.delete('search');
                urlParams.delete('page');
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            } else {
                searchInput.focus();
            }
        }
    }
</script>
@endsection
