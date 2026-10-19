@extends('layouts.app')

@php
    $t1Name = $team1 ? $team1->name : 'Team 1';
    $t2Name = $team2 ? $team2->name : 'Team 2';
    $vsTitle = $t1Name . ' vs ' . $t2Name;
    $fLabel = $formatLabel ?? 'TEST';

    $isHubPage = $isHub ?? false;

    if ($isHubPage) {
        $pageTitle = 'Head to Head Stats & Records In ' . $fLabel . ' Cricket | CricketKaScore';
        $metaDesc = 'Explore head-to-head cricket stats, historical win-loss records, draws, ties, and face-off comparisons across all international teams in ' . $fLabel . ' on CricketKaScore.';
    } else {
        $pageTitle = $vsTitle . ' Head to Head in ' . $fLabel . ' Records, Stats & Results | CricketKaScore';
        $metaDesc = $vsTitle . ' Head to Head in ' . $fLabel . ' cricket records, match results, win counts, home/away/neutral wins, highest scores, and recent form on CricketKaScore.';
    }

    $metaKeywords = 'head to head cricket, ' . $vsTitle . ' head to head, ' . $t1Name . ' vs ' . $t2Name . ' ' . $fLabel . ' records, h2h cricket stats, cricket team compare, win percentage, CricketKaScore';
    $ogTitle = $pageTitle;
    $ogDesc = $metaDesc;
    $canonicalUrl = request()->fullUrl();
    $ogImage = $team1?->logo ?: asset('images/logo.png');

    $formatTabs = [
        'TEST' => 'TEST',
        'ODI' => 'ODI',
        'T20' => 'T20',
        'ALL' => 'ALL FORMATS',
    ];

    $formatDescriptions = [
        'TEST' => 'TEST is a longest cricket format played between two ICC teams. Here are the Head to Head records of all the participating teams in the ICC TEST Cricket so far.',
        'ODI'  => 'ODI is a limited-overs cricket format played between two ICC teams. Here are the Head to Head records of all the participating teams in the ICC ODI Cricket so far.',
        'T20'  => 'T20 is a shortest cricket format played between two ICC teams. Here are the Head to Head records of all the participating teams in the ICC T20 Cricket so far.',
        'ALL'  => 'Here are the comprehensive Head to Head records across all cricket formats of all participating international teams so far.',
    ];
    $currentFormatDesc = $formatDescriptions[$format] ?? $formatDescriptions['TEST'];

    $teamColorMap = [
        'Australia'    => ['bg' => 'linear-gradient(135deg, #eab308, #ca8a04)', 'badge' => '#a16207'],
        'England'      => ['bg' => 'linear-gradient(135deg, #1e3a8a, #0f172a)', 'badge' => '#1e40af'],
        'India'        => ['bg' => 'linear-gradient(135deg, #2563eb, #1d4ed8)', 'badge' => '#1d4ed8'],
        'New Zealand'  => ['bg' => 'linear-gradient(135deg, #27272a, #09090b)', 'badge' => '#3f3f46'],
        'South Africa' => ['bg' => 'linear-gradient(135deg, #15803d, #14532d)', 'badge' => '#166534'],
        'Sri Lanka'    => ['bg' => 'linear-gradient(135deg, #0284c7, #0369a1)', 'badge' => '#075985'],
        'West Indies'  => ['bg' => 'linear-gradient(135deg, #881337, #4c0519)', 'badge' => '#9f1239'],
        'Pakistan'     => ['bg' => 'linear-gradient(135deg, #047857, #064e3b)', 'badge' => '#065f46'],
        'Zimbabwe'     => ['bg' => 'linear-gradient(135deg, #dc2626, #991b1b)', 'badge' => '#b91c1c'],
        'Bangladesh'   => ['bg' => 'linear-gradient(135deg, #059669, #065f46)', 'badge' => '#047857'],
        'Ireland'      => ['bg' => 'linear-gradient(135deg, #10b981, #047857)', 'badge' => '#059669'],
        'Afghanistan'  => ['bg' => 'linear-gradient(135deg, #0891b2, #155e75)', 'badge' => '#0e7490'],
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
    'name' => $pageTitle,
    'url' => $canonicalUrl,
    'description' => $metaDesc,
    'competitor' => [
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t1Name,
            'image' => $team1?->logo_url ?? $team1?->logo
        ],
        [
            chr(64) . 'type' => 'SportsTeam',
            'name' => $t2Name,
            'image' => $team2?->logo_url ?? $team2?->logo
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<div class="h2h-page-wrapper" style="max-width: 1180px; margin: 0 auto; padding: 20px 16px 60px;">

    <!-- Breadcrumbs -->
    <nav class="h2h-breadcrumbs" aria-label="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span class="h2h-breadcrumb-sep">&rsaquo;</span>
        <a href="{{ route('teams') }}">Teams</a>
        <span class="h2h-breadcrumb-sep">&rsaquo;</span>
        <a href="{{ url('/head-to-head-records-in-' . strtolower($format)) }}">Teams Head to Head</a>
        @if(!$isHubPage)
            <span class="h2h-breadcrumb-sep">&rsaquo;</span>
            <span class="h2h-breadcrumb-current">{{ $vsTitle }} ({{ $fLabel }})</span>
        @endif
    </nav>

    <!-- Header Section (Matching Screenshot) -->
    <div style="margin-bottom: 22px;">
        <h1 style="font-size: 1.65rem; font-weight: 900; color: var(--text-main); margin: 0 0 6px; letter-spacing: -0.02em;">
            @if($isHubPage)
                Head to Head Stats & Records In {{ $fLabel }} Cricket
            @else
                {{ $vsTitle }} Head to Head In {{ $fLabel }}
            @endif
        </h1>
        
        <!-- Format Tabs (Clean SEO URLs matching MyKhel structure: /head-to-head-records-in-test) -->
        <div class="h2h-screenshot-tabs-wrap">
            <div class="h2h-screenshot-tabs">
                @foreach($formatTabs as $fKey => $fTitle)
                    @php 
                        $isActive = ($format === $fKey);
                        if ($isHubPage) {
                            $tabUrl = url('/head-to-head-records-in-' . strtolower($fKey));
                        } else {
                            $tabUrl = url('/head-to-head/' . Str::slug($team1->name) . '-vs-' . Str::slug($team2->name) . '-in-' . strtolower($fKey));
                        }
                    @endphp
                    <a href="{{ $tabUrl }}" 
                       class="h2h-screenshot-tab {{ $isActive ? 'active' : '' }}">
                        {{ $fTitle }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Format Description text (from Reference) -->
        <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin: 14px 0 0;">
            {{ $currentFormatDesc }}
        </p>
    </div>

    @if($isHubPage)
        <!-- ========================================================================= -->
        <!-- HUB VIEW: Grid of Circular Team Cards ONLY (Matching User's Screenshot)    -->
        <!-- NO featured rivalry or preview below it - only clean teams grid!          -->
        <!-- ========================================================================= -->
        <div class="h2h-cards-grid">
            @foreach($topTeams as $t)
                @php
                    $cStyle = $teamColorMap[$t->name] ?? ['bg' => 'linear-gradient(135deg, #334155, #1e293b)', 'badge' => '#475569'];
                @endphp
                <div class="h2h-team-card" id="card-wrap-{{ $t->id }}">
                    <!-- Top Circular Section -->
                    <div class="h2h-card-circle-top" style="background: {{ $cStyle['bg'] }};">
                        <div class="h2h-circle-flag">
                            @if(!empty($t->logo_url ?? $t->logo))
                                <img src="{{ $t->logo_url ?? $t->logo }}" alt="{{ $t->name }}">
                            @else
                                <span style="font-size: 0.75rem; font-weight: 900; color: #1e293b;">{{ strtoupper(substr($t->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="h2h-circle-name">{{ $t->name }}</div>
                    </div>

                    <!-- Center Cricket Ball with Stitched Seam & VS text -->
                    <div class="h2h-hub-vs-ball">
                        <div class="h2h-cricket-ball">
                            <span class="h2h-vs-text">VS</span>
                        </div>
                    </div>

                    <!-- Bottom Dropdown Button -->
                    <div class="h2h-card-bottom" style="position: relative;">
                        <button type="button" class="h2h-select-team-btn" onclick="toggleHubDropdown('hub-drop-{{ $t->id }}', event)">
                            <span id="hub-label-{{ $t->id }}">Select Team</span>
                            <span class="h2h-chevron-icon">▾</span>
                        </button>

                        <!-- Searchable Dropdown Popup Menu (Z-index elevated, solid background) -->
                        <div id="hub-drop-{{ $t->id }}" class="h2h-hub-dropdown-menu" style="display: none;">
                            <div class="h2h-hub-search-wrap">
                                <span class="h2h-hub-search-icon">🔍</span>
                                <input type="text" class="h2h-hub-search-input" placeholder="Search for Team" oninput="filterHubDropdown(this, 'hub-list-{{ $t->id }}')" onclick="event.stopPropagation();">
                            </div>
                            <div class="h2h-hub-options-list" id="hub-list-{{ $t->id }}">
                                @foreach($topTeams as $opp)
                                    @if($opp->id !== $t->id)
                                        @php
                                            $cleanMatchupUrl = url('/head-to-head/' . Str::slug($t->name) . '-vs-' . Str::slug($opp->name) . '-in-' . strtolower($format));
                                        @endphp
                                        <a href="{{ $cleanMatchupUrl }}" class="h2h-hub-option-item" data-name="{{ strtolower($opp->name) }}">
                                            <div class="h2h-hub-option-flag">
                                                @if(!empty($opp->logo_url ?? $opp->logo))
                                                    <img src="{{ $opp->logo_url ?? $opp->logo }}" alt="{{ $opp->name }}">
                                                @else
                                                    <span>{{ strtoupper(substr($opp->name, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <span class="h2h-hub-option-name">{{ $opp->name }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <!-- ========================================================================= -->
        <!-- DETAILED MATCHUP COMPARISON VIEW (Shown ONLY when a matchup is selected!) -->
        <!-- ========================================================================= -->
        
        <!-- Narrative Sentence (as shown in reference design) -->
        <div style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
            @if(!empty($stats['narrative']))
                {{ $stats['narrative'] }}
            @else
                {{ $t1Name }} and {{ $t2Name }} have faced each other in <strong style="color: #38bdf8;">{{ $stats['totalMatches'] }}</strong> matches in <strong style="color: var(--text-main);">{{ $fLabel }}</strong>. Out of these {{ $stats['totalMatches'] }} games, <strong style="color: #22c55e;">{{ $t1Name }}</strong> have won <strong style="color: #22c55e;">{{ $stats['t1Wins'] }}</strong> whereas <strong style="color: #60a5fa;">{{ $t2Name }}</strong> have come out victorious on <strong style="color: #60a5fa;">{{ $stats['t2Wins'] }}</strong> occasions{{ $stats['drawn'] > 0 ? ', with ' . $stats['drawn'] . ' drawn' : '' }}{{ $stats['noResults'] > 0 ? ', with ' . $stats['noResults'] . ' matches ending without a result' : '' }}{{ $stats['ties'] > 0 ? ', and ' . $stats['ties'] . ' tied' : '' }}.
            @endif
        </div>

        <!-- Main Comparison Section -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px 20px 28px; margin-bottom: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            
            <!-- Teams Dropdown Header Form -->
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

                        <!-- Custom Floating Menu -->
                        <div class="h2h-dropdown-menu" id="team1DropdownMenu" style="display: none;">
                            <div class="h2h-dropdown-search-wrap">
                                <input type="text" class="h2h-search-box" placeholder="Search team..." oninput="filterH2HTeams(this, 'team1List')">
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

                    <!-- Center Cricket Ball / VS Icon (Click to Swap) -->
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

                        <!-- Custom Floating Menu -->
                        <div class="h2h-dropdown-menu" id="team2DropdownMenu" style="display: none;">
                            <div class="h2h-dropdown-search-wrap">
                                <input type="text" class="h2h-search-box" placeholder="Search team..." oninput="filterH2HTeams(this, 'team2List')">
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

            <!-- Center Matches Badge -->
            <div class="h2h-matches-badge-wrap" style="position: relative; display: flex; align-items: center; justify-content: center; margin: 10px 0 28px;">
                <div class="h2h-connector-line" style="position: absolute; left: 8%; right: 8%; height: 2px; background: linear-gradient(90deg, transparent, var(--border-color) 20%, var(--border-color) 80%, transparent); z-index: 1;">
                    <span style="position: absolute; left: 15%; top: -4px; width: 10px; height: 10px; border-radius: 50%; background: #38bdf8; box-shadow: 0 0 8px #38bdf8;"></span>
                    <span style="position: absolute; right: 15%; top: -4px; width: 10px; height: 10px; border-radius: 50%; background: #60a5fa; box-shadow: 0 0 8px #60a5fa;"></span>
                </div>
                
                <div style="position: relative; z-index: 2; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); border-radius: 12px; padding: 8px 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
                        Matches
                    </div>
                    <div class="h2h-matches-num" style="font-size: 1.6rem; font-weight: 900; color: #38bdf8; line-height: 1;">
                        {{ $stats['totalMatches'] }}
                    </div>
                </div>
            </div>

            @php
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
                        'barColor' => '#22c55e',
                    ],
                    [
                        'label' => 'Lost',
                        't1' => $stats['t1Lost'],
                        't2' => $stats['t2Lost'],
                        'barColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Drawn',
                        't1' => $stats['drawn'],
                        't2' => $stats['drawn'],
                        'barColor' => '#64748b',
                    ],
                    [
                        'label' => 'Tied',
                        't1' => $stats['ties'],
                        't2' => $stats['ties'],
                        'barColor' => '#f59e0b',
                    ],
                    [
                        'label' => 'No Result',
                        't1' => $stats['noResults'],
                        't2' => $stats['noResults'],
                        'barColor' => '#94a3b8',
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

                    <div style="margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">{{ $team1->name }} Win %</div>
                            <div style="font-size: 1.25rem; font-weight: 900; color: #22c55e;">{{ $stats['t1WinPct'] }}%</div>
                        </div>
                        <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">{{ $team2->name }} Win %</div>
                            <div style="font-size: 1.25rem; font-weight: 900; color: #60a5fa;">{{ $stats['t2WinPct'] }}%</div>
                        </div>
                    </div>
                @else
                    <div style="font-size: 0.82rem; color: var(--text-dim); padding: 12px 0;">
                        No past face-offs recorded between {{ $team1->name }} and {{ $team2->name }} for {{ $fLabel }}.
                    </div>
                @endif
            </div>

            <!-- Right: Recent Form Guide (Last 5 Games) -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
                <div style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>📈</span> Recent Form (Last 5 Matches)
                </div>

                <!-- Team 1 Recent Form -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span>{{ $team1->name }}</span>
                        <span style="font-size: 0.72rem; color: var(--text-dim);">Latest &rarr;</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        @forelse($stats['recentFormT1'] as $rf)
                            <span class="h2h-form-badge {{ $rf === 'W' ? 'badge-win' : ($rf === 'L' ? 'badge-loss' : 'badge-tie') }}">
                                {{ $rf }}
                            </span>
                        @empty
                            <span style="font-size: 0.78rem; color: var(--text-dim);">No recent data</span>
                        @endforelse
                    </div>
                </div>

                <!-- Team 2 Recent Form -->
                <div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span>{{ $team2->name }}</span>
                        <span style="font-size: 0.72rem; color: var(--text-dim);">Latest &rarr;</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        @forelse($stats['recentFormT2'] as $rf)
                            <span class="h2h-form-badge {{ $rf === 'W' ? 'badge-win' : ($rf === 'L' ? 'badge-loss' : 'badge-tie') }}">
                                {{ $rf }}
                            </span>
                        @empty
                            <span style="font-size: 0.78rem; color: var(--text-dim);">No recent data</span>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- EXPLORE MORE TEAMS GRID (Below Detailed Matchup View)                     -->
        <!-- ========================================================================= -->
        <div style="margin: 40px 0 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <div>
                    <h2 style="font-size: 1.3rem; font-weight: 900; color: var(--text-main); margin: 0 0 4px;">
                        ⚔️ Explore More Head-to-Head Records In {{ $fLabel }}
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                        Select any team below to compare head to head statistics and match records.
                    </p>
                </div>
            </div>

            <div class="h2h-cards-grid">
                @foreach($topTeams as $t)
                    @php
                        $cStyle = $teamColorMap[$t->name] ?? ['bg' => 'linear-gradient(135deg, #334155, #1e293b)', 'badge' => '#475569'];
                    @endphp
                    <div class="h2h-team-card" id="card-wrap-sub-{{ $t->id }}">
                        <!-- Top Circular Section -->
                        <div class="h2h-card-circle-top" style="background: {{ $cStyle['bg'] }};">
                            <div class="h2h-circle-flag">
                                @if(!empty($t->logo_url ?? $t->logo))
                                    <img src="{{ $t->logo_url ?? $t->logo }}" alt="{{ $t->name }}">
                                @else
                                    <span style="font-size: 0.75rem; font-weight: 900; color: #1e293b;">{{ strtoupper(substr($t->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="h2h-circle-name">{{ $t->name }}</div>
                        </div>

                        <!-- Center Cricket Ball with Stitched Seam & VS text -->
                        <div class="h2h-hub-vs-ball">
                            <div class="h2h-cricket-ball">
                                <span class="h2h-vs-text">VS</span>
                            </div>
                        </div>

                        <!-- Bottom Dropdown Button -->
                        <div class="h2h-card-bottom" style="position: relative;">
                            <button type="button" class="h2h-select-team-btn" onclick="toggleHubDropdown('hub-sub-drop-{{ $t->id }}', event)">
                                <span id="hub-sub-label-{{ $t->id }}">Select Team</span>
                                <span class="h2h-chevron-icon">▾</span>
                            </button>

                            <!-- Searchable Dropdown Popup Menu -->
                            <div id="hub-sub-drop-{{ $t->id }}" class="h2h-hub-dropdown-menu" style="display: none;">
                                <div class="h2h-hub-search-wrap">
                                    <span class="h2h-hub-search-icon">🔍</span>
                                    <input type="text" class="h2h-hub-search-input" placeholder="Search for Team" oninput="filterHubDropdown(this, 'hub-sub-list-{{ $t->id }}')" onclick="event.stopPropagation();">
                                </div>
                                <div class="h2h-hub-options-list" id="hub-sub-list-{{ $t->id }}">
                                    @foreach($topTeams as $opp)
                                        @if($opp->id !== $t->id)
                                            @php
                                                $cleanMatchupUrl = url('/head-to-head/' . Str::slug($t->name) . '-vs-' . Str::slug($opp->name) . '-in-' . strtolower($format));
                                            @endphp
                                            <a href="{{ $cleanMatchupUrl }}" class="h2h-hub-option-item" data-name="{{ strtolower($opp->name) }}">
                                                <div class="h2h-hub-option-flag">
                                                    @if(!empty($opp->logo_url ?? $opp->logo))
                                                        <img src="{{ $opp->logo_url ?? $opp->logo }}" alt="{{ $opp->name }}">
                                                    @else
                                                        <span>{{ strtoupper(substr($opp->name, 0, 2)) }}</span>
                                                    @endif
                                                </div>
                                                <span class="h2h-hub-option-name">{{ $opp->name }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Scorecards Match History Section (if database contains matches) -->
        @if($matches && $matches->count() > 0)
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px 20px; margin-bottom: 28px;">
                <div style="font-size: 1.1rem; font-weight: 900; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                    <span>📅 Match History & Scorecards ({{ $matches->count() }})</span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-dim);">Chronological Order</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($matches as $m)
                        <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <div style="font-size: 0.78rem; color: var(--text-dim); margin-bottom: 4px;">
                                    {{ $m->tournament->name ?? 'International Match' }} &bull; {{ $m->match_date ? \Carbon\Carbon::parse($m->match_date)->format('d M Y') : '' }}
                                </div>
                                <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">
                                    {{ $m->team1->name }} <span style="font-weight: 900; color: #38bdf8;">{{ $m->team1_score ?: '' }}</span> 
                                    vs 
                                    {{ $m->team2->name }} <span style="font-weight: 900; color: #60a5fa;">{{ $m->team2_score ?: '' }}</span>
                                </div>
                                <div style="font-size: 0.82rem; color: #22c55e; font-weight: 700; margin-top: 4px;">
                                    {{ $m->result_text ?: ($m->result_description ?: 'Match Completed') }}
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('matches.detail.slug', ['slug' => $m->slug ?? 'match', 'id' => $m->id]) }}" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 0.82rem; text-decoration: none;">
                                    View Scorecard &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif

</div>

<script>
// Main Header Team Selection Dropdowns (Inside Comparison)
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

// Interactive Team Hub Cards Dropdowns with Proper Stacking / Z-Index Elevation
function toggleHubDropdown(menuId, event) {
    if (event) {
        event.stopPropagation();
    }
    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    const parentCard = targetMenu.closest('.h2h-team-card');

    // Close all other hub dropdowns and reset their card z-index
    document.querySelectorAll('.h2h-hub-dropdown-menu').forEach(m => {
        if (m.id !== menuId) {
            m.style.display = 'none';
            const c = m.closest('.h2h-team-card');
            if (c) {
                c.style.zIndex = '';
                c.classList.remove('active-dropdown-card');
            }
        }
    });

    const isHidden = (targetMenu.style.display === 'none' || targetMenu.style.display === '');
    if (isHidden) {
        targetMenu.style.display = 'block';
        if (parentCard) {
            parentCard.style.zIndex = '9999';
            parentCard.classList.add('active-dropdown-card');
        }
        const input = targetMenu.querySelector('.h2h-hub-search-input');
        if (input) {
            input.value = '';
            input.focus();
            const list = targetMenu.querySelector('.h2h-hub-options-list');
            if (list) {
                list.querySelectorAll('.h2h-hub-option-item').forEach(item => item.style.display = 'flex');
            }
        }
    } else {
        targetMenu.style.display = 'none';
        if (parentCard) {
            parentCard.style.zIndex = '';
            parentCard.classList.remove('active-dropdown-card');
        }
    }
}

function filterHubDropdown(input, listId) {
    const term = input.value.toLowerCase().trim();
    const list = document.getElementById(listId);
    if (!list) return;
    const items = list.querySelectorAll('.h2h-hub-option-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        if (name.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Global outside-click closer
document.addEventListener('click', function(e) {
    if (!e.target.closest('#team1DropdownWrap') && !e.target.closest('#team2DropdownWrap')) {
        const m1 = document.getElementById('team1DropdownMenu');
        const m2 = document.getElementById('team2DropdownMenu');
        if (m1) m1.style.display = 'none';
        if (m2) m2.style.display = 'none';
    }

    if (!e.target.closest('.h2h-card-bottom')) {
        document.querySelectorAll('.h2h-hub-dropdown-menu').forEach(m => {
            m.style.display = 'none';
            const c = m.closest('.h2h-team-card');
            if (c) {
                c.style.zIndex = '';
                c.classList.remove('active-dropdown-card');
            }
        });
    }
});
</script>

<style>
/* Breadcrumbs Theme Styling */
.h2h-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: var(--text-dim, #64748b);
    margin-bottom: 18px;
    flex-wrap: wrap;
    font-weight: 600;
}
.h2h-breadcrumbs a {
    color: var(--text-dim, #64748b);
    text-decoration: none;
    transition: color 0.15s ease;
}
.h2h-breadcrumbs a:hover {
    color: #38bdf8;
    text-decoration: underline;
}
.h2h-breadcrumb-sep {
    color: var(--text-dim, #64748b);
    font-size: 0.95rem;
}
.h2h-breadcrumb-current {
    color: #38bdf8;
    font-weight: 700;
}
html.light-theme .h2h-breadcrumbs a,
body.light-theme .h2h-breadcrumbs a {
    color: #64748b;
}
html.light-theme .h2h-breadcrumbs a:hover,
body.light-theme .h2h-breadcrumbs a:hover {
    color: #0284c7;
}
html.light-theme .h2h-breadcrumb-current,
body.light-theme .h2h-breadcrumb-current {
    color: #2563eb;
}

/* Format Tab Pills (Underlined Active Tab styled according to theme) */
.h2h-screenshot-tabs-wrap {
    margin: 16px 0 12px;
    border-bottom: 2px solid var(--border-color, #30363d);
}
html.light-theme .h2h-screenshot-tabs-wrap,
body.light-theme .h2h-screenshot-tabs-wrap {
    border-bottom-color: #e2e8f0;
}
.h2h-screenshot-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
}
.h2h-screenshot-tab {
    padding: 10px 22px;
    font-size: 0.95rem;
    font-weight: 800;
    text-decoration: none;
    color: var(--text-dim, #64748b);
    background: transparent;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s ease;
    letter-spacing: 0.04em;
    white-space: nowrap;
}
.h2h-screenshot-tab:hover {
    color: var(--text-main, #ffffff);
    border-bottom-color: rgba(56, 189, 248, 0.4);
}
.h2h-screenshot-tab.active {
    color: #38bdf8 !important;
    border-bottom: 3px solid #38bdf8 !important;
    background: transparent;
    text-shadow: 0 0 12px rgba(56, 189, 248, 0.35);
}
html.light-theme .h2h-screenshot-tab,
body.light-theme .h2h-screenshot-tab {
    color: #64748b;
}
html.light-theme .h2h-screenshot-tab:hover,
body.light-theme .h2h-screenshot-tab:hover {
    color: #0f172a;
    border-bottom-color: rgba(37, 99, 235, 0.4);
}
html.light-theme .h2h-screenshot-tab.active,
body.light-theme .h2h-screenshot-tab.active {
    color: #2563eb !important;
    border-bottom: 3px solid #2563eb !important;
    text-shadow: none;
}

html.light-theme .h2h-matches-num,
body.light-theme .h2h-matches-num {
    color: #2563eb !important;
}

/* Hub Cards Grid */
.h2h-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 22px;
    margin-top: 24px;
    margin-bottom: 36px;
}
@media (max-width: 1024px) {
    .h2h-cards-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
}
@media (max-width: 768px) {
    .h2h-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
}
@media (max-width: 480px) {
    .h2h-cards-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}

/* Hub Card Container */
.h2h-team-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.h2h-team-card.active-dropdown-card {
    z-index: 9999 !important;
}
.h2h-team-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(0,0,0,0.14);
}

.h2h-card-circle-top {
    border-radius: 16px 16px 0 0;
    padding: 24px 16px 36px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
    position: relative;
}
.h2h-circle-flag {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    background: white;
    border: 2.5px solid rgba(255,255,255,0.85);
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}
.h2h-circle-flag img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.h2h-circle-name {
    color: #ffffff;
    font-weight: 800;
    font-size: 1.05rem;
    letter-spacing: -0.01em;
    text-shadow: 0 1px 4px rgba(0,0,0,0.6);
}

/* Center Cricket Ball with White Seam & VS */
.h2h-hub-vs-ball {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: -24px;
    margin-bottom: -18px;
    z-index: 5;
}
.h2h-cricket-ball {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #ef4444 0%, #b91c1c 65%, #7f1d1d 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45), inset 0 2px 4px rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    border: 1.5px solid #dc2626;
}
.h2h-cricket-ball::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    height: 10px;
    border-top: 1.5px dashed rgba(255, 255, 255, 0.75);
    border-bottom: 1.5px dashed rgba(255, 255, 255, 0.75);
    top: 50%;
    transform: translateY(-50%);
}
.h2h-vs-text {
    position: relative;
    z-index: 2;
    color: #ffffff;
    font-weight: 900;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
}

/* Card Bottom & Select Team Button */
.h2h-card-bottom {
    padding: 24px 14px 16px;
    border-radius: 0 0 16px 16px;
    background: var(--bg-card);
}
.h2h-select-team-btn {
    width: 100%;
    background: var(--bg-card-secondary, #f8fafc);
    border: 1px solid var(--border-color, #cbd5e1);
    color: var(--text-main, #0f172a);
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.h2h-select-team-btn:hover {
    border-color: #38bdf8;
    background: rgba(56, 189, 248, 0.08);
}
.h2h-chevron-icon {
    font-size: 0.85rem;
    color: var(--text-dim, #64748b);
}

/* Hub Dropdown Menu - Floating on top with solid background & crisp text */
.h2h-hub-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #0f172a !important;
    border: 1.5px solid #38bdf8 !important;
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.85) !important;
    z-index: 10000 !important;
    padding: 10px;
    animation: fadeInDown 0.15s ease-out;
}
html.light-theme .h2h-hub-dropdown-menu,
body.light-theme .h2h-hub-dropdown-menu {
    background: #ffffff !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2) !important;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

.h2h-hub-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}
.h2h-hub-search-icon {
    position: absolute;
    left: 12px;
    font-size: 0.9rem;
    color: #38bdf8;
    pointer-events: none;
}
.h2h-hub-search-input {
    width: 100%;
    padding: 10px 12px 10px 36px !important;
    border: 1.5px solid #334155 !important;
    border-radius: 8px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #1e293b !important;
    color: #ffffff !important;
    outline: none !important;
    box-sizing: border-box;
}
.h2h-hub-search-input::placeholder {
    color: #94a3b8 !important;
    opacity: 1;
}
html.light-theme .h2h-hub-search-input,
body.light-theme .h2h-hub-search-input {
    background: #f8fafc !important;
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}
html.light-theme .h2h-hub-search-input::placeholder,
body.light-theme .h2h-hub-search-input::placeholder {
    color: #64748b !important;
}

.h2h-hub-options-list {
    max-height: 220px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 3px;
    scrollbar-width: thin;
    scrollbar-color: #334155 transparent;
}
.h2h-hub-option-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: #f8fafc !important;
    font-weight: 700;
    font-size: 0.88rem;
    transition: all 0.15s ease;
}
html.light-theme .h2h-hub-option-item,
body.light-theme .h2h-hub-option-item {
    color: #0f172a !important;
}
.h2h-hub-option-item:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    transform: translateX(3px);
}
.h2h-hub-option-flag {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    overflow: hidden;
    background: white;
    border: 1.5px solid rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.h2h-hub-option-flag img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Header Comparison Grid */
.h2h-teams-header-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}
.h2h-custom-dropdown {
    position: relative;
    width: 100%;
}
.h2h-dropdown-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 12px;
    background: var(--bg-card-secondary);
    border: 1.5px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
}
.h2h-dropdown-trigger:hover {
    border-color: #38bdf8;
}
.h2h-trigger-team-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.h2h-team-logo-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
    overflow: hidden;
    border: 2px solid #38bdf8;
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    flex-shrink: 0;
}
.h2h-team-logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    color: var(--text-dim);
    font-size: 0.85rem;
    margin-left: 8px;
}
.h2h-vs-ball {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #ef4444 0%, #b91c1c 65%, #7f1d1d 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), inset 0 2px 4px rgba(255, 255, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.25s ease;
    border: 1.5px solid #dc2626;
}
.h2h-vs-ball:hover {
    transform: rotate(180deg) scale(1.08);
}
.h2h-vs-ball span {
    font-weight: 900;
    font-size: 0.8rem;
    color: #ffffff;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8);
}
.h2h-dropdown-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 16px 36px rgba(0,0,0,0.35);
    z-index: 100;
    padding: 10px;
    max-height: 280px;
    display: flex;
    flex-direction: column;
}
.h2h-dropdown-search-wrap {
    margin-bottom: 8px;
}
.h2h-search-box {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    background: var(--bg-card-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-main);
    font-size: 0.85rem;
    font-weight: 600;
    outline: none;
}
.h2h-options-list {
    overflow-y: auto;
    max-height: 210px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.h2h-option-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.h2h-option-item:hover {
    background: rgba(56, 189, 248, 0.12);
}
.h2h-option-item.selected {
    background: rgba(56, 189, 248, 0.2);
}
.h2h-option-logo {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    overflow: hidden;
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    flex-shrink: 0;
}
.h2h-option-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.h2h-option-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-main);
    flex: 1;
}
.h2h-option-check {
    color: #22c55e;
    font-weight: 900;
}

/* Bilateral Progress Bar Comparison Rows */
.h2h-metric-row {
    display: grid;
    grid-template-columns: 70px 1fr 70px;
    align-items: center;
    gap: 16px;
}
.h2h-stat-box {
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    font-weight: 900;
    transition: all 0.2s ease;
    background: var(--bg-card-secondary, #0d1117);
    color: var(--text-main, #ffffff);
    border: 1.5px solid var(--border-color, #30363d);
}
.h2h-stat-box.left-box {
    border-color: rgba(56, 189, 248, 0.35);
}
.h2h-stat-box.right-box {
    border-color: rgba(96, 165, 250, 0.35);
}

/* Crisp black numbers in light theme */
body.light-theme .h2h-stat-box,
html.light-theme .h2h-stat-box,
body.light-theme .h2h-stat-box.left-box,
html.light-theme .h2h-stat-box.left-box,
body.light-theme .h2h-stat-box.right-box,
html.light-theme .h2h-stat-box.right-box {
    color: #000000 !important;
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
}

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
    justify-content: flex-end;
}
.h2h-bar-track.right-track {
    display: flex;
    justify-content: flex-start;
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

/* Recent Form Badges */
.h2h-form-badge {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.85rem;
}
.badge-win {
    background: #22c55e;
    color: white;
}
.badge-loss {
    background: #ef4444;
    color: white;
}
.badge-tie {
    background: #f59e0b;
    color: white;
}

/* Responsive adjustments */
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
}
</style>
@endsection
