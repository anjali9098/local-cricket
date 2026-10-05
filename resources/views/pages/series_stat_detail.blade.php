@extends('layouts.app')

@php
    $tourName = trim($tournament->name);
    $statMap = [
        'most-runs' => ['title' => 'Most Runs', 'icon' => '🏏', 'type' => 'batting'],
        'most-fours' => ['title' => 'Most Fours', 'icon' => '⚡', 'type' => 'batting'],
        'most-sixes' => ['title' => 'Most Sixes', 'icon' => '💥', 'type' => 'batting'],
        'most-fifties' => ['title' => 'Most Fifties', 'icon' => '🎖️', 'type' => 'batting'],
        'most-centuries' => ['title' => 'Most Centuries', 'icon' => '💯', 'type' => 'batting'],
        'fours-innings' => ['title' => 'Most Fours (Innings)', 'icon' => '⚡', 'type' => 'innings_batting'],
        'sixes-innings' => ['title' => 'Most Sixes (Innings)', 'icon' => '💥', 'type' => 'innings_batting'],
        'best-strike-rates' => ['title' => 'Best Strike Rates', 'icon' => '🚀', 'type' => 'batting'],
        'highest-scores' => ['title' => 'Highest Individual Scores', 'icon' => '👑', 'type' => 'innings_batting'],
        'top-wickets' => ['title' => 'Top Wicket Takers', 'icon' => '🎯', 'type' => 'bowling'],
        'four-wickets' => ['title' => 'Four Wickets', 'icon' => '🔥', 'type' => 'bowling'],
        'five-wickets' => ['title' => 'Five Wickets', 'icon' => '🌟', 'type' => 'bowling'],
        'maidens' => ['title' => 'Maidens', 'icon' => '🛡️', 'type' => 'bowling'],
        'best-bowling-avg' => ['title' => 'Best Bowling Averages', 'icon' => '📊', 'type' => 'bowling'],
        'best-figures' => ['title' => 'Best Bowling Figures', 'icon' => '💎', 'type' => 'innings_bowling'],
        'best-economies' => ['title' => 'Best Economy Rates', 'icon' => '🛡️', 'type' => 'bowling'],
        'team-runs' => ['title' => 'Total Runs', 'icon' => '🛡️', 'type' => 'team'],
        'team-wickets' => ['title' => 'Total Wickets', 'icon' => '🎯', 'type' => 'team'],
        'team-fifties' => ['title' => 'Most Fifties (Team)', 'icon' => '🎖️', 'type' => 'team'],
        'team-centuries' => ['title' => 'Most Centuries (Team)', 'icon' => '💯', 'type' => 'team'],
        'highest-team-totals' => ['title' => 'Highest Team Totals', 'icon' => '🏆', 'type' => 'team'],
    ];

    $currentStatKey = $activeStat ?? 'most-runs';
    $statInfo = $statMap[$currentStatKey] ?? ['title' => ucwords(str_replace('-', ' ', $currentStatKey)), 'icon' => '📊', 'type' => 'batting'];
    $pageTitle = "{$tourName} {$statInfo['title']} — Series Statistics | CricketKaScore";
@endphp

@section('pageTitle', $pageTitle)
@section('canonical_url', url()->current())

