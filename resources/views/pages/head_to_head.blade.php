@extends('layouts.app')

@php
    $t1Name = $team1 ? $team1->name : 'Team 1';
    $t2Name = $team2 ? $team2->name : 'Team 2';
    $vsTitle = $t1Name . ' vs ' . $t2Name;
    $fLabel = $formatLabel ?? 'All Formats';

    $pageTitle = $vsTitle . ' Head to Head in ' . $fLabel . ' Records, Stats & Results | CricketKaScore';
    $metaDesc = $vsTitle . ' Head to Head in ' . $fLabel . ' cricket records, match results, win counts, home/away/neutral wins, highest scores, and recent form on CricketKaScore.';
    $metaKeywords = $vsTitle . ' head to head, ' . $t1Name . ' vs ' . $t2Name . ' ' . $fLabel . ' records, h2h cricket stats, cricket team compare, win percentage, CricketKaScore';
    $ogTitle = $vsTitle . ' Head to Head in ' . $fLabel . ' | CricketKaScore';
    $ogDesc = 'Explore ' . $vsTitle . ' head-to-head match records in ' . $fLabel . ', win-loss stats, home/away records, and recent face-offs on CricketKaScore.';
    $canonicalUrl = request()->fullUrl();
    $ogImage = $team1?->logo ?: asset('images/logo.png');

    $formatTabs = [
        'ALL' => 'All Formats',
        'T20' => 'T20',
        'T10' => 'T10',
        'TEST' => 'TEST',
        'ODI' => 'ODI',
        'T20WC' => 'T20 WC',
        'ODIWC' => 'ODI WC',
        'IPL' => 'IPL',
    ];
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'website')
@section('og_title', $ogTitle)
@section('og_description', $ogDesc)
@section('og_url', $canonicalUrl)
@section('og_image', $ogImage)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'SportsEvent',
    'name' => $vsTitle . ' Head to Head in ' . $fLabel,
    'url' => $canonicalUrl,
    'description' => $metaDesc,
    'competitor' => [
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t1Name,
            'image' => $team1?->logo
        ],
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t2Name,
            'image' => $team2?->logo
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<div class="h2h-page-wrapper" style="max-width: 1100px; margin: 0 auto; padding: 20px 16px 60px;">

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-dim); margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('home') }}" style="color: var(--text-dim); text-decoration: none; transition: color 0.15s;">Home</a>
        <span>&rsaquo;</span>
        <a href="{{ route('teams') }}" style="color: var(--text-dim); text-decoration: none; transition: color 0.15s;">Teams</a>
        <span>&rsaquo;</span>
        <a href="{{ route('head-to-head') }}" style="color: var(--text-dim); text-decoration: none; transition: color 0.15s;">Teams Head to Head</a>
        <span>&rsaquo;</span>
        <span style="color: #38bdf8; font-weight: 700;">{{ $vsTitle }} ({{ $fLabel }})</span>
    </nav>

    <!-- Header Section -->
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 1.65rem; font-weight: 900; color: var(--text-main); margin: 0 0 8px; letter-spacing: -0.02em;">
            {{ $vsTitle }} Head to Head In {{ $fLabel }}
        </h1>
        
        <!-- Format Tabs (Styled in Dark UI Theme) -->
        <div class="h2h-format-tabs-container" style="display: flex; align-items: center; gap: 6px; margin: 16px 0 14px; overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; padding-bottom: 4px;">
            @foreach($formatTabs as $fKey => $fTitle)
                @php $isActive = ($format === $fKey); @endphp
                <a href="{{ route('head-to-head', ['team1' => $team1?->id, 'team2' => $team2?->id, 'format' => $fKey]) }}" 
                   class="h2h-format-tab {{ $isActive ? 'active' : '' }}">
                    {{ $fTitle }}
                </a>
            @endforeach
        </div>

        <!-- Narrative Sentence (as shown in reference design) -->
        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; margin: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 16px;">
            {{ $t1Name }} and {{ $t2Name }} have faced each other in <strong style="color: #38bdf8;">{{ $stats['totalMatches'] }}</strong> matches in <strong style="color: var(--text-main);">{{ $fLabel }}</strong>. Out of these {{ $stats['totalMatches'] }} games, <strong style="color: #22c55e;">{{ $t1Name }}</strong> have won <strong style="color: #22c55e;">{{ $stats['t1Wins'] }}</strong> whereas <strong style="color: #60a5fa;">{{ $t2Name }}</strong> have come out victorious on <strong style="color: #60a5fa;">{{ $stats['t2Wins'] }}</strong> occasions{{ $stats['noResults'] > 0 ? ', with ' . $stats['noResults'] . ' matches ending without a result' : '' }}{{ $stats['ties'] > 0 ? ', and ' . $stats['ties'] . ' tied' : '' }}.
        </p>
    </div>

    <!-- Main Comparison Section (Matching Reference Theme & Layout) -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px 20px 28px; margin-bottom: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
        
        <!-- Teams Dropdown Header Form with Custom Searchable Select (No OS Popups) -->
        <form method="GET" action="{{ route('head-to-head') }}" id="h2hForm">
            <input type="hidden" name="format" value="{{ $format }}">
            <input type="hidden" name="team1" id="team1HiddenInput" value="{{ $team1?->id }}">
            <input type="hidden" name="team2" id="team2HiddenInput" value="{{ $team2?->id }}">

            <div class="h2h-teams-header-grid">
                
                <!-- Team 1 Custom Dropdown -->
                <div class="h2h-custom-dropdown" id="team1DropdownWrap">
                    <button type="button" class="h2h-dropdown-trigger team1-trigger" onclick="toggleH2HDropdown('team1DropdownMenu')">
                        <div class="h2h-trigger-team-info">
                            <div class="h2h-team-logo-circle">
                                @if(!empty($team1?->logo_url ?? $team1?->logo))
                                    <img src="{{ $team1->logo_url ?? $team1->logo }}" alt="{{ $t1Name }}">
                                @else
                                    <span>{{ strtoupper(substr($t1Name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <span class="h2h-trigger-name" id="team1SelectedName">{{ $t1Name }}</span>
                        </div>
                        <span class="h2h-dropdown-chevron">▾</span>
                    </button>

                    <!-- Custom Floating Menu (Contained & Scrollable) -->
                    <div class="h2h-dropdown-menu" id="team1DropdownMenu" style="display: none;">
                        <div class="h2h-dropdown-search-wrap">
                            <input type="text" class="h2h-search-box" oninput="filterH2HTeams(this, 'team1List')">
                        </div>
                        <div class="h2h-options-list" id="team1List">
                            @foreach($allTeams as $t)
                                @php $isSelected = ($team1 && $team1->id == $t->id); @endphp
                                <div class="h2h-option-item {{ $isSelected ? 'selected' : '' }}" 
                                     data-name="{{ strtolower($t->name) }}"
                                     onclick="selectH2HTeam('team1', '{{ $t->id }}', '{{ addslashes($t->name) }}')">
                                    <div class="h2h-option-logo">
                                        @if(!empty($t->logo_url ?? $t->logo))
                                            <img src="{{ $t->logo_url ?? $t->logo }}" alt="{{ $t->name }}">
                                        @else
                                            <span>{{ strtoupper(substr($t->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <span class="h2h-option-name">{{ $t->name }}</span>
                                    @if($isSelected)
                                        <span class="h2h-option-check">✓</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Center 3D Cricket Ball / VS Icon -->
                <div style="display: flex; align-items: center; justify-content: center;">
                    <div class="h2h-vs-ball" onclick="swapTeams()" title="Click to Swap Teams">
                        <span>VS</span>
                    </div>
                </div>

                <!-- Team 2 Custom Dropdown -->
                <div class="h2h-custom-dropdown" id="team2DropdownWrap">
                    <button type="button" class="h2h-dropdown-trigger team2-trigger" onclick="toggleH2HDropdown('team2DropdownMenu')">
                        <div class="h2h-trigger-team-info">
                            <div class="h2h-team-logo-circle" style="border-color: #60a5fa; background: rgba(96, 165, 250, 0.15); color: #60a5fa;">
                                @if(!empty($team2?->logo_url ?? $team2?->logo))
                                    <img src="{{ $team2->logo_url ?? $team2->logo }}" alt="{{ $t2Name }}">
                                @else
                                    <span>{{ strtoupper(substr($t2Name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <span class="h2h-trigger-name" id="team2SelectedName">{{ $t2Name }}</span>
                        </div>
                        <span class="h2h-dropdown-chevron">▾</span>
                    </button>

                    <!-- Custom Floating Menu (Contained & Scrollable) -->
                    <div class="h2h-dropdown-menu" id="team2DropdownMenu" style="display: none;">
                        <div class="h2h-dropdown-search-wrap">
                            <input type="text" class="h2h-search-box" oninput="filterH2HTeams(this, 'team2List')">
                        </div>
                        <div class="h2h-options-list" id="team2List">
                            @foreach($allTeams as $t)
                                @php $isSelected = ($team2 && $team2->id == $t->id); @endphp
                                <div class="h2h-option-item {{ $isSelected ? 'selected' : '' }}" 
                                     data-name="{{ strtolower($t->name) }}"
                                     onclick="selectH2HTeam('team2', '{{ $t->id }}', '{{ addslashes($t->name) }}')">
                                    <div class="h2h-option-logo" style="color: #60a5fa; background: rgba(96, 165, 250, 0.15);">
                                        @if(!empty($t->logo_url ?? $t->logo))
                                            <img src="{{ $t->logo_url ?? $t->logo }}" alt="{{ $t->name }}">
                                        @else
                                            <span>{{ strtoupper(substr($t->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <span class="h2h-option-name">{{ $t->name }}</span>
                                    @if($isSelected)
                                        <span class="h2h-option-check">✓</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <!-- Center Matches Badge with Connecting Lines -->
        <div class="h2h-matches-badge-wrap" style="position: relative; display: flex; align-items: center; justify-content: center; margin: 10px 0 28px;">
            <div class="h2h-connector-line" style="position: absolute; left: 8%; right: 8%; height: 2px; background: linear-gradient(90deg, transparent, var(--border-color) 20%, var(--border-color) 80%, transparent); z-index: 1;">
                <span style="position: absolute; left: 15%; top: -4px; width: 10px; height: 10px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 8px #38bdf8;"></span>
                <span style="position: absolute; right: 15%; top: -4px; width: 10px; height: 10px; border-radius: 50%; background: #60a5fa; box-shadow: 0 0 8px #60a5fa;"></span>
            </div>
            
            <div style="position: relative; z-index: 2; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); border-radius: 12px; padding: 8px 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
                <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
                    Matches
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #ef4444; line-height: 1;">
                    {{ $stats['totalMatches'] }}
                </div>
            </div>
        </div>

        @php
            // Helper function for computing percentage bars safely
            $calcBarPct = function($val1, $val2) {
                $v1 = (float) $val1;
                $v2 = (float) $val2;
                $sum = $v1 + $v2;
                if ($sum <= 0) return [0, 0];
                return [
                    round(($v1 / $sum) * 100),
                    round(($v2 / $sum) * 100)
                ];
            };

            $metricRows = [
                [
                    'label' => 'Won',
                    't1' => $stats['t1Wins'],
                    't2' => $stats['t2Wins'],
                    'barColor' => '#22c55e', // Green for wins
                ],
                [
                    'label' => 'Lost',
                    't1' => $stats['t1Lost'],
                    't2' => $stats['t2Lost'],
                    'barColor' => '#ef4444', // Red for losses
                ],
                [
                    'label' => 'No Result',
                    't1' => $stats['noResults'],
                    't2' => $stats['noResults'],
                    'barColor' => '#94a3b8',
                ],
                [
                    'label' => 'Tied',
                    't1' => $stats['ties'],
                    't2' => $stats['ties'],
                    'barColor' => '#f59e0b',
                ],
                [
                    'label' => 'Home Won',
                    't1' => $stats['t1HomeWins'],
                    't2' => $stats['t2HomeWins'],
                    'barColor' => '#38bdf8',
                ],
                [
                    'label' => 'Away Won',
                    't1' => $stats['t1AwayWins'],
                    't2' => $stats['t2AwayWins'],
                    'barColor' => '#60a5fa',
                ],
                [
                    'label' => 'Neutral Won',
                    't1' => $stats['t1NeutralWins'],
                    't2' => $stats['t2NeutralWins'],
                    'barColor' => '#a78bfa',
                ],
                [
                    'label' => 'Won Batting 1st',
                    't1' => $stats['t1Bat1stWins'],
                    't2' => $stats['t2Bat1stWins'],
                    'barColor' => '#0284c7',
                ],
                [
                    'label' => 'Won Chasing (2nd)',
                    't1' => $stats['t1ChaseWins'],
                    't2' => $stats['t2ChaseWins'],
                    'barColor' => '#3b82f6',
                ],
                [
                    'label' => 'Highest Score',
                    't1' => $stats['t1Highest'] ?: '--',
                    't2' => $stats['t2Highest'] ?: '--',
                    'barColor' => '#10b981',
                ],
                [
                    'label' => 'Lowest Score',
                    't1' => $stats['t1Lowest'] ?: '--',
                    't2' => $stats['t2Lowest'] ?: '--',
                    'barColor' => '#f43f5e',
                ],
                [
                    'label' => 'Average Score',
                    't1' => $stats['t1Average'] ?: '--',
                    't2' => $stats['t2Average'] ?: '--',
                    'barColor' => '#06b6d4',
                ],
            ];
        @endphp

        <!-- Comparison Metric Rows List (Exact Bilateral Bar Layout) -->
        <div class="h2h-metrics-list" style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($metricRows as $row)
                @php
                    $v1 = is_numeric($row['t1']) ? (float)$row['t1'] : 0;
                    $v2 = is_numeric($row['t2']) ? (float)$row['t2'] : 0;
                    [$pct1, $pct2] = $calcBarPct($v1, $v2);
                    $bColor = $row['barColor'] ?? '#38bdf8';
                @endphp
                <div class="h2h-metric-row">
                    <!-- Left Value Box (Team 1) -->
                    <div class="h2h-stat-box left-box">
                        {{ $row['t1'] }}
                    </div>

                    <!-- Center Bar & Label -->
                    <div class="h2h-center-bar-col">
                        <div class="h2h-metric-title">
                            {{ $row['label'] }}
                        </div>

                        <div class="h2h-bar-track-wrap">
                            <!-- Left Bar (fills right-to-left towards dot) -->
                            <div class="h2h-bar-track left-track">
                                <div class="h2h-bar-fill left-fill" style="width: {{ $pct1 }}%; background: {{ $bColor }};"></div>
                            </div>

                            <!-- Center Dot -->
                            <div class="h2h-bar-dot"></div>

                            <!-- Right Bar (fills left-to-right from dot) -->
                            <div class="h2h-bar-track right-track">
                                <div class="h2h-bar-fill right-fill" style="width: {{ $pct2 }}%; background: {{ $bColor }};"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Value Box (Team 2) -->
                    <div class="h2h-stat-box right-box">
                        {{ $row['t2'] }}
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- 2-Column Section: Dominance Verdict & Recent Form -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;" class="h2h-summary-grid">
        
        <!-- Left: Dominance Verdict -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
            <div style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>👑</span> Head to Head Verdict ({{ $fLabel }})
            </div>

            @if($stats['totalMatches'] > 0)
                @if($stats['t1Wins'] > $stats['t2Wins'])
                    <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 0.92rem; font-weight: 800; color: #38bdf8; margin-bottom: 6px;">
                            🏆 {{ $team1->name }} Dominance Record
                        </div>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                            {{ $team1->name }} holds the statistical lead over {{ $team2->name }} in {{ $fLabel }} with <strong>{{ $stats['t1Wins'] }}</strong> wins (<strong>{{ $stats['t1WinPct'] }}%</strong> win rate) out of <strong>{{ $stats['totalMatches'] }}</strong> played matches.
                        </p>
                    </div>
                @elseif($stats['t2Wins'] > $stats['t1Wins'])
                    <div style="background: rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.25); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 0.92rem; font-weight: 800; color: #60a5fa; margin-bottom: 6px;">
                            🏆 {{ $team2->name }} Dominance Record
                        </div>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                            {{ $team2->name }} holds the statistical lead over {{ $team1->name }} in {{ $fLabel }} with <strong>{{ $stats['t2Wins'] }}</strong> wins (<strong>{{ $stats['t2WinPct'] }}%</strong> win rate) out of <strong>{{ $stats['totalMatches'] }}</strong> played matches.
                        </p>
                    </div>
                @else
                    <div style="background: rgba(148, 163, 184, 0.08); border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 0.92rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">
                            ⚖️ Perfectly Balanced Rivalry
                        </div>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                            Both {{ $team1->name }} and {{ $team2->name }} are tied with <strong>{{ $stats['t1Wins'] }}</strong> wins each in {{ $fLabel }}.
                        </p>
                    </div>
                @endif

                <!-- Progress percentage bar -->
                <div style="margin-top: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 800; margin-bottom: 6px;">
                        <span style="color: #38bdf8;">{{ $team1->name }}: {{ $stats['t1WinPct'] }}%</span>
                        <span style="color: #60a5fa;">{{ $team2->name }}: {{ $stats['t2WinPct'] }}%</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.06); border-radius: 10px; overflow: hidden; display: flex;">
                        <div style="width: {{ $stats['t1WinPct'] }}%; background: #38bdf8; transition: width 0.5s ease;"></div>
                        <div style="width: {{ $stats['t2WinPct'] }}%; background: #60a5fa; transition: width 0.5s ease;"></div>
                    </div>
                </div>
            @else
                <div style="background: var(--bg-card-secondary); border: 1px dashed var(--border-color); border-radius: 10px; padding: 16px; text-align: center;">
                    <span style="font-size: 0.82rem; color: var(--text-muted);">No completed {{ $fLabel }} matches recorded between these two teams.</span>
                </div>
            @endif
        </div>

        <!-- Right: Recent Form Guide (Last 5 matches) -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <div style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <span>⚡</span> Recent Form (Last 5)
                </div>
                <span style="font-size: 0.72rem; color: var(--text-dim);">W=Win &bull; L=Loss &bull; T=Tie</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- Team 1 Form -->
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #38bdf8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px;">
                        {{ $team1->name }}
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        @if(!empty($stats['recentFormT1']))
                            @foreach($stats['recentFormT1'] as $res)
                                @php
                                    $bBg = match($res) {
                                        'W' => 'rgba(34, 197, 94, 0.18)',
                                        'L' => 'rgba(239, 68, 68, 0.18)',
                                        'T' => 'rgba(245, 158, 11, 0.18)',
                                        default => 'rgba(148, 163, 184, 0.18)'
                                    };
                                    $bTxt = match($res) {
                                        'W' => '#22c55e',
                                        'L' => '#ef4444',
                                        'T' => '#f59e0b',
                                        default => '#94a3b8'
                                    };
                                @endphp
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; font-size: 0.75rem; font-weight: 900; background: {{ $bBg }}; color: {{ $bTxt }}; border: 1px solid {{ $bTxt }}40;">
                                    {{ $res }}
                                </span>
                            @endforeach
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-dim);">No recent data</span>
                        @endif
                    </div>
                </div>

                <!-- Team 2 Form -->
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #60a5fa; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px;">
                        {{ $team2->name }}
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        @if(!empty($stats['recentFormT2']))
                            @foreach($stats['recentFormT2'] as $res)
                                @php
                                    $bBg = match($res) {
                                        'W' => 'rgba(34, 197, 94, 0.18)',
                                        'L' => 'rgba(239, 68, 68, 0.18)',
                                        'T' => 'rgba(245, 158, 11, 0.18)',
                                        default => 'rgba(148, 163, 184, 0.18)'
                                    };
                                    $bTxt = match($res) {
                                        'W' => '#22c55e',
                                        'L' => '#ef4444',
                                        'T' => '#f59e0b',
                                        default => '#94a3b8'
                                    };
                                @endphp
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; font-size: 0.75rem; font-weight: 900; background: {{ $bBg }}; color: {{ $bTxt }}; border: 1px solid {{ $bTxt }}40;">
                                    {{ $res }}
                                </span>
                            @endforeach
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-dim);">No recent data</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Head to Head Face-off Match Cards -->
    <div style="margin-bottom: 36px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                <span>🏟️</span> Recent {{ $vsTitle }} Encounters ({{ $fLabel }})
            </div>
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-dim);">
                {{ $matches->count() }} face-offs found
            </span>
        </div>

        @if($matches->isNotEmpty())
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
                @foreach($matches as $m)
                    @php
                        $isT1Win = ($m->winner_team_id == $team1->id);
                        $isT2Win = ($m->winner_team_id == $team2->id);
                        if (!$isT1Win && !$isT2Win) {
                            $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($m->team1_score ?? '0'))[0] ?? '0');
                            $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($m->team2_score ?? '0'))[0] ?? '0');
                            if ($s1 > $s2 && $s1 > 0) $isT1Win = ($m->team1_id == $team1->id);
                            elseif ($s2 > $s1 && $s2 > 0) $isT2Win = ($m->team2_id == $team2->id);
                        }
                    @endphp
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: transform 0.15s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 10px;">
                            <span style="font-weight: 700; color: #38bdf8;">{{ $m->tournament ? $m->tournament->name : 'Match' }} &bull; {{ strtoupper($m->match_type ?: $fLabel) }}</span>
                            <span>{{ $m->match_date ? date('d M Y', strtotime($m->match_date)) : 'Recent' }}</span>
                        </div>

                        <!-- Team 1 Score Row -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <div style="font-weight: 800; font-size: 0.92rem; color: {{ $isT1Win ? '#38bdf8' : 'var(--text-main)' }};">
                                {{ $m->team1 ? $m->team1->name : $team1->name }}
                            </div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">
                                {{ $m->team1_score ?: '--' }}
                                @if(!empty($m->team1_overs))
                                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-dim);">({{ $m->team1_overs }} ov)</span>
                                @endif
                            </div>
                        </div>

                        <!-- Team 2 Score Row -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <div style="font-weight: 800; font-size: 0.92rem; color: {{ $isT2Win ? '#60a5fa' : 'var(--text-main)' }};">
                                {{ $m->team2 ? $m->team2->name : $team2->name }}
                            </div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">
                                {{ $m->team2_score ?: '--' }}
                                @if(!empty($m->team2_overs))
                                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-dim);">({{ $m->team2_overs }} ov)</span>
                                @endif
                            </div>
                        </div>

                        <!-- Match Result Badge -->
                        <div style="font-size: 0.78rem; font-weight: 800; padding: 5px 10px; border-radius: 6px; {{ $isT1Win ? 'background: rgba(56,189,248,0.12); color: #38bdf8; border: 1px solid rgba(56,189,248,0.25);' : ($isT2Win ? 'background: rgba(96,165,250,0.12); color: #60a5fa; border: 1px solid rgba(96,165,250,0.25);' : 'background: var(--bg-card-secondary); color: var(--text-muted); border: 1px solid var(--border-color);') }}">
                            {{ $m->winning_title ?: ($m->result_text ?: 'Match Completed') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: 12px; padding: 32px 20px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 6px;">🏏</div>
                <h3 style="font-size: 1rem; font-weight: 800; color: var(--text-main); margin: 0 0 4px;">
                    No Head-to-Head Matches Found for {{ $fLabel }}
                </h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                    There are no direct match records found between {{ $team1->name }} and {{ $team2->name }} under the {{ $fLabel }} category in the database.
                </p>
            </div>
        @endif
    </div>

    <!-- Key Squad Players Head to Head Section -->
    @if($t1Players->isNotEmpty() || $t2Players->isNotEmpty())
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
        <div style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>👥</span> Key Squad Players Head to Head
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;" class="h2h-players-grid">
            <!-- Team 1 Players -->
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: #38bdf8; margin-bottom: 10px;">
                    🛡️ {{ $team1->name }} Squad
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($t1Players as $p)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px;">
                            <a href="{{ route('player.profile.slug', ['slug' => $p->slug, 'id' => $p->id]) }}" style="color: var(--text-main); text-decoration: none; font-weight: 700; font-size: 0.85rem;">
                                {{ $p->name }}
                            </a>
                            <span style="font-size: 0.75rem; color: #38bdf8; font-weight: 700;">{{ $p->role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Team 2 Players -->
            <div>
                <div style="font-size: 0.85rem; font-weight: 800; color: #60a5fa; margin-bottom: 10px;">
                    🛡️ {{ $team2->name }} Squad
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($t2Players as $p)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px;">
                            <a href="{{ route('player.profile.slug', ['slug' => $p->slug, 'id' => $p->id]) }}" style="color: var(--text-main); text-decoration: none; font-weight: 700; font-size: 0.85rem;">
                                {{ $p->name }}
                            </a>
                            <span style="font-size: 0.75rem; color: #60a5fa; font-weight: 700;">{{ $p->role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
function toggleH2HDropdown(menuId) {
    const menu = document.getElementById(menuId);
    const otherMenuId = (menuId === 'team1DropdownMenu') ? 'team2DropdownMenu' : 'team1DropdownMenu';
    const otherMenu = document.getElementById(otherMenuId);
    if (otherMenu) otherMenu.style.display = 'none';

    if (menu) {
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
            const searchInput = menu.querySelector('.h2h-search-box');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                const list = menu.querySelector('.h2h-options-list');
                if (list) {
                    list.querySelectorAll('.h2h-option-item').forEach(item => item.style.display = 'flex');
                }
            }
        } else {
            menu.style.display = 'none';
        }
    }
}

function selectH2HTeam(teamKey, teamId, teamName) {
    document.getElementById(teamKey + 'HiddenInput').value = teamId;
    document.getElementById(teamKey + 'SelectedName').textContent = teamName;
    const menu = document.getElementById(teamKey + 'DropdownMenu');
    if (menu) menu.style.display = 'none';
    document.getElementById('h2hForm').submit();
}

function filterH2HTeams(input, listId) {
    const term = input.value.toLowerCase().trim();
    const list = document.getElementById(listId);
    if (!list) return;
    const items = list.querySelectorAll('.h2h-option-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        if (name.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function swapTeams() {
    const t1Input = document.getElementById('team1HiddenInput');
    const t2Input = document.getElementById('team2HiddenInput');
    if (t1Input && t2Input) {
        const temp = t1Input.value;
        t1Input.value = t2Input.value;
        t2Input.value = temp;
        document.getElementById('h2hForm').submit();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#team1DropdownWrap') && !e.target.closest('#team2DropdownWrap')) {
        const m1 = document.getElementById('team1DropdownMenu');
        const m2 = document.getElementById('team2DropdownMenu');
        if (m1) m1.style.display = 'none';
        if (m2) m2.style.display = 'none';
    }
});
</script>

<style>
/* Format Tab Pills */
.h2h-format-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 18px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s ease;
    background: var(--bg-card-secondary);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
}
.h2h-format-tab:hover {
    color: var(--text-main);
    border-color: rgba(56, 189, 248, 0.4);
    transform: translateY(-1px);
}
.h2h-format-tab.active {
    background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
    color: #0b1120;
    border-color: #38bdf8;
    box-shadow: 0 4px 14px rgba(56, 189, 248, 0.35);
}

/* Teams Header Grid */
.h2h-teams-header-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 16px;
    align-items: center;
    margin-bottom: 24px;
    position: relative;
    z-index: 20;
}

/* Custom Dropdown Trigger */
.h2h-custom-dropdown {
    position: relative;
    width: 100%;
}
.h2h-dropdown-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-card-secondary);
    border-radius: 12px;
    padding: 10px 14px;
    cursor: pointer;
    border: 1.5px solid var(--border-color);
    color: var(--text-main);
    transition: all 0.2s ease;
    outline: none;
}
.h2h-dropdown-trigger.team1-trigger {
    border-color: rgba(56, 189, 248, 0.4);
}
.h2h-dropdown-trigger.team1-trigger:hover, .h2h-dropdown-trigger.team1-trigger:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.25);
}
.h2h-dropdown-trigger.team2-trigger {
    border-color: rgba(96, 165, 250, 0.4);
}
.h2h-dropdown-trigger.team2-trigger:hover, .h2h-dropdown-trigger.team2-trigger:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 12px rgba(96, 165, 250, 0.25);
}
.h2h-trigger-team-info {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
}
.h2h-team-logo-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(56, 189, 248, 0.15);
    border: 1.5px solid #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.8rem;
    font-weight: 900;
    color: #38bdf8;
}
.h2h-team-logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.h2h-trigger-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.h2h-dropdown-chevron {
    font-size: 0.85rem;
    color: var(--text-dim);
    margin-left: 8px;
    flex-shrink: 0;
}

/* Custom Floating Dropdown Menu */
.h2h-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 100;
    background: #0f172a;
    border: 1.5px solid rgba(56, 189, 248, 0.4);
    border-radius: 12px;
    box-shadow: 0 16px 36px rgba(0,0,0,0.7), 0 4px 12px rgba(0,0,0,0.5);
    overflow: hidden;
}
.h2h-dropdown-search-wrap {
    padding: 10px;
    background: #1e293b;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.h2h-search-box {
    width: 100%;
    background: #0b1120;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px;
    padding: 8px 12px;
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 600;
    outline: none;
    box-sizing: border-box;
}
.h2h-search-box:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 8px rgba(56, 189, 248, 0.3);
}
.h2h-options-list {
    max-height: 250px;
    overflow-y: auto;
    padding: 6px;
    scrollbar-width: thin;
    scrollbar-color: #334155 #0f172a;
}
.h2h-options-list::-webkit-scrollbar {
    width: 6px;
}
.h2h-options-list::-webkit-scrollbar-track {
    background: #0f172a;
}
.h2h-options-list::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 3px;
}
.h2h-option-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    color: #e2e8f0;
    font-size: 0.88rem;
    font-weight: 700;
}
.h2h-option-item:hover {
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
}
.h2h-option-item.selected {
    background: rgba(56, 189, 248, 0.22);
    color: #38bdf8;
}
.h2h-option-logo {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(56, 189, 248, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.68rem;
    font-weight: 900;
    color: #38bdf8;
}
.h2h-option-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.h2h-option-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.h2h-option-check {
    color: #38bdf8;
    font-weight: 900;
    font-size: 0.95rem;
}

/* Center VS Ball */
.h2h-vs-ball {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, #ef4444, #7f1d1d);
    border: 2px solid rgba(255,255,255,0.25);
    box-shadow: 0 4px 16px rgba(239, 68, 68, 0.45), inset -2px -2px 6px rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.h2h-vs-ball:hover {
    transform: scale(1.1) rotate(15deg);
}
.h2h-vs-ball span {
    font-size: 0.85rem;
    font-weight: 900;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    letter-spacing: 0.5px;
}

/* Bilateral Metric Row */
.h2h-metric-row {
    display: grid;
    grid-template-columns: 80px 1fr 80px;
    gap: 16px;
    align-items: center;
}

/* Stat Box (Left & Right Numbers) */
.h2h-stat-box {
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 900;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
}
.h2h-stat-box.left-box {
    background: var(--bg-card-secondary);
    border: 1.5px solid rgba(56, 189, 248, 0.35);
    color: #38bdf8;
}
.h2h-stat-box.right-box {
    background: var(--bg-card-secondary);
    border: 1.5px solid rgba(96, 165, 250, 0.35);
    color: #60a5fa;
}

/* Center Column */
.h2h-center-bar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.h2h-metric-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--text-main);
    text-transform: capitalize;
    letter-spacing: 0.02em;
}

/* Bilateral Progress Bar Tracks */
.h2h-bar-track-wrap {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 10px;
}
.h2h-bar-track {
    height: 8px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}
.h2h-bar-track.left-track {
    display: flex;
    justify-content: flex-end; /* Fills towards center dot */
}
.h2h-bar-track.right-track {
    display: flex;
    justify-content: flex-start; /* Fills away from center dot */
}
.h2h-bar-fill {
    height: 100%;
    border-radius: 6px;
    transition: width 0.5s ease-in-out;
}
.h2h-bar-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--text-dim);
    box-shadow: 0 0 6px rgba(255, 255, 255, 0.2);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .h2h-teams-header-grid {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }
    .h2h-metric-row {
        grid-template-columns: 56px 1fr 56px !important;
        gap: 8px !important;
    }
    .h2h-stat-box {
        height: 44px !important;
        font-size: 1.05rem !important;
    }
    .h2h-summary-grid {
        grid-template-columns: 1fr !important;
    }
    .h2h-players-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection
