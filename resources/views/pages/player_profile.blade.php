@extends('layouts.app')

@section('content')
<main class="container mx-auto px-3 sm:px-6 py-6 sm:py-10 pb-24" style="font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Breadcrumb & Back -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-dim);">
            <a href="{{ route('home') }}" style="color: var(--text-dim); text-decoration: none;">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('players') }}" style="color: var(--text-dim); text-decoration: none;">Players</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">{{ $player->name }}</span>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('players') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: background 0.15s;">
                &larr; All Players
            </a>
        </div>
    </div>

    <!-- Player Hero Header Card -->
    <div class="rounded-2xl p-4 sm:p-7 mb-7 relative overflow-hidden shadow-xl" style="background: var(--bg-card); border: 1px solid var(--border-color);">
        <div style="position: absolute; top: -60px; right: -60px; width: 260px; height: 260px; background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
            
            <!-- Player Image / Avatar -->
            <div style="flex-shrink: 0; width: 130px; height: 130px; position: relative;">
                @if(!empty($player->profile_image))
                    <img src="{{ $player->profile_image }}" alt="{{ $player->name }}" style="width: 130px; height: 130px; border-radius: 18px; object-fit: cover; border: 3px solid #38bdf8; box-shadow: 0 8px 24px rgba(56, 189, 248, 0.35);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="width: 130px; height: 130px; border-radius: 18px; background: linear-gradient(135deg, #0284c7, #38bdf8); display: none; align-items: center; justify-content: center; font-size: 3rem; font-weight: 900; color: white; box-shadow: 0 8px 24px rgba(2,132,199,0.35);">
                        {{ $player->initials ?? strtoupper(substr($player->name, 0, 2)) }}
                    </div>
                @else
                    <div style="width: 130px; height: 130px; border-radius: 18px; background: linear-gradient(135deg, #0284c7, #38bdf8); display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 900; color: white; box-shadow: 0 8px 24px rgba(2,132,199,0.35);">
                        {{ $player->initials ?? strtoupper(substr($player->name, 0, 2)) }}
                    </div>
                @endif
                @if($player->jersey_number)
                    <div style="position: absolute; bottom: -8px; right: -8px; background: #0284c7; color: white; font-weight: 900; font-size: 0.85rem; padding: 2px 8px; border-radius: 8px; border: 2px solid var(--bg-card); box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                        #{{ $player->jersey_number }}
                    </div>
                @endif
            </div>

            <!-- Profile Overview Header Info -->
            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; margin-bottom: 4px;">
                    <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">
                        {{ $player->name }}
                    </h1>
                </div>

                @if(!empty($player->nickname) && $player->nickname !== $player->name)
                    <div style="font-size: 0.88rem; color: var(--text-dim); font-weight: 600; margin-bottom: 6px;">
                        Known as: <span style="color: var(--text-main); font-weight: 800;">"{{ $player->nickname }}"</span>
                    </div>
                @endif

                <!-- 4-Card Responsive Grid Covering the Full Width: Role | Team | Nationality | D.O.B. -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 mt-3.5 pt-3" style="border-top: 1px solid rgba(255,255,255,0.06);">
                    
                    <!-- 1. Player Role -->
                    <div style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.28); border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span style="font-size: 1.15rem; flex-shrink: 0;">🏏</span>
                        <div style="min-width: 0; line-height: 1.2;">
                            <span style="font-size: 0.65rem; font-weight: 800; color: #86efac; text-transform: uppercase; letter-spacing: 0.06em; display: block;">ROLE</span>
                            <strong style="font-size: 0.84rem; font-weight: 900; color: #22c55e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                {{ strtoupper($player->role ?? 'Player') }}
                            </strong>
                        </div>
                    </div>

                    <!-- 2. Team -->
                    <div style="background: rgba(56, 189, 248, 0.10); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span style="font-size: 1.15rem; flex-shrink: 0;">🛡️</span>
                        <div style="min-width: 0; line-height: 1.2;">
                            <span style="font-size: 0.65rem; font-weight: 800; color: #7dd3fc; text-transform: uppercase; letter-spacing: 0.06em; display: block;">TEAM</span>
                            <strong style="font-size: 0.84rem; font-weight: 900; color: #38bdf8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;" title="{{ $player->team ? $player->team->name : ($player->nationality ?: 'Free Agent') }}">
                                {{ $player->team ? $player->team->name : ($player->nationality ?: 'Free Agent') }}
                            </strong>
                        </div>
                    </div>

                    <!-- 3. Nationality -->
                    <div style="background: rgba(168, 85, 247, 0.10); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span style="font-size: 1.15rem; flex-shrink: 0;">📍</span>
                        <div style="min-width: 0; line-height: 1.2;">
                            <span style="font-size: 0.65rem; font-weight: 800; color: #d8b4fe; text-transform: uppercase; letter-spacing: 0.06em; display: block;">NATIONALITY</span>
                            <strong style="font-size: 0.84rem; font-weight: 900; color: #c084fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                {{ $player->nationality ?: ($player->country ?: 'India') }}
                            </strong>
                        </div>
                    </div>

                    <!-- 4. Date of Birth -->
                    <div style="background: rgba(244, 63, 94, 0.10); border: 1px solid rgba(244, 63, 94, 0.25); border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span style="font-size: 1.15rem; flex-shrink: 0;">🎂</span>
                        <div style="min-width: 0; line-height: 1.2;">
                            <span style="font-size: 0.65rem; font-weight: 800; color: #fda4af; text-transform: uppercase; letter-spacing: 0.06em; display: block;">BORN (DOB)</span>
                            <strong style="font-size: 0.84rem; font-weight: 900; color: #fb7185; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                {{ $player->date_of_birth ? \Carbon\Carbon::parse($player->date_of_birth)->format('d M Y') : '-' }}
                            </strong>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start mb-12">
        
        <!-- ================= LEFT COLUMN: Personal Specs & Family Details ================= -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Personal Specifications Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Personal Details
                    </span>
                    <span style="font-size: 0.72rem; color: #38bdf8; font-weight: 700; text-transform: uppercase;">Profile Specs</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Nickname -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Nickname</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">{{ $player->nickname ?: '-' }}</span>
                    </div>

                    <!-- Born (DOB) -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Born (DOB)</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->date_of_birth ? \Carbon\Carbon::parse($player->date_of_birth)->format('d M Y') : '-' }}
                        </span>
                    </div>

                    <!-- Age -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Age</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: #38bdf8; text-align: right;">
                            {{ $player->age ?: '-' }}
                        </span>
                    </div>

                    <!-- Birthplace -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Birthplace</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->birthplace ?: '-' }}
                        </span>
                    </div>

                    <!-- Height -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Height</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->height ?: '-' }}
                        </span>
                    </div>

                    <!-- Role -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Role</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->role ? ucwords(str_replace('_', ' ', $player->role)) : '-' }}
                        </span>
                    </div>

                    <!-- Batting Style -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Batting Style</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->batting_style ?: '-' }}
                        </span>
                    </div>

                    <!-- Bowling Style -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Bowling Style</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->bowling_style ?: '-' }}
                        </span>
                    </div>

                    <!-- Nationality -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); min-width: 100px;">Nationality</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->nationality ?: ($player->country ?: '-') }}
                        </span>
                    </div>

                    <!-- Played Teams list summary -->
                    <div style="padding-top: 10px; border-top: 1px dashed var(--border-color);">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 6px;">Played Teams</span>
                        <div style="font-size: 0.84rem; line-height: 1.5; color: var(--text-main); font-weight: 600;">
                            {{ $player->played_teams ?: ($player->team ? $player->team->name : '-') }}
                        </div>
                    </div>

                </div>
            </div>

            <!-- Family Details Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span>👨‍👩‍👧</span> Family Details
                    </span>
                    <span style="font-size: 0.72rem; color: #10b981; font-weight: 700; text-transform: uppercase;">Background</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 13px;">
                    <!-- Father -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);">Father</span>
                        <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->father_name ?: '-' }}
                        </span>
                    </div>

                    <!-- Mother -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);">Mother</span>
                        <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->mother_name ?: '-' }}
                        </span>
                    </div>

                    <!-- Spouse / Wife -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);">Spouse / Wife</span>
                        <span style="font-size: 0.88rem; font-weight: 800; color: #ec4899; text-align: right;">
                            {{ $player->spouse_name ?: '-' }}
                        </span>
                    </div>

                    <!-- Children -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);">Children</span>
                        <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->children ?: '-' }}
                        </span>
                    </div>

                    <!-- Siblings -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);">Siblings</span>
                        <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-main); text-align: right;">
                            {{ $player->siblings ?: '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Teammates Card -->
            @if($teammates && $teammates->isNotEmpty())
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                    <div style="font-size: 1rem; font-weight: 800; color: var(--text-main); margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px;">
                        <span>👥</span> Team Squad ({{ $player->team->name ?? 'Team' }})
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($teammates as $mate)
                            <a href="{{ route('player.profile', $mate->id) }}" style="text-decoration: none; background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; gap: 10px; transition: transform 0.15s, border-color 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.transform='translateX(3px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateX(0)';">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--bg-card); border: 1px solid #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #38bdf8; font-size: 0.8rem; flex-shrink: 0;">
                                    {{ strtoupper(substr($mate->name, 0, 2)) }}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 800; font-size: 0.84rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $mate->name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-dim);">{{ $mate->role ?? 'Player' }}</div>
                                </div>
                                <span style="color: var(--text-dim); font-size: 0.75rem;">&rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- ================= RIGHT COLUMN: Teams Played, Profile Bio & Stats ================= -->
        <div class="lg:col-span-8 space-y-7">
            
            <!-- Played for the Teams Section -->
            @php
                $teamsList = !empty($player->played_teams) ? array_values(array_filter(array_map('trim', explode(',', $player->played_teams)))) : ($player->team ? [$player->team->name] : []);
            @endphp
            @if(!empty($teamsList))
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                    <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>🛡️</span> Played for the Teams:
                    </h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($teamsList as $tm)
                            @if(!empty($tm))
                                <span style="background: var(--bg-card-secondary); color: var(--text-main); font-weight: 700; font-size: 0.84rem; padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <span style="color: #38bdf8;">•</span> {{ $tm }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Profile Narrative / Biography -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 26px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <h2 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin-top: 0; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <span>📖</span> Profile:
                </h2>
                
                <div style="font-size: 0.96rem; line-height: 1.8; color: var(--text-muted, #334155); font-weight: 500;">
                    @if(!empty($player->bio))
                        @foreach(preg_split("/\r\n|\n|\r/", $player->bio) as $para)
                            @if(trim($para) !== '')
                                <p style="margin-bottom: 16px;">{{ trim($para) }}</p>
                            @endif
                        @endforeach
                    @else
                        <p style="color: var(--text-dim); font-style: italic; margin-bottom: 0;">
                            No biography profile details recorded yet.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Career Statistics Dashboard -->
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>📊</span> Career Statistics &amp; Metrics
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- BATTING STATS CARD -->
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                                <span>🏏</span> Batting Record
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">All Formats</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center">
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">TOTAL RUNS</span>
                                <strong style="font-size: 1.25rem; color: var(--text-main); font-weight: 900;">{{ number_format($stats['runs'] ?? 0) }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">BALLS FACED</span>
                                <strong style="font-size: 1.25rem; color: #38bdf8; font-weight: 900;">{{ number_format($stats['balls'] ?? 0) }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">HIGHEST SCORE</span>
                                <strong style="font-size: 1.25rem; color: #f59e0b; font-weight: 900;">{{ $stats['highest'] ?? $stats['highestScore'] ?? 0 }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">STRIKE RATE</span>
                                <strong style="font-size: 1.15rem; color: #22c55e; font-weight: 800;">{{ $stats['strike_rate'] ?? $stats['strikeRate'] ?? '0.00' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">AVERAGE</span>
                                <strong style="font-size: 1.15rem; color: var(--text-main); font-weight: 800;">{{ $stats['average'] ?? '0.00' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">FOURS (4s)</span>
                                <strong style="font-size: 1.15rem; color: #10b981; font-weight: 800;">{{ number_format($stats['fours'] ?? 0) }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">SIXES (6s)</span>
                                <strong style="font-size: 1.15rem; color: #8b5cf6; font-weight: 800;">{{ number_format($stats['sixes'] ?? 0) }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">50s / 100s</span>
                                <strong style="font-size: 1.15rem; color: var(--text-main); font-weight: 800;">{{ $stats['fifties'] ?? 0 }} / {{ $stats['hundreds'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- BOWLING STATS CARD -->
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: #f59e0b; display: flex; align-items: center; gap: 6px;">
                                <span>🎯</span> Bowling Record
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">All Formats</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-center">
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">WICKETS</span>
                                <strong style="font-size: 1.25rem; color: var(--text-main); font-weight: 900;">{{ number_format($stats['wickets'] ?? 0) }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">BEST BOWLING</span>
                                <strong style="font-size: 1.25rem; color: #f59e0b; font-weight: 900;">{{ $stats['best_bowling'] ?? $stats['bestBowling'] ?? '-' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">ECONOMY</span>
                                <strong style="font-size: 1.25rem; color: var(--text-main); font-weight: 900;">{{ $stats['economy'] ?? '0.00' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">OVERS BOWLED</span>
                                <strong style="font-size: 1.15rem; color: var(--text-main); font-weight: 800;">{{ $stats['overs'] ?? '0.0' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">BOWLING AVG</span>
                                <strong style="font-size: 1.15rem; color: var(--text-main); font-weight: 800;">{{ $stats['bowlingAvg'] ?? '-' }}</strong>
                            </div>
                            <div style="background: var(--bg-card-secondary); padding: 10px 8px; border-radius: 8px;">
                                <span style="font-size: 0.68rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">MAIDENS</span>
                                <strong style="font-size: 1.15rem; color: var(--text-main); font-weight: 800;">{{ $stats['maidens'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ICC RANKINGS CARD (Inside Right Column, directly under Career Statistics) -->
            @php
                $rankingTabs = [
                    'batting' => 'Batting',
                    'bowling' => 'Bowling',
                    'all_rounder' => 'All-Rounder',
                ];
                $rankingFormats = [
                    'test' => 'Test',
                    'odi'  => 'ODI',
                    't20i' => 'T20I',
                ];
                $iccData = $player->normalized_icc_rankings;
            @endphp
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <!-- Card Header with subtle dashed divider line -->
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <h3 style="font-size: 1.05rem; font-weight: 900; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-main); margin: 0; white-space: nowrap;">
                        ICC RANKINGS
                    </h3>
                    <div style="flex: 1; border-bottom: 1px dashed var(--border-color); opacity: 0.6;"></div>
                </div>

                <!-- Segmented Tab Pills: Batting | Bowling | All-Rounder (Loop Engineering) -->
                <div style="display: flex; background: var(--bg-card-secondary, #f1f5f9); padding: 4px; border-radius: 12px; margin-bottom: 18px; gap: 4px; max-width: 380px;">
                    @foreach($rankingTabs as $catKey => $catLabel)
                        <button type="button"
                            onclick="switchIccRankingTab('{{ $catKey }}')"
                            id="icc-tab-btn-{{ $catKey }}"
                            class="icc-ranking-tab-btn"
                            data-tab="{{ $catKey }}"
                            style="flex: 1; padding: 7px 10px; border-radius: 9px; font-size: 0.85rem; font-weight: {{ $loop->first ? '800' : '600' }}; border: none; cursor: pointer; transition: all 0.2s ease; {{ $loop->first ? 'background: var(--bg-card, #ffffff); color: var(--text-main); box-shadow: 0 2px 6px rgba(0,0,0,0.08);' : 'background: transparent; color: var(--text-dim);' }}">
                            {{ $catLabel }}
                        </button>
                    @endforeach
                </div>

                <!-- Tab Content Panes (Loop Engineering) -->
                @foreach($rankingTabs as $catKey => $catLabel)
                    <div id="icc-pane-{{ $catKey }}" class="icc-ranking-pane" style="display: {{ $loop->first ? 'block' : 'none' }};">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <th style="text-align: left; padding: 8px 10px 12px 10px; font-weight: 600; color: var(--text-dim); font-size: 0.85rem;">
                                        Format
                                    </th>
                                    <th style="text-align: center; padding: 8px 10px 12px 10px; font-weight: 600; color: var(--text-dim); font-size: 0.85rem;">
                                        Current Rank
                                    </th>
                                    <th style="text-align: center; padding: 8px 10px 12px 10px; font-weight: 600; color: var(--text-dim); font-size: 0.85rem;">
                                        Best Rank
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankingFormats as $fmtKey => $fmtLabel)
                                    @php
                                        $curr = $iccData[$catKey][$fmtKey]['current'] ?? '--';
                                        $best = $iccData[$catKey][$fmtKey]['best'] ?? '--';
                                        $isRankedCurr = ($curr !== '--' && trim($curr) !== '');
                                        $isRankedBest = ($best !== '--' && trim($best) !== '');
                                    @endphp
                                    <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                                        <td style="padding: 13px 10px; font-weight: 700; color: var(--text-main);">
                                            {{ $fmtLabel }}
                                        </td>
                                        <td style="padding: 13px 10px; text-align: center; {{ $isRankedCurr ? 'font-weight: 900; color: var(--text-main); font-size: 0.98rem;' : 'font-weight: 600; color: var(--text-dim);' }}">
                                            {{ $curr }}
                                        </td>
                                        <td style="padding: 13px 10px; text-align: center; {{ $isRankedBest ? 'font-weight: 700; color: var(--text-main); font-size: 0.95rem;' : 'font-weight: 600; color: var(--text-dim);' }}">
                                            {{ $best }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

    <script>
        function switchIccRankingTab(activeKey) {
            document.querySelectorAll('.icc-ranking-tab-btn').forEach(btn => {
                const isTarget = btn.getAttribute('data-tab') === activeKey;
                if (isTarget) {
                    btn.style.background = 'var(--bg-card, #ffffff)';
                    btn.style.color = 'var(--text-main)';
                    btn.style.fontWeight = '800';
                    btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = 'var(--text-dim)';
                    btn.style.fontWeight = '600';
                    btn.style.boxShadow = 'none';
                }
            });

            document.querySelectorAll('.icc-ranking-pane').forEach(pane => {
                pane.style.display = 'none';
            });
            const targetPane = document.getElementById('icc-pane-' + activeKey);
            if (targetPane) {
                targetPane.style.display = 'block';
            }
        }
    </script>

    <!-- ================= BOTTOM SECTION: Articles (No top border line) ================= -->
    <div style="margin-top: 36px; padding-top: 10px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
            <div>
                <h2 style="font-size: 1.45rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.01em;">
                    Article
                </h2>
            </div>

            <a href="{{ route('news', ['type' => 'article']) }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 800; color: #38bdf8; text-decoration: none;">
                View All Articles &rarr;
            </a>
        </div>

        @if(isset($articles) && $articles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles->take(6) as $art)
                    <article style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 16px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 24px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.02)';">
                        
                        <!-- Article Thumbnail -->
                        <div style="position: relative; width: 100%; height: 180px; background: var(--bg-card-secondary); overflow: hidden;">
                            @if(!empty($art->image_url))
                                <img src="{{ $art->image_url }}" alt="{{ $art->title }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;background:linear-gradient(135deg,#0f172a,#1e293b);color:#38bdf8;\'>🏏</div>';">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; background: linear-gradient(135deg, #0f172a, #1e293b); color: #38bdf8;">
                                    🏏
                                </div>
                            @endif
                            @if(!empty($art->category))
                                <div style="position: absolute; top: 10px; left: 10px;">
                                    <span style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); color: #38bdf8; font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; border: 1px solid rgba(56, 189, 248, 0.3);">
                                        {{ $art->category }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Article Content -->
                        <div style="padding: 18px; display: flex; flex-direction: column; flex: 1;">
                            <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600; margin-bottom: 8px;">
                                ⏱️ {{ $art->read_time ?: '3 MIN READ' }} &bull; {{ $art->published_date ? \Carbon\Carbon::parse($art->published_date)->format('M d, Y') : ($art->created_at ? \Carbon\Carbon::parse($art->created_at)->format('M d, Y') : '') }}
                            </div>

                            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); line-height: 1.4; margin: 0 0 10px 0; flex: 1;">
                                <a href="{{ route('article.show', $art->slug ?: $art->id) }}" style="color: inherit; text-decoration: none;">
                                    {{ $art->title }}
                                </a>
                            </h3>

                            @if(!empty($art->summary))
                                <p style="font-size: 0.82rem; color: var(--text-dim); line-height: 1.5; margin: 0 0 16px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $art->summary }}
                                </p>
                            @endif

                            <div style="margin-top: auto; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                                <a href="{{ route('article.show', $art->slug ?: $art->id) }}" style="color: #38bdf8; font-weight: 800; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    Read Full Story &rarr;
                                </a>
                                @if(!empty($art->keywords))
                                    <span style="font-size: 0.72rem; color: var(--text-dim); max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        🏷️ {{ $art->keywords }}
                                    </span>
                                @endif
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>
        @else
            <!-- Empty State Fallback -->
            <div style="background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: 14px; padding: 36px 20px; text-align: center;">
                <div style="font-size: 2.2rem; margin-bottom: 8px;">📰</div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0;">
                    No article found
                </h3>
            </div>
        @endif
    </div>

</main>
@endsection