@section('content')
<!-- CricTracker Breadcrumb Bar -->
<div style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color); padding: 10px 20px;">
    <div class="max-w-6xl mx-auto" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 0.82rem; font-weight: 700; color: var(--text-muted); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ url('/') }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('series') }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">Series</a>
            <span>&rsaquo;</span>
            <a href="{{ route('series.public.slug', ['slug' => $tournament->slug, 'id' => $tournament->id]) }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">{{ $tournament->name }}</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">{{ $statInfo['title'] }}</span>
        </div>

        <a href="{{ route('series.public.slug', ['slug' => $tournament->slug, 'id' => $tournament->id]) }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">
            <span>&larr;</span> Back to Series Stats Hub
        </a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 pb-28">

    <!-- Header Bar with Dynamic Stat Title and Dropdown Filters (Screenshot 2 Layout) -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-top-left-radius: 14px; border-top-right-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; border-bottom: 2px solid #2563eb;">
        <div>
            <h1 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin: 0; letter-spacing: -0.01em; display: flex; align-items: center; gap: 8px;">
                <span>{{ $statInfo['icon'] }}</span>
                <span>{{ strtoupper($tournament->name) }} <span style="color: #38bdf8;">{{ strtoupper($statInfo['title']) }}</span></span>
            </h1>
        </div>

        <!-- Filter Dropdowns Right (Screenshot 2 Top Right) -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            
            <!-- Team Filter Dropdown -->
            <select id="teamFilterSelect" onchange="filterLeaderboardByTeam(this.value)" style="background: var(--bg-card-secondary); color: var(--text-main); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; outline: none; cursor: pointer;">
                <option value="all">Select Team (All)</option>
                @foreach($teams as $tm)
                    <option value="{{ $tm->id }}">{{ $tm->name }}</option>
                @endforeach
            </select>

            <!-- Stat Category Switcher Dropdown -->
            <select onchange="if(this.value) window.location.href = this.value;" style="background: var(--bg-card-secondary); color: #38bdf8; border: 1px solid #2563eb; padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; outline: none; cursor: pointer;">
                <optgroup label="Batting Stats">
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'most-runs']) }}" {{ $currentStatKey === 'most-runs' ? 'selected' : '' }}>Most Runs</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'most-fours']) }}" {{ $currentStatKey === 'most-fours' ? 'selected' : '' }}>Most Fours</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'most-sixes']) }}" {{ $currentStatKey === 'most-sixes' ? 'selected' : '' }}>Most Sixes</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'most-fifties']) }}" {{ $currentStatKey === 'most-fifties' ? 'selected' : '' }}>Most Fifties</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'most-centuries']) }}" {{ $currentStatKey === 'most-centuries' ? 'selected' : '' }}>Most Centuries</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'fours-innings']) }}" {{ $currentStatKey === 'fours-innings' ? 'selected' : '' }}>Most Fours (Innings)</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'sixes-innings']) }}" {{ $currentStatKey === 'sixes-innings' ? 'selected' : '' }}>Most Sixes (Innings)</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'best-strike-rates']) }}" {{ $currentStatKey === 'best-strike-rates' ? 'selected' : '' }}>Best Strike Rates</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'highest-scores']) }}" {{ $currentStatKey === 'highest-scores' ? 'selected' : '' }}>Highest Scores</option>
                </optgroup>
                <optgroup label="Bowling Stats">
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'top-wickets']) }}" {{ $currentStatKey === 'top-wickets' ? 'selected' : '' }}>Top Wicket Takers</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'four-wickets']) }}" {{ $currentStatKey === 'four-wickets' ? 'selected' : '' }}>Four Wickets</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'five-wickets']) }}" {{ $currentStatKey === 'five-wickets' ? 'selected' : '' }}>Five Wickets</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'maidens']) }}" {{ $currentStatKey === 'maidens' ? 'selected' : '' }}>Maidens</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'best-bowling-avg']) }}" {{ $currentStatKey === 'best-bowling-avg' ? 'selected' : '' }}>Best Averages</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'best-figures']) }}" {{ $currentStatKey === 'best-figures' ? 'selected' : '' }}>Best Figures</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'best-economies']) }}" {{ $currentStatKey === 'best-economies' ? 'selected' : '' }}>Best Economy Rates</option>
                </optgroup>
                <optgroup label="Team Stats">
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'team-runs']) }}" {{ $currentStatKey === 'team-runs' ? 'selected' : '' }}>Total Runs</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'team-wickets']) }}" {{ $currentStatKey === 'team-wickets' ? 'selected' : '' }}>Total Wickets</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'team-fifties']) }}" {{ $currentStatKey === 'team-fifties' ? 'selected' : '' }}>Most Fifties (Team)</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'team-centuries']) }}" {{ $currentStatKey === 'team-centuries' ? 'selected' : '' }}>Most Centuries (Team)</option>
                    <option value="{{ route('series.stat.slug', ['slug' => $tournament->slug, 'id' => $tournament->id, 'stat' => 'highest-team-totals']) }}" {{ $currentStatKey === 'highest-team-totals' ? 'selected' : '' }}>Highest Team Totals</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- Leaderboard Table Container (Exact match with Screenshot 2) -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-top: none; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="overflow-x: auto;">

            @if($statInfo['type'] === 'batting')
                <!-- BATTING LEADERBOARD (Screenshot 2 Exact Columns) -->
                @php
                    $dataset = match($currentStatKey) {
                        'most-fours' => $mostFours,
                        'most-sixes' => $mostSixes,
                        'most-fifties' => $mostFifties,
                        'most-centuries' => $mostCenturies,
                        'best-strike-rates' => $bestStrikeRates,
                        default => $mostRuns,
                    };
                @endphp
                <table class="cricket-stat-table" style="min-width: 800px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #2563eb; color: #ffffff;">
                            <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Player</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Team</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">R</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Mat</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">BF</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Avg</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">H.S</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">SR</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">4s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">6s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">100s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">50s</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataset as $index => $b)
                        <tr class="stat-row" data-team-id="{{ $b->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <a href="{{ $b->player_url }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                    <span>🏏</span>
                                    <span>{{ $b->player_name }}</span>
                                </a>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">
                                    {{ $b->team_short_name }}
                                </span>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 900; color: {{ $currentStatKey === 'most-runs' ? '#38bdf8' : 'var(--text-main)' }}; text-align: center; font-size: 0.95rem;">{{ $b->runs }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->matches }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->balls }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: var(--text-main); text-align: center; font-size: 0.88rem;">{{ $b->average }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $b->highest_score }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: {{ $currentStatKey === 'best-strike-rates' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $b->strike_rate }}</td>
                            <td style="padding: 12px 12px; font-weight: {{ $currentStatKey === 'most-fours' ? '900' : '700' }}; color: {{ $currentStatKey === 'most-fours' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->fours }}</td>
                            <td style="padding: 12px 12px; font-weight: {{ $currentStatKey === 'most-sixes' ? '900' : '700' }}; color: {{ $currentStatKey === 'most-sixes' ? '#ef4444' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->sixes }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: {{ $currentStatKey === 'most-centuries' ? '#10b981' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->centuries ?: '--' }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: {{ $currentStatKey === 'most-fifties' ? '#38bdf8' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->fifties ?: '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.88rem;">
                                No batting records recorded yet for this series.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($statInfo['type'] === 'bowling')
                <!-- BOWLING LEADERBOARD (Screenshot 2 Bowling Columns) -->
                @php
                    $bowlingDataset = match($currentStatKey) {
                        'four-wickets' => $fourWickets,
                        'five-wickets' => $fiveWickets,
                        'maidens' => $mostMaidens,
                        'best-bowling-avg' => $bestBowlingAverages,
                        'best-economies' => $bestEconomies,
                        default => $topBowlers,
                    };
                @endphp
                <table class="cricket-stat-table" style="min-width: 800px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #2563eb; color: #ffffff;">
                            <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Player</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Team</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Wkts</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Mat</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Overs</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Mdns</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Runs</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Avg</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">BBI</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Econ</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">4w</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">5w</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bowlingDataset as $index => $w)
                        <tr class="stat-row" data-team-id="{{ $w->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <a href="{{ $w->player_url }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                    <span>🎯</span>
                                    <span>{{ $w->player_name }}</span>
                                </a>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ $w->team_short_name }}</span>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 900; color: #ef4444; text-align: center; font-size: 1.05rem;">{{ $w->wickets }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->matches }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->overs }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: {{ $currentStatKey === 'maidens' ? '#38bdf8' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->maidens }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->runs }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: {{ $currentStatKey === 'best-bowling-avg' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $w->average }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $w->best_figure }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: {{ $currentStatKey === 'best-economies' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $w->economy }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: {{ $currentStatKey === 'four-wickets' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->four_wickets ?: '--' }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: {{ $currentStatKey === 'five-wickets' ? '#10b981' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->five_wickets ?: '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No bowling records recorded yet for this series.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($statInfo['type'] === 'innings_batting')
                <!-- INNINGS BATTING RECORDS -->
                @php
                    $innsBatting = match($currentStatKey) {
                        'fours-innings' => $mostFoursInnings,
                        'sixes-innings' => $mostSixesInnings,
                        default => $highestInnings,
                    };
                @endphp
                <table class="cricket-stat-table" style="min-width: 700px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #2563eb; color: #ffffff;">
                            <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Player</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Team</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Runs</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Balls</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">4s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">6s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">SR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($innsBatting->take(30) as $index => $r)
                        <tr class="stat-row" data-team-id="{{ $r->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <a href="{{ $r->player_url }}" style="text-decoration: none; color: inherit;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">{{ $r->player_name }}</a>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ $r->team_short_name }}</span>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 900; color: #38bdf8; text-align: center; font-size: 1.05rem;">{{ $r->runs }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $r->balls }}</td>
                            <td style="padding: 12px 12px; font-weight: {{ $currentStatKey === 'fours-innings' ? '900' : '700' }}; color: {{ $currentStatKey === 'fours-innings' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $r->fours }}</td>
                            <td style="padding: 12px 12px; font-weight: {{ $currentStatKey === 'sixes-innings' ? '900' : '700' }}; color: {{ $currentStatKey === 'sixes-innings' ? '#ef4444' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $r->sixes }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: #10b981; text-align: center; font-size: 0.88rem;">{{ $r->strike_rate }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No innings records recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($statInfo['type'] === 'innings_bowling')
                <!-- INNINGS BOWLING FIGURES -->
                <table class="cricket-stat-table" style="min-width: 700px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #2563eb; color: #ffffff;">
                            <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Player</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Team</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Figures (W/R)</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Overs</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Econ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bestFigures->take(30) as $index => $r)
                        <tr class="stat-row" data-team-id="{{ $r->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <a href="{{ $r->player_url }}" style="text-decoration: none; color: inherit;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">{{ $r->player_name }}</a>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ $r->team_short_name }}</span>
                            </td>
                            <td style="padding: 12px 12px; font-weight: 900; color: #38bdf8; text-align: center; font-size: 1.05rem;">{{ $r->wickets }}/{{ $r->runs }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $r->overs }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: #10b981; text-align: center; font-size: 0.88rem;">{{ $r->economy }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No figures recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($statInfo['type'] === 'team')
                <!-- TEAM LEADERBOARDS -->
                @php
                    $teamDataset = match($currentStatKey) {
                        'team-wickets' => $teamWickets,
                        'team-fifties' => $teamFifties,
                        'team-centuries' => $teamCenturies,
                        'highest-team-totals' => $teamHighestTotals,
                        default => $teamRuns,
                    };
                @endphp
                <table class="cricket-stat-table" style="min-width: 650px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #2563eb; color: #ffffff;">
                            <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                            <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Team</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Matches</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Total Runs</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Highest Total</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">50s</th>
                            <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">100s</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamDataset as $index => $t)
                        <tr class="stat-row" data-team-id="{{ $t['team_id'] }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $t['team_name'] }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $t['matches_played'] }}</td>
                            <td style="padding: 12px 12px; font-weight: 900; color: #38bdf8; text-align: center; font-size: 1.05rem;">{{ $t['total_runs'] }}</td>
                            <td style="padding: 12px 12px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.9rem;">{{ $t['highest_total'] }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $t['fifties'] }}</td>
                            <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $t['centuries'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No records available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

        </div>
    </div>

</div>

<!-- Team Filter Client Script -->
<script>
function filterLeaderboardByTeam(teamId) {
    const rows = document.querySelectorAll('tbody tr.stat-row');
    let visibleCount = 0;
    rows.forEach(row => {
        const rowTeamId = row.getAttribute('data-team-id');
        if (teamId === 'all' || rowTeamId === teamId || !rowTeamId) {
            row.style.display = '';
            visibleCount++;
            const indexCell = row.querySelector('.stat-index-cell');
            if (indexCell) {
                indexCell.textContent = visibleCount;
            }
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
