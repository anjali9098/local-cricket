@php
    $isLocal = ($s->series_type === 'LOCAL') || ($s->category === 'local');
    $badgeCategory = $isLocal ? 'LOCAL' : (strtoupper($s->category ?? 'TOURNAMENT'));
    $teamsCount = $s->teams ? $s->teams->count() : 0;
    $matchesCount = $s->matches ? $s->matches->count() : 0;
    $loc = trim(($s->city ?? '') . ($s->state ? ', ' . $s->state : ''));
@endphp

<div class="rounded-2xl p-5 flex flex-col justify-between transition-all duration-200 shadow-md group relative overflow-hidden" 
    style="background: var(--bg-card); border: 1px solid var(--border-color);"
    onmouseover="this.style.borderColor='rgba(56, 189, 248, 0.4)'; this.style.transform='translateY(-2px)';"
    onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateY(0)';"
>
    <!-- Top Level Badge & Status -->
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[10px] font-black px-2 py-0.5 rounded tracking-wider uppercase {{ $isLocal ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                {{ $isLocal ? '🏏 LOCAL' : '🏆 ' . $badgeCategory }}
            </span>

            @if($statusColor === 'emerald')
                <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>LIVE</span>
                </span>
            @elseif($statusColor === 'sky')
                <span class="inline-flex items-center gap-1 bg-sky-500/15 border border-sky-500/30 text-sky-400 font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase">
                    <span>UPCOMING</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1 bg-purple-500/15 border border-purple-500/30 text-purple-300 font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase">
                    <span>COMPLETED</span>
                </span>
            @endif
        </div>

        <!-- Tournament Name -->
        <a href="{{ route('tournament.public', $s->id) }}" class="block text-decoration-none mb-2.5">
            <h3 class="text-lg font-black text-white group-hover:text-sky-400 transition-colors m-0 tracking-tight leading-snug">
                {{ $s->name }}
            </h3>
        </a>

        <!-- Specs & Format Tags -->
        <div class="flex flex-wrap items-center gap-1.5 mb-3.5">
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/5 border border-white/10 text-sky-300">
                ⚡ {{ $s->format ?? 'T20' }}
            </span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/5 border border-white/10 text-gray-300">
                ⏱️ {{ $s->overs ?? 20 }} Overs
            </span>
            @if(!empty($s->type))
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/5 border border-white/10 text-amber-300">
                    ⚔️ {{ $s->type }}
                </span>
            @endif
        </div>

        <!-- Location & Dates -->
        <div class="space-y-1.5 text-xs text-gray-400 mb-4">
            @if(!empty($loc))
                <div class="flex items-center gap-1.5">
                    <span>📍</span>
                    <span class="font-medium text-gray-300">{{ $loc }}</span>
                </div>
            @endif
            @if(!empty($s->venue))
                <div class="flex items-center gap-1.5 truncate">
                    <span>🏟️</span>
                    <span class="font-medium text-gray-400 truncate">{{ $s->venue }}</span>
                </div>
            @endif
            <div class="flex items-center gap-1.5">
                <span>📅</span>
                <span class="font-medium text-gray-400">
                    @if($s->start_date)
                        {{ \Carbon\Carbon::parse($s->start_date)->format('d M') }}
                        @if($s->end_date)
                            - {{ \Carbon\Carbon::parse($s->end_date)->format('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($s->start_date)->format('Y') }}
                        @endif
                    @else
                        Season {{ $s->year ?? '2026' }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Bottom Stats & Action Bar -->
    <div class="pt-3 border-t" style="border-color: var(--border-color);">
        <div class="flex items-center justify-between mb-3 text-xs">
            <div class="flex items-center gap-1 font-bold text-gray-300">
                <span class="text-amber-400">🛡️</span>
                <span>{{ $teamsCount }} {{ Str::plural('Team', $teamsCount) }}</span>
            </div>
            <div class="flex items-center gap-1 font-bold text-gray-300">
                <span class="text-sky-400">🏏</span>
                <span>{{ $matchesCount }} {{ Str::plural('Match', $matchesCount) }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(Auth::check() && (Auth::id() == $s->user_id || Auth::user()->role === 'superadmin'))
                <a href="{{ route('local.manage-tournament', $s->id) }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs py-2 px-3 rounded-xl inline-flex items-center justify-center gap-1 transition-all shadow-sm flex-1 text-decoration-none">
                    <span>⚙️ Manage</span>
                </a>
            @endif
            <a href="{{ route('tournament.public', $s->id) }}" class="font-bold text-xs py-2 px-3 rounded-xl inline-flex items-center justify-center gap-1.5 transition-all flex-1 text-decoration-none text-center" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main);">
                <span>View Tournament &rarr;</span>
            </a>
        </div>
    </div>
</div>
