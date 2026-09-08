@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 24px 24px 80px; width: 100%; box-sizing: border-box; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Section Header (Clean & Simple, No Loud Highlights) -->
    <div style="text-align: center; margin-bottom: 20px; width: 100%;">
        <h1 style="font-size: clamp(1.35rem, 4vw, 1.85rem); font-weight: 800; color: var(--text-main); margin: 0 0 6px 0; letter-spacing: -0.01em; word-break: break-word;">
            Player Head-to-Head Comparison
        </h1>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
            Compare 2, 3, or 4 players career statistics, visual scoring distribution, and bowling metrics
        </p>
    </div>

    <!-- Player Count Switcher (2 Players / 3 Players / 4 Players) -->
    <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        @php
            $q2 = ['count' => 2, 'p1' => $p1->id ?? '', 'p2' => $p2->id ?? ''];
            $q3 = ['count' => 3, 'p1' => $p1->id ?? '', 'p2' => $p2->id ?? '', 'p3' => $p3->id ?? ($allPlayers->count() > 2 ? $allPlayers->skip(2)->first()->id : '')];
            $q4 = ['count' => 4, 'p1' => $p1->id ?? '', 'p2' => $p2->id ?? '', 'p3' => $p3->id ?? ($allPlayers->count() > 2 ? $allPlayers->skip(2)->first()->id : ''), 'p4' => $p4->id ?? ($allPlayers->count() > 3 ? $allPlayers->skip(3)->first()->id : '')];
        @endphp
        <a href="{{ route('compare', $q2) }}" class="series-tab {{ $count == 2 ? 'active' : '' }}" style="text-decoration: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem;">
            2 Players
        </a>
        <a href="{{ route('compare', $q3) }}" class="series-tab {{ $count == 3 ? 'active' : '' }}" style="text-decoration: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem;">
            3 Players
        </a>
        <a href="{{ route('compare', $q4) }}" class="series-tab {{ $count == 4 ? 'active' : '' }}" style="text-decoration: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem;">
            4 Players
        </a>
    </div>

    <!-- Player Selector Form Bar -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; margin-bottom: 24px; box-sizing: border-box; width: 100%;">
        <form method="GET" action="{{ route('compare') }}">
            <input type="hidden" name="count" value="{{ $count }}">
            
            <div style="display: grid; grid-template-columns: repeat({{ $count }}, 1fr) auto; gap: 14px; align-items: flex-end;" class="compare-selectors-grid">
                
                <!-- Player 1 Selector -->
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.04em;">
                        Player 1
                    </label>
                    <select name="p1" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 600; outline: none; cursor: pointer;">
                        @foreach($allPlayers as $pl)
                            <option value="{{ $pl->id }}" {{ $p1 && $p1->id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }} ({{ $pl->team->short_name ?? 'IND' }} • {{ $pl->role ?? 'Player' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Player 2 Selector -->
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.04em;">
                        Player 2
                    </label>
                    <select name="p2" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 600; outline: none; cursor: pointer;">
                        @foreach($allPlayers as $pl)
                            <option value="{{ $pl->id }}" {{ $p2 && $p2->id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }} ({{ $pl->team->short_name ?? 'IND' }} • {{ $pl->role ?? 'Player' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Player 3 Selector (If count >= 3) -->
                @if($count >= 3)
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #10b981; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.04em;">
                        Player 3
                    </label>
                    <select name="p3" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 600; outline: none; cursor: pointer;">
                        @foreach($allPlayers as $pl)
                            <option value="{{ $pl->id }}" {{ $p3 && $p3->id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }} ({{ $pl->team->short_name ?? 'IND' }} • {{ $pl->role ?? 'Player' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Player 4 Selector (If count >= 4) -->
                @if($count >= 4)
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #ec4899; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.04em;">
                        Player 4
                    </label>
                    <select name="p4" onchange="this.form.submit()" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 600; outline: none; cursor: pointer;">
                        @foreach($allPlayers as $pl)
                            <option value="{{ $pl->id }}" {{ $p4 && $p4->id == $pl->id ? 'selected' : '' }}>
                                {{ $pl->name }} ({{ $pl->team->short_name ?? 'IND' }} • {{ $pl->role ?? 'Player' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Compare Submit Button -->
                <div>
                    <button type="submit" class="compare-submit-btn" style="background: #0284c7; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; white-space: nowrap; height: 42px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <span>Compare</span> <span>⚡</span>
                    </button>
                </div>

            </div>
        </form>
    </div>

    @if(!empty($playersList))

    <!-- OVERVIEW BANNER (When comparing 3 or 4 players) -->
    @if($count > 2 && !empty($verdicts['highlights']))
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px 22px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); letter-spacing: 0.5px;">
                        MULTI-PLAYER COMPARISON VERDICT
                    </span>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 2px 0 0 0;">
                        Overall Leader: <span style="color: {{ $verdicts['overallColor'] ?? '#38bdf8' }};">{{ $verdicts['overallWinner'] ?? 'Evenly Matched' }}</span>
                    </h3>
                </div>
                <div style="font-size: 0.82rem; color: var(--text-dim);">
                    Comparing {{ $count }} players across career milestones
                </div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @foreach($verdicts['highlights'] as $item)
                    <div style="background: var(--bg-card-secondary); padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; display: flex; align-items: center; gap: 6px;">
                        <span style="color: var(--text-dim);">{{ $item['metric'] }}:</span>
                        <strong style="color: {{ $item['color'] }};">{{ $item['winner'] }} ({{ $item['value'] }})</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- HERO CARDS SHOWCASE -->
    @if($count == 2)
        <!-- 2-PLAYER LAYOUT: Card 1 - Middle Head to Head - Card 2 -->
        <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr; gap: 16px; margin-bottom: 28px;" class="player-cards-grid">
            
            <!-- Player 1 Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800; color: #38bdf8; margin: 0 auto 12px;">
                    {{ strtoupper(substr($p1->name, 0, 2)) }}
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">{{ $p1->name }}</h2>
                <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <span style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 6px;">{{ $p1->role ?? 'Player' }}</span>
                    <span style="background: var(--bg-card-secondary); color: var(--text-dim); font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 6px;">{{ $p1->team->name ?? 'Team' }}</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: left; background: var(--bg-card-secondary); padding: 10px 12px; border-radius: 8px; font-size: 0.8rem;">
                    <div><span style="color: var(--text-dim); font-size: 0.7rem; display: block; font-weight: 600;">BATTING</span><strong style="color: var(--text-main);">{{ $p1->batting_style ?? 'Right-hand bat' }}</strong></div>
                    <div><span style="color: var(--text-dim); font-size: 0.7rem; display: block; font-weight: 600;">BOWLING</span><strong style="color: var(--text-main);">{{ $p1->bowling_style ?? 'Right-arm' }}</strong></div>
                </div>
            </div>

            <!-- Middle Card: Head-to-Head Overview -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="text-align: center; margin-bottom: 14px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-dim);">Head-to-Head Overview</span>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 4px 0 2px 0;">
                            {{ $verdicts['overallWinner'] ?? 'Overview' }}
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Holds statistical advantage in overall head-to-head metrics</p>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @forelse($verdicts['highlights'] ?? [] as $item)
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-card-secondary); padding: 7px 12px; border-radius: 6px; font-size: 0.82rem;">
                                <span style="color: var(--text-dim); font-weight: 600;">{{ $item['metric'] }}</span>
                                <span style="color: {{ $item['color'] }}; font-weight: 700;">
                                    {{ $item['winner'] }} <span style="color: var(--text-muted); font-size: 0.75rem; font-weight: 500;">({{ $item['value'] }})</span>
                                </span>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-dim); font-size: 0.85rem; padding: 12px 0;">Players are evenly matched in major metrics.</div>
                        @endforelse
                    </div>
                </div>
                <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-dim);">
                    <span>{{ $p1->name }}: <strong style="color: var(--text-main);">{{ $verdicts['points'][1] ?? 0 }} pts</strong></span>
                    <span>{{ $p2->name }}: <strong style="color: var(--text-main);">{{ $verdicts['points'][2] ?? 0 }} pts</strong></span>
                </div>
            </div>

            <!-- Player 2 Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800; color: #f59e0b; margin: 0 auto 12px;">
                    {{ strtoupper(substr($p2->name, 0, 2)) }}
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">{{ $p2->name }}</h2>
                <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 6px;">{{ $p2->role ?? 'Player' }}</span>
                    <span style="background: var(--bg-card-secondary); color: var(--text-dim); font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 6px;">{{ $p2->team->name ?? 'Team' }}</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: left; background: var(--bg-card-secondary); padding: 10px 12px; border-radius: 8px; font-size: 0.8rem;">
                    <div><span style="color: var(--text-dim); font-size: 0.7rem; display: block; font-weight: 600;">BATTING</span><strong style="color: var(--text-main);">{{ $p2->batting_style ?? 'Right-hand bat' }}</strong></div>
                    <div><span style="color: var(--text-dim); font-size: 0.7rem; display: block; font-weight: 600;">BOWLING</span><strong style="color: var(--text-main);">{{ $p2->bowling_style ?? 'Right-arm' }}</strong></div>
                </div>
            </div>

        </div>
    @else
        <!-- 3 OR 4 PLAYERS CARD GRID -->
        <div style="display: grid; grid-template-columns: repeat({{ $count }}, 1fr); gap: 16px; margin-bottom: 28px;" class="player-cards-grid">
            @foreach($playersList as $item)
                @php $pl = $item['player']; @endphp
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="width: 58px; height: 58px; border-radius: 50%; background: var(--bg-card-secondary); border: 2px solid {{ $item['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; color: {{ $item['color'] }}; margin: 0 auto 10px;">
                        {{ strtoupper(substr($pl->name, 0, 2)) }}
                    </div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: {{ $item['color'] }}; text-transform: uppercase;">Player {{ $item['index'] }}</span>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 3px 0 6px 0;">{{ $pl->name }}</h3>
                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap; margin-bottom: 12px;">
                        <span style="background: rgba(56, 189, 248, 0.1); color: var(--text-main); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">{{ $pl->role ?? 'Player' }}</span>
                        <span style="background: var(--bg-card-secondary); color: var(--text-dim); font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 4px;">{{ $pl->team->short_name ?? 'Team' }}</span>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 8px 10px; border-radius: 6px; font-size: 0.76rem; text-align: left; display: flex; flex-direction: column; gap: 4px;">
                        <div><span style="color: var(--text-dim); font-size: 0.68rem; font-weight: 600;">BAT:</span> <strong style="color: var(--text-main);">{{ $pl->batting_style ?? 'Right-hand' }}</strong></div>
                        <div><span style="color: var(--text-dim); font-size: 0.68rem; font-weight: 600;">BOWL:</span> <strong style="color: var(--text-main);">{{ $pl->bowling_style ?? 'Right-arm' }}</strong></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- SIDE-BY-SIDE PIE / DONUT GRAPHS SECTION -->
    <div style="margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin: 0 0 2px 0;">
                    🥧 Career Scoring Distribution
                </h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                    Visual career breakdown of boundary runs vs running between wickets for all {{ $count }} players
                </p>
            </div>
            <div style="display: flex; gap: 14px; font-size: 0.8rem; font-weight: 600;">
                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    Fours (4s)
                </span>
                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #8b5cf6; display: inline-block;"></span>
                    Sixes (6s)
                </span>
                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #38bdf8; display: inline-block;"></span>
                    Running (1s, 2s)
                </span>
            </div>
        </div>

        <!-- Donut Graphs Grid (2, 3, or 4 side-by-side) -->
        <div style="display: grid; grid-template-columns: repeat({{ $count }}, 1fr); gap: 18px;" class="pie-graphs-grid">
            @foreach($playersList as $item)
                @php 
                    $pl = $item['player']; 
                    $st = $item['stats'];
                    $pF = $item['pFour'];
                    $pS = $item['pSix'];
                    $pR = $item['pRun'];
                @endphp
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div>
                                <h4 style="font-size: 1.02rem; font-weight: 800; color: var(--text-main); margin: 0;">{{ $pl->name }}</h4>
                                <span style="font-size: 0.74rem; color: var(--text-muted);">{{ $pl->team->name ?? 'Team' }}</span>
                            </div>
                            <span style="background: rgba(56, 189, 248, 0.1); color: {{ $item['color'] }}; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 14px;">
                                Player {{ $item['index'] }}
                            </span>
                        </div>

                        <!-- Circular Pie / Donut Chart (SVG) -->
                        <div style="position: relative; width: 155px; height: 155px; margin: 0 auto 16px;">
                            <svg viewBox="0 0 42 42" style="width: 100%; height: 100%; transform: rotate(-90deg); border-radius: 50%;">
                                <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="var(--bg-card-secondary)" stroke-width="4.8"></circle>
                                @if($item['totalRuns'] > 0)
                                    <!-- Fours (Emerald) -->
                                    <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#10b981" stroke-width="4.8" stroke-dasharray="{{ $pF }} {{ 100 - $pF }}" stroke-dashoffset="0"></circle>
                                    <!-- Sixes (Purple) -->
                                    <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#8b5cf6" stroke-width="4.8" stroke-dasharray="{{ $pS }} {{ 100 - $pS }}" stroke-dashoffset="-{{ $pF }}"></circle>
                                    <!-- Running (Sky Blue) -->
                                    <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#38bdf8" stroke-width="4.8" stroke-dasharray="{{ $pR }} {{ 100 - $pR }}" stroke-dashoffset="-{{ $pF + $pS }}"></circle>
                                @endif
                            </svg>
                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                                <span style="font-size: 1.55rem; font-weight: 800; color: var(--text-main); line-height: 1;">{{ $item['totalRuns'] }}</span>
                                <span style="font-size: 0.68rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-top: 3px;">Runs</span>
                            </div>
                        </div>

                        <!-- Legend Breakdown -->
                        <div style="background: var(--bg-card-secondary); border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 7px; font-size: 0.78rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                                    <span style="width: 8px; height: 8px; border-radius: 2px; background: #10b981;"></span> Fours
                                </span>
                                <strong style="color: var(--text-main);">{{ $item['fourRuns'] }} r <span style="color: var(--text-muted); font-weight: 500;">({{ $pF }}%)</span></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                                    <span style="width: 8px; height: 8px; border-radius: 2px; background: #8b5cf6;"></span> Sixes
                                </span>
                                <strong style="color: var(--text-main);">{{ $item['sixRuns'] }} r <span style="color: var(--text-muted); font-weight: 500;">({{ $pS }}%)</span></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="display: flex; align-items: center; gap: 5px; color: var(--text-dim);">
                                    <span style="width: 8px; height: 8px; border-radius: 2px; background: #38bdf8;"></span> Running
                                </span>
                                <strong style="color: var(--text-main);">{{ $item['runRuns'] }} r <span style="color: var(--text-muted); font-weight: 500;">({{ $pR }}%)</span></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Career Chips Footer -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; text-align: center; font-size: 0.72rem;">
                        <div style="background: var(--bg-card-secondary); padding: 5px 2px; border-radius: 5px;">
                            <span style="color: var(--text-muted); display: block; font-size: 0.62rem;">SR</span>
                            <strong style="color: var(--text-main);">{{ $st['strike_rate'] }}</strong>
                        </div>
                        <div style="background: var(--bg-card-secondary); padding: 5px 2px; border-radius: 5px;">
                            <span style="color: var(--text-muted); display: block; font-size: 0.62rem;">AVG</span>
                            <strong style="color: var(--text-main);">{{ $st['average'] }}</strong>
                        </div>
                        <div style="background: var(--bg-card-secondary); padding: 5px 2px; border-radius: 5px;">
                            <span style="color: var(--text-muted); display: block; font-size: 0.62rem;">WKTS</span>
                            <strong style="color: var(--text-main);">{{ $st['wickets'] }}</strong>
                        </div>
                        <div style="background: var(--bg-card-secondary); padding: 5px 2px; border-radius: 5px;">
                            <span style="color: var(--text-muted); display: block; font-size: 0.62rem;">ECON</span>
                            <strong style="color: var(--text-main);">{{ $st['economy'] > 0 ? $st['economy'] : '-' }}</strong>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    <!-- COMPLETE STATISTICAL BREAKDOWN TABLE (2, 3, or 4 columns) -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0 0 16px 0;">
            Detailed Career Matrix ({{ $count }} Players)
        </h3>

        <div class="table-responsive-wrapper">
            <table style="width: 100%; min-width: 500px; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 1.5px solid var(--border-color); color: var(--text-muted); font-size: 0.8rem;">
                        <th style="padding: 10px 14px; text-align: left; width: 22%; color: var(--text-dim); text-transform: uppercase; font-weight: 700;">
                            METRIC
                        </th>
                        @foreach($playersList as $item)
                            <th style="padding: 10px 14px; text-align: center; color: {{ $item['color'] }}; font-weight: 700;">
                                {{ $item['player']->name }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $metricsDef = [
                            ['key' => 'matches', 'label' => 'Matches Played', 'highGood' => true],
                            ['key' => 'runs', 'label' => 'Total Runs', 'highGood' => true],
                            ['key' => 'highest', 'label' => 'Highest Score', 'highGood' => true],
                            ['key' => 'average', 'label' => 'Batting Average', 'highGood' => true],
                            ['key' => 'strike_rate', 'label' => 'Strike Rate', 'highGood' => true],
                            ['key' => 'hundreds', 'label' => 'Centuries (100s)', 'highGood' => true],
                            ['key' => 'fifties', 'label' => 'Half-Centuries (50s)', 'highGood' => true],
                            ['key' => 'fours', 'label' => 'Fours (4s)', 'highGood' => true],
                            ['key' => 'sixes', 'label' => 'Sixes (6s)', 'highGood' => true],
                            ['key' => 'overs', 'label' => 'Overs Bowled', 'highGood' => true],
                            ['key' => 'wickets', 'label' => 'Wickets Taken', 'highGood' => true],
                            ['key' => 'economy', 'label' => 'Bowling Economy', 'highGood' => false],
                            ['key' => 'best_bowling', 'label' => 'Best Bowling', 'highGood' => null],
                        ];
                    @endphp

                    @foreach($metricsDef as $m)
                        @php
                            // Calculate best value across players for this metric
                            $bestVal = null;
                            if ($m['highGood'] === true) {
                                foreach ($playersList as $item) {
                                    $v = (float)($item['stats'][$m['key']] ?? 0);
                                    if ($bestVal === null || $v > $bestVal) {
                                        $bestVal = $v;
                                    }
                                }
                            } elseif ($m['highGood'] === false) {
                                foreach ($playersList as $item) {
                                    $v = (float)($item['stats'][$m['key']] ?? 0);
                                    if ($v > 0 && ($bestVal === null || $v < $bestVal)) {
                                        $bestVal = $v;
                                    }
                                }
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <!-- Metric Label -->
                            <td style="padding: 12px 14px; color: var(--text-dim); font-weight: 600; font-size: 0.82rem; text-align: left;">
                                {{ $m['label'] }}
                            </td>

                            <!-- Value per Player -->
                            @foreach($playersList as $item)
                                @php
                                    $val = $item['stats'][$m['key']] ?? '-';
                                    $isLeader = false;
                                    if ($bestVal !== null && is_numeric($val) && (float)$val == $bestVal && $bestVal > 0) {
                                        $isLeader = true;
                                    }
                                @endphp
                                <td style="padding: 12px 14px; text-align: center; font-weight: {{ $isLeader ? '800' : '600' }}; color: {{ $isLeader ? '#10b981' : 'var(--text-main)' }};">
                                    {{ $val }}
                                    @if($isLeader)
                                        <span style="color: #10b981; font-weight: 800; margin-left: 3px;">✓</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</main>

<style>
@media (max-width: 900px) {
    .compare-selectors-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
    }
    .player-cards-grid {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .pie-graphs-grid {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
}
@media (max-width: 640px) {
    .compare-selectors-grid {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }
    .compare-submit-btn {
        width: 100% !important;
    }
    .player-cards-grid,
    .pie-graphs-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
}
</style>
@endsection
