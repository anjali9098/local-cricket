@extends('layouts.app')

@php
    $tourName = trim($tournament->name);
    $pageTitle = $tourName . ' — Series Home, Stats, Schedule, Points Table & Squads | CricketKaScore';
    $metaDesc = "Follow {$tourName} on CricketKaScore. Access full match schedule, live standings points table, batting & bowling statistics, team squads, and latest news & articles.";
    $metaKeywords = "{$tourName}, {$tourName} schedule, {$tourName} points table, {$tourName} stats, {$tourName} most runs, {$tourName} most wickets, {$tourName} squads, cricket series, CricketKaScore";
    $canonicalUrl = $tournament->url;
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'website')
@section('og_title', $pageTitle)
@section('og_description', $metaDesc)
@section('og_url', $canonicalUrl)

@section('content')
<!-- Breadcrumb Bar -->
<div style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color); padding: 10px 20px;">
    <div class="max-w-6xl mx-auto" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 0.82rem; font-weight: 700; color: var(--text-muted); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ url('/') }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('series') }}" style="color: var(--text-muted); text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">Series</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">{{ $tournament->name }}</span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('series') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                <span>&larr;</span> All Series
            </a>
        </div>
    </div>
</div>

<!-- Header Hero Banner -->
<div class="tournament-hero-banner" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 28px 20px; transition: background-color 0.3s ease, border-color 0.3s ease;">
    <div class="max-w-6xl mx-auto">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.82rem; font-weight: 700; flex-wrap: wrap;">
                    <span style="background: #2563eb; color: #ffffff; padding: 3px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em;">
                        {{ strtoupper($tournament->format ?: 'T20') }}
                    </span>
                    <span style="border: 1px solid var(--border-color); background: var(--bg-card-secondary); color: var(--text-muted); padding: 3px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.72rem; font-weight: 700;">
                        {{ strtoupper($tournament->status ?: 'ONGOING') }}
                    </span>
                    @if(!empty($tournament->city) || !empty($tournament->hosting_country))
                        <span style="color: var(--text-dim); display: inline-flex; align-items: center; gap: 4px; font-size: 0.82rem; font-weight: 600;">
                            <span>📍</span> {{ $tournament->city ? $tournament->city . ($tournament->hosting_country ? ', ' . $tournament->hosting_country : '') : $tournament->hosting_country }}
                        </span>
                    @endif
                </div>
                
                <h1 style="font-size: 2.1rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: -0.02em; margin: 0 0 10px 0; line-height: 1.2;">
                    {{ $tournament->name }}
                </h1>
                
                <div style="font-size: 0.88rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    @if($tournament->start_date)
                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                            <span>📅</span> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') }}
                            @if($tournament->end_date)
                                &mdash; {{ \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') }}
                            @endif
                        </span>
                    @endif
                    <span style="color: var(--text-dim); font-size: 0.82rem;">
                        ⚔️ <strong>{{ $tournament->matches->count() }}</strong> Matches &bull; 👥 <strong>{{ count($teams) }}</strong> Teams
                    </span>
                </div>
            </div>

            <!-- Series Share Button -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Series link copied to clipboard!');" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🔗</span> Share Series
                </button>
            </div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 pb-28">

    <!-- 7 ORDERED TABS NAVIGATION (1. Home, 2. Stats, 3. Fixtures, 4. Points Table, 5. Players, 6. Teams, 7. News & Articles) -->
    @php
        $curTab = $activeTab ?? 'home';
        if ($curTab === 'points-table') $curTab = 'points';
        $seriesBaseCanonicalUrl = url('/series/' . $tournament->slug . '-' . $tournament->id);
    @endphp
    <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;" class="no-scrollbar">
            
            <!-- TAB 1: HOME -->
            <button type="button" onclick="switchSeriesTab('tab-home')" id="btn-tab-home" class="series-tab-btn {{ $curTab === 'home' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'home' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'home' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'home' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>🏠</span> HOME
            </button>

            <!-- TAB 2: STATS -->
            <button type="button" onclick="openStatsTabHub()" id="btn-tab-stats" class="series-tab-btn {{ $curTab === 'stats' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'stats' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'stats' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'stats' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>📊</span> STATS
            </button>

            <!-- TAB 3: FIXTURES -->
            <button type="button" onclick="switchSeriesTab('tab-fixtures')" id="btn-tab-fixtures" class="series-tab-btn {{ $curTab === 'fixtures' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'fixtures' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'fixtures' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'fixtures' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>📅</span> FIXTURES <span style="background: var(--bg-card-secondary); font-size: 0.70rem; padding: 1px 6px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ $tournament->matches->count() }}</span>
            </button>

            <!-- TAB 4: POINTS TABLE -->
            <button type="button" onclick="switchSeriesTab('tab-points')" id="btn-tab-points" class="series-tab-btn {{ $curTab === 'points' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'points' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'points' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'points' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>🏆</span> POINTS TABLE
            </button>

            <!-- TAB 5: PLAYERS -->
            <button type="button" onclick="switchSeriesTab('tab-players')" id="btn-tab-players" class="series-tab-btn {{ $curTab === 'players' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'players' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'players' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'players' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>👤</span> PLAYERS <span id="navPlayersBadge" style="background: var(--bg-card-secondary); font-size: 0.70rem; padding: 1px 6px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($players) }}</span>
            </button>

            <!-- TAB 6: TEAMS -->
            <button type="button" onclick="switchSeriesTab('tab-teams')" id="btn-tab-teams" class="series-tab-btn {{ $curTab === 'teams' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'teams' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'teams' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'teams' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>👥</span> TEAMS <span style="background: var(--bg-card-secondary); font-size: 0.70rem; padding: 1px 6px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($teams) }}</span>
            </button>

            <!-- TAB 7: NEWS & ARTICLES -->
            <button type="button" onclick="switchSeriesTab('tab-news')" id="btn-tab-news" class="series-tab-btn {{ $curTab === 'news' ? 'active-tab-btn' : '' }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 0.80rem; cursor: pointer; border: 1px solid {{ $curTab === 'news' ? '#2563eb' : 'var(--border-color)' }}; background: {{ $curTab === 'news' ? '#2563eb' : 'var(--bg-card)' }}; color: {{ $curTab === 'news' ? '#ffffff' : 'var(--text-muted)' }}; transition: all 0.2s;">
                <span>📰</span> NEWS &amp; ARTICLES <span style="background: var(--bg-card-secondary); font-size: 0.70rem; padding: 1px 6px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ (isset($seriesNews) ? $seriesNews->count() : 0) + (isset($seriesArticles) ? $seriesArticles->count() : 0) }}</span>
            </button>

        </div>
    </div>

    <!-- =========================================================================
         TAB 1: HOME (Comprehensive Series Overview, Key Facts, Rules & Tournament Hub)
         ========================================================================= -->
    <div id="tab-home" class="series-tab-pane" style="{{ $curTab === 'home' ? '' : 'display: none;' }}">
        
        <!-- Live Matches in this Series (if active) -->
        @php
            $liveSeriesMatches = $tournament->matches->whereIn('status', ['ongoing', 'live']);
        @endphp
        @if($liveSeriesMatches->count() > 0)
        <div class="mb-6">
            <h3 style="font-size: 0.92rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ef4444; animation: pulse 2s infinite;"></span>
                LIVE NOW IN THIS SERIES
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($liveSeriesMatches as $liveMatch)
                <a href="{{ $liveMatch->url }}" style="display: block; background: var(--bg-card); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 14px; padding: 16px; text-decoration: none; box-shadow: var(--shadow-sm); transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                    <div style="display: inline-block; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px;">&bull; LIVE</div>
                    <div style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin-bottom: 8px;">
                        {{ $liveMatch->team1->name ?? 'TBA' }} <span style="color: #ef4444;">VS</span> {{ $liveMatch->team2->name ?? 'TBA' }}
                    </div>
                    <div style="font-size: 0.82rem; color: #38bdf8; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                        <span>View Live Scorecard</span> &rarr;
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Series Master Key Details Card -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
                    <div>
                        <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #38bdf8; letter-spacing: 0.05em; margin-bottom: 4px;">
                            TOURNAMENT PROFILE
                        </div>
                        <h2 style="font-size: 1.35rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: -0.01em;">
                            {{ $tournament->name }} Overview
                        </h2>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background: #2563eb; color: #ffffff; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.75rem; font-weight: 800;">
                            {{ strtoupper($tournament->format ?: 'T20') }} FORMAT
                        </span>
                        <span style="border: 1px solid var(--border-color); background: var(--bg-card-secondary); color: #22c55e; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.75rem; font-weight: 800;">
                            {{ strtoupper($tournament->status ?: 'ONGOING') }}
                        </span>
                    </div>
                </div>

                <!-- 8 Comprehensive Fact Tiles -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 22px;">
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Cricket Format</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ strtoupper($tournament->format ?: 'T20') }} Cricket</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Host Country &amp; City</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ $tournament->city ? $tournament->city . ($tournament->hosting_country ? ', ' . $tournament->hosting_country : '') : ($tournament->hosting_country ?: 'International') }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Start Date</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') : 'Announced' }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">End Date</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') : 'Final TBA' }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Total Matches</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: #38bdf8;">{{ $tournament->matches->count() }} Fixtures</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Participating Teams</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: #38bdf8;">{{ count($teams) }} Teams</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Category / Series Type</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ strtoupper($tournament->category ?: ($tournament->series_type ?: 'Championship')) }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Registered Players</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: #38bdf8;">{{ count($players) }} Squad Players</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Overs / Innings</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ strtolower($tournament->format) == 'odi' ? '50 Overs' : (strtolower($tournament->format) == 'test' ? 'Unlimited' : '20 Overs') }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Mandatory Powerplay</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-main);">{{ strtolower($tournament->format) == 'odi' ? 'Overs 1 - 10' : (strtolower($tournament->format) == 'test' ? 'N/A' : 'Overs 1 - 6') }}</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Tiebreaker Rule</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: #4ade80;">Super Over Applicable</div>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="color: var(--text-dim); font-size: 0.72rem; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">DRS Availability</div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: #a855f7;">Ball Tracking &amp; UltraEdge</div>
                    </div>
                </div>

                @if(!empty($tournament->description))
                <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <h4 style="font-size: 0.88rem; font-weight: 800; color: #38bdf8; text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 0.04em;">
                        About This Series
                    </h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin: 0;">
                        {!! nl2br(e($tournament->description)) !!}
                    </p>
                </div>
                @endif

                <!-- Tournament Structure & Rules Card -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px;">
                        <h4 style="font-size: 0.88rem; font-weight: 800; color: #4ade80; text-transform: uppercase; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                            <span>⚖️</span> Points &amp; Qualification System
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; color: var(--text-muted); font-size: 0.84rem; line-height: 1.7;">
                            <li><strong style="color: var(--text-main);">Win:</strong> 2 Points awarded on match victory.</li>
                            <li><strong style="color: var(--text-main);">Tie / No Result:</strong> 1 Point to both teams in case of abandoned matches.</li>
                            <li><strong style="color: var(--text-main);">Loss:</strong> 0 Points.</li>
                            <li><strong style="color: var(--text-main);">Net Run Rate (NRR):</strong> Used to determine higher rank on points table in case of tie.</li>
                        </ul>
                    </div>

                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px;">
                        <h4 style="font-size: 0.88rem; font-weight: 800; color: #a855f7; text-transform: uppercase; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                            <span>🚀</span> Direct Section Shortcuts
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <button type="button" onclick="openStatsTabHub()" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                📊 Stats Leaderboards
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-fixtures')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                📅 Match Schedule
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-points')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                🏆 Points Standings
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-teams')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                👥 Teams &amp; Squads
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-players')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                👤 All Players
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-news')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                📰 News &amp; Articles
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- =========================================================================
         TAB 2: STATS HUB & LEADERBOARD (In-Place Interactive Leaderboard)
         ========================================================================= -->
    <style>
    .stat-hub-card-link {
        width: 100%;
        text-align: left;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid var(--border-color);
        background: var(--bg-card-secondary);
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .stat-hub-card-link:hover {
        background: rgba(37, 99, 235, 0.15);
        border-color: #2563eb;
        color: #38bdf8;
        transform: translateX(3px);
    }
    .stat-hub-card-link .stat-hub-arrow {
        color: var(--text-dim);
        font-size: 1.15rem;
        font-weight: 900;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .stat-hub-card-link:hover .stat-hub-arrow {
        color: #38bdf8;
        transform: translateX(2px);
    }
    .stat-pill-btn {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 800;
        font-size: 0.76rem;
        padding: 5px 11px;
        border-radius: 9999px;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }
    .stat-pill-btn:hover {
        border-color: #38bdf8;
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.1);
    }
    .stat-pill-btn.active-stat-pill {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    </style>

    <div id="tab-stats" class="series-tab-pane" style="{{ $curTab === 'stats' ? '' : 'display: none;' }}">
        
        <!-- 1. STATS CATEGORIES 3-COLUMN HUB OVERVIEW -->
        <div id="stats-category-overview">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                    <span>📊</span> SERIES STATISTICS &amp; LEADERBOARDS
                </h3>
                <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 700;">Click any category to view full leaderboard</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
                
                <!-- COLUMN 1: BATTING STATS -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                    <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>🏏</span> Batting Stats
                    </div>
                    <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <a href="javascript:void(0)" onclick="openStatDetail('most-runs')" class="stat-hub-card-link">
                            <span>🏏 Most Runs</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('most-fours')" class="stat-hub-card-link">
                            <span>⚡ Most Fours</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('most-sixes')" class="stat-hub-card-link">
                            <span>💥 Most Sixes</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('most-fifties')" class="stat-hub-card-link">
                            <span>🎖️ Most Fifties</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('most-centuries')" class="stat-hub-card-link">
                            <span>💯 Most Centuries</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('fours-innings')" class="stat-hub-card-link">
                            <span>⚡ Most Fours (Innings)</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('sixes-innings')" class="stat-hub-card-link">
                            <span>💥 Most Sixes (Innings)</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('best-strike-rates')" class="stat-hub-card-link">
                            <span>🚀 Best Strike Rates</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('highest-scores')" class="stat-hub-card-link">
                            <span>👑 Highest Individual Scores</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                    </div>
                </div>

                <!-- COLUMN 2: BOWLING STATS -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                    <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>🎯</span> Bowling Stats
                    </div>
                    <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <a href="javascript:void(0)" onclick="openStatDetail('top-wickets')" class="stat-hub-card-link">
                            <span>🎯 Top Wicket Takers</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('four-wickets')" class="stat-hub-card-link">
                            <span>🔥 Four Wickets</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('five-wickets')" class="stat-hub-card-link">
                            <span>🌟 Five Wickets</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('maidens')" class="stat-hub-card-link">
                            <span>🛡️ Maidens</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('best-figures')" class="stat-hub-card-link">
                            <span>💎 Best Bowling Figures</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('best-economy')" class="stat-hub-card-link">
                            <span>🛡️ Best Economy</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('best-bowling-averages')" class="stat-hub-card-link">
                            <span>📊 Best Bowling Averages</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                    </div>
                </div>

                <!-- COLUMN 3: TEAM STATS -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                    <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>👥</span> Team Stats
                    </div>
                    <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <a href="javascript:void(0)" onclick="openStatDetail('team-runs')" class="stat-hub-card-link">
                            <span>🛡️ Total Runs</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('team-wickets')" class="stat-hub-card-link">
                            <span>🎯 Total Wickets</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('highest-team-totals')" class="stat-hub-card-link">
                            <span>🏆 Highest Team Totals</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('team-fifties')" class="stat-hub-card-link">
                            <span>🎖️ Most Fifties</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                        <a href="javascript:void(0)" onclick="openStatDetail('team-centuries')" class="stat-hub-card-link">
                            <span>💯 Most Centuries</span>
                            <span class="stat-hub-arrow">&rsaquo;</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. IN-PLACE DETAILED STAT LEADERBOARD VIEW (HEADER + TABS STAY INTACT!) -->
        <div id="stats-detail-container" style="display: none; margin-bottom: 28px;">
            
            <!-- Top Controls Header Bar -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-top-left-radius: 14px; border-top-right-radius: 14px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border-color);">
                
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <button type="button" onclick="closeStatDetail()" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: #38bdf8; font-weight: 800; font-size: 0.82rem; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.background='rgba(56,189,248,0.1)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-card-secondary)';">
                        <span>&larr;</span> Back to Stats Categories
                    </button>
                    
                    <h3 id="stat-detail-title" style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin: 0; letter-spacing: -0.01em; display: flex; align-items: center; gap: 8px;">
                        <span id="stat-detail-icon">🏏</span>
                        <span id="stat-detail-name">Most Runs</span>
                    </h3>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- Team Filter Dropdown -->
                    <select id="statTeamFilterSelect" onchange="filterStatLeaderboardByTeam(this.value)" style="background: var(--bg-card-secondary); color: var(--text-main); border: 1px solid var(--border-color); padding: 7px 12px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; outline: none; cursor: pointer;">
                        <option value="all">Select Team (All)</option>
                        @foreach($teams as $tm)
                            <option value="{{ $tm->id }}">{{ $tm->name }}</option>
                        @endforeach
                    </select>

                    <!-- Stat Category Switcher Dropdown -->
                    <select id="statCategorySwitcher" onchange="openStatDetail(this.value)" style="background: var(--bg-card-secondary); color: #38bdf8; border: 1px solid #2563eb; padding: 7px 12px; border-radius: 8px; font-size: 0.82rem; font-weight: 800; outline: none; cursor: pointer;">
                        <optgroup label="Batting Stats">
                            <option value="most-runs">Most Runs</option>
                            <option value="most-fours">Most Fours</option>
                            <option value="most-sixes">Most Sixes</option>
                            <option value="most-fifties">Most Fifties</option>
                            <option value="most-centuries">Most Centuries</option>
                            <option value="fours-innings">Most Fours (Innings)</option>
                            <option value="sixes-innings">Most Sixes (Innings)</option>
                            <option value="best-strike-rates">Best Strike Rates</option>
                            <option value="highest-scores">Highest Individual Scores</option>
                        </optgroup>
                        <optgroup label="Bowling Stats">
                            <option value="top-wickets">Top Wicket Takers</option>
                            <option value="four-wickets">Four Wickets</option>
                            <option value="five-wickets">Five Wickets</option>
                            <option value="maidens">Maidens</option>
                            <option value="best-bowling-averages">Best Bowling Averages</option>
                            <option value="best-figures">Best Bowling Figures</option>
                            <option value="best-economy">Best Economy Rates</option>
                        </optgroup>
                        <optgroup label="Team Stats">
                            <option value="team-runs">Total Runs</option>
                            <option value="team-wickets">Total Wickets</option>
                            <option value="highest-team-totals">Highest Team Totals</option>
                            <option value="team-fifties">Most Fifties (Team)</option>
                            <option value="team-centuries">Most Centuries (Team)</option>
                        </optgroup>
                    </select>
                </div>

            </div>

            <!-- Quick Stat Switcher Badges Bar -->
            <div style="background: var(--bg-card-secondary); border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 2px solid #2563eb; padding: 10px 16px; display: flex; align-items: center; gap: 8px; overflow-x: auto; scrollbar-width: none;" class="no-scrollbar">
                <span style="font-size: 0.72rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; white-space: nowrap; margin-right: 4px;">Quick Switch:</span>
                <button type="button" onclick="openStatDetail('most-runs')" class="stat-pill-btn" data-stat="most-runs">🏏 Most Runs</button>
                <button type="button" onclick="openStatDetail('most-fours')" class="stat-pill-btn" data-stat="most-fours">⚡ Most 4s</button>
                <button type="button" onclick="openStatDetail('most-sixes')" class="stat-pill-btn" data-stat="most-sixes">💥 Most 6s</button>
                <button type="button" onclick="openStatDetail('highest-scores')" class="stat-pill-btn" data-stat="highest-scores">👑 Highest Scores</button>
                <button type="button" onclick="openStatDetail('best-strike-rates')" class="stat-pill-btn" data-stat="best-strike-rates">🚀 Best SR</button>
                <button type="button" onclick="openStatDetail('top-wickets')" class="stat-pill-btn" data-stat="top-wickets">🎯 Top Wickets</button>
                <button type="button" onclick="openStatDetail('best-figures')" class="stat-pill-btn" data-stat="best-figures">💎 Best Figures</button>
                <button type="button" onclick="openStatDetail('best-economy')" class="stat-pill-btn" data-stat="best-economy">🛡️ Best Economy</button>
                <button type="button" onclick="openStatDetail('team-runs')" class="stat-pill-btn" data-stat="team-runs">👥 Total Runs</button>
                <button type="button" onclick="openStatDetail('highest-team-totals')" class="stat-pill-btn" data-stat="highest-team-totals">🏆 Highest Totals</button>
            </div>

            <!-- Leaderboard Tables Container -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-top: none; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="overflow-x: auto;">
                    
                    <!-- BATTING STATS TABLES -->
                    @php
                        $battingStatDatasets = [
                            'most-runs' => ['data' => $mostRuns, 'highlight' => 'runs'],
                            'most-fours' => ['data' => $mostFours, 'highlight' => 'fours'],
                            'most-sixes' => ['data' => $mostSixes, 'highlight' => 'sixes'],
                            'most-fifties' => ['data' => $mostFifties, 'highlight' => 'fifties'],
                            'most-centuries' => ['data' => $mostCenturies, 'highlight' => 'centuries'],
                            'best-strike-rates' => ['data' => $bestStrikeRates, 'highlight' => 'strike_rate'],
                        ];
                    @endphp

                    @foreach($battingStatDatasets as $key => $bMeta)
                    <div id="stat-pane-{{ $key }}" class="stat-table-pane" style="display: none;">
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
                                @forelse($bMeta['data'] as $index => $b)
                                <tr class="stat-row" data-team-id="{{ $b->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                                    <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                                    <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                        <a href="{{ $b->player_url }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                            <span>🏏</span>
                                            <span>{{ $b->player_name }}</span>
                                        </a>
                                    </td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                        <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ $b->team_short_name }}</span>
                                    </td>
                                    <td style="padding: 12px 12px; font-weight: 900; color: {{ $bMeta['highlight'] === 'runs' ? '#38bdf8' : 'var(--text-main)' }}; text-align: center; font-size: 0.95rem;">{{ $b->runs }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->matches }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->balls }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: var(--text-main); text-align: center; font-size: 0.88rem;">{{ $b->average }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $b->highest_score }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $bMeta['highlight'] === 'strike_rate' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $b->strike_rate }}</td>
                                    <td style="padding: 12px 12px; font-weight: {{ $bMeta['highlight'] === 'fours' ? '900' : '700' }}; color: {{ $bMeta['highlight'] === 'fours' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->fours }}</td>
                                    <td style="padding: 12px 12px; font-weight: {{ $bMeta['highlight'] === 'sixes' ? '900' : '700' }}; color: {{ $bMeta['highlight'] === 'sixes' ? '#ef4444' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->sixes }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $bMeta['highlight'] === 'centuries' ? '#10b981' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->centuries ?: '--' }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $bMeta['highlight'] === 'fifties' ? '#38bdf8' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $b->fifties ?: '--' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.88rem;">No batting records recorded yet for this series.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endforeach

                    <!-- INNINGS BATTING TABLES -->
                    @php
                        $innsBattingDatasets = [
                            'highest-scores' => ['data' => $highestInnings, 'title' => 'Highest Individual Scores'],
                            'fours-innings' => ['data' => $mostFoursInnings, 'title' => 'Most Fours (Innings)', 'highlight' => 'fours'],
                            'sixes-innings' => ['data' => $mostSixesInnings, 'title' => 'Most Sixes (Innings)', 'highlight' => 'sixes'],
                        ];
                    @endphp

                    @foreach($innsBattingDatasets as $key => $iMeta)
                    <div id="stat-pane-{{ $key }}" class="stat-table-pane" style="display: none;">
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
                                @forelse($iMeta['data']->take(30) as $index => $r)
                                <tr class="stat-row" data-team-id="{{ $r->team_id }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                                    <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                                    <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                        <a href="{{ $r->player_url }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                            <span>👑</span>
                                            <span>{{ $r->player_name }}</span>
                                        </a>
                                    </td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: var(--text-muted); text-align: center; font-size: 0.85rem;">
                                        <span style="background: var(--bg-card-secondary); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ $r->team_short_name }}</span>
                                    </td>
                                    <td style="padding: 12px 12px; font-weight: 900; color: #38bdf8; text-align: center; font-size: 1.05rem;">{{ $r->runs }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $r->balls }}</td>
                                    <td style="padding: 12px 12px; font-weight: {{ ($iMeta['highlight'] ?? '') === 'fours' ? '900' : '700' }}; color: {{ ($iMeta['highlight'] ?? '') === 'fours' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $r->fours }}</td>
                                    <td style="padding: 12px 12px; font-weight: {{ ($iMeta['highlight'] ?? '') === 'sixes' ? '900' : '700' }}; color: {{ ($iMeta['highlight'] ?? '') === 'sixes' ? '#ef4444' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $r->sixes }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: #10b981; text-align: center; font-size: 0.88rem;">{{ $r->strike_rate }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No innings records recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endforeach

                    <!-- BOWLING STATS TABLES -->
                    @php
                        $bowlingStatDatasets = [
                            'top-wickets' => ['data' => $topBowlers, 'highlight' => 'wickets'],
                            'four-wickets' => ['data' => $fourWickets, 'highlight' => 'four_wickets'],
                            'five-wickets' => ['data' => $fiveWickets, 'highlight' => 'five_wickets'],
                            'maidens' => ['data' => $mostMaidens, 'highlight' => 'maidens'],
                            'best-bowling-averages' => ['data' => $bestBowlingAverages, 'highlight' => 'average'],
                            'best-economy' => ['data' => $bestEconomies, 'highlight' => 'economy'],
                        ];
                    @endphp

                    @foreach($bowlingStatDatasets as $key => $wMeta)
                    <div id="stat-pane-{{ $key }}" class="stat-table-pane" style="display: none;">
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
                                @forelse($wMeta['data'] as $index => $w)
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
                                    <td style="padding: 12px 12px; font-weight: 700; color: {{ $wMeta['highlight'] === 'maidens' ? '#38bdf8' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->maidens }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->runs }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $wMeta['highlight'] === 'average' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $w->average }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $w->best_figure }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $wMeta['highlight'] === 'economy' ? '#10b981' : 'var(--text-main)' }}; text-align: center; font-size: 0.88rem;">{{ $w->economy }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: {{ $wMeta['highlight'] === 'four_wickets' ? '#f59e0b' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->four_wickets ?: '--' }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: {{ $wMeta['highlight'] === 'five_wickets' ? '#10b981' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $w->five_wickets ?: '--' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No bowling records recorded yet for this series.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endforeach

                    <!-- INNINGS BOWLING FIGURES -->
                    <div id="stat-pane-best-figures" class="stat-table-pane" style="display: none;">
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
                                        <a href="{{ $r->player_url }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                            <span>💎</span>
                                            <span>{{ $r->player_name }}</span>
                                        </a>
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
                    </div>

                    <!-- TEAM STATS TABLES -->
                    @php
                        $teamStatDatasets = [
                            'team-runs' => ['data' => $teamRuns, 'highlight' => 'total_runs'],
                            'team-wickets' => ['data' => $teamWickets, 'highlight' => 'total_wickets_lost'],
                            'highest-team-totals' => ['data' => $teamHighestTotals, 'highlight' => 'highest_total'],
                            'team-fifties' => ['data' => $teamFifties, 'highlight' => 'fifties'],
                            'team-centuries' => ['data' => $teamCenturies, 'highlight' => 'centuries'],
                        ];
                    @endphp

                    @foreach($teamStatDatasets as $key => $tMeta)
                    <div id="stat-pane-{{ $key }}" class="stat-table-pane" style="display: none;">
                        <table class="cricket-stat-table" style="min-width: 650px; width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #2563eb; color: #ffffff;">
                                    <th style="padding: 12px 14px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; width: 50px; text-align: center;">No</th>
                                    <th style="padding: 12px 16px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase;">Team</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Matches</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Total Runs</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Wkts Lost</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">Highest Total</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">50s</th>
                                    <th style="padding: 12px 12px; font-size: 0.78rem; font-weight: 900; text-transform: uppercase; text-align: center;">100s</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tMeta['data'] as $index => $t)
                                <tr class="stat-row" data-team-id="{{ $t['team_id'] }}" style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                                    <td class="stat-index-cell" style="padding: 12px 14px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem; text-align: center;">{{ $index + 1 }}</td>
                                    <td style="padding: 12px 16px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $t['team_name'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $t['matches_played'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 900; color: {{ $tMeta['highlight'] === 'total_runs' ? '#38bdf8' : 'var(--text-main)' }}; text-align: center; font-size: 1.05rem;">{{ $t['total_runs'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 900; color: {{ $tMeta['highlight'] === 'total_wickets_lost' ? '#ef4444' : 'var(--text-muted)' }}; text-align: center; font-size: 0.95rem;">{{ $t['total_wickets_lost'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 800; color: {{ $tMeta['highlight'] === 'highest_total' ? '#2563eb' : 'var(--text-main)' }}; text-align: center; font-size: 0.9rem;">{{ $t['highest_total'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: {{ $tMeta['highlight'] === 'fifties' ? '#38bdf8' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $t['fifties'] }}</td>
                                    <td style="padding: 12px 12px; font-weight: 700; color: {{ $tMeta['highlight'] === 'centuries' ? '#10b981' : 'var(--text-muted)' }}; text-align: center; font-size: 0.88rem;">{{ $t['centuries'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600;">No records available.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endforeach

                </div>
            </div>

        </div>

    </div>

    <!-- =========================================================================
         TAB 3: FIXTURES & SCHEDULE
         ========================================================================= -->
    <div id="tab-fixtures" class="series-tab-pane" style="{{ $curTab === 'fixtures' ? '' : 'display: none;' }}">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>📅</span> FULL SERIES FIXTURES &amp; SCHEDULE ({{ $tournament->matches->count() }})
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($tournament->matches as $m)
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 12px; transition: border-color 0.2s ease;">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: var(--text-dim); flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span>📍 {{ $m->venue->name ?? ($tournament->city ?? 'Cricket Ground') }}</span>
                            @if($m->match_date)
                                <span>&bull; {{ \Carbon\Carbon::parse($m->match_date)->format('D, d M Y • h:i A') }}</span>
                            @endif
                        </div>
                        <div>
                            <span style="background: {{ $m->effective_status === 'completed' ? '#059669' : ($m->effective_status === 'live' ? '#ef4444' : '#2563eb') }}; color: #ffffff; padding: 2px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                                {{ $m->effective_status }}
                            </span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 16px;">
                        <!-- Team 1 -->
                        <div style="display: flex; align-items: center; gap: 10px; justify-content: flex-start;">
                            @if(!empty($m->team1->logo))
                                <img src="{{ $m->team1->logo }}" alt="{{ $m->team1->name }}" style="width: 32px; height: 32px; border-radius: 6px; object-fit: contain;" onerror="this.style.display='none';">
                            @endif
                            <div>
                                <div style="font-weight: 900; font-size: 1rem; color: var(--text-main);">{{ $m->team1->name ?? 'Team 1' }}</div>
                                <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-muted);">
                                    {{ $m->team1_score ? $m->team1_score . ($m->team1_wickets !== null ? '/' . $m->team1_wickets : '') : '-' }}
                                    @if($m->team1_overs) <span style="font-size: 0.72rem; color: var(--text-dim);">({{ $m->team1_overs }} ov)</span> @endif
                                </div>
                            </div>
                        </div>

                        <!-- VS Badge -->
                        <div style="font-weight: 900; font-size: 0.88rem; color: var(--text-dim); background: var(--bg-card-secondary); padding: 4px 10px; border-radius: 9999px; border: 1px solid var(--border-color);">
                            VS
                        </div>

                        <!-- Team 2 -->
                        <div style="display: flex; align-items: center; gap: 10px; justify-content: flex-end; text-align: right;">
                            <div>
                                <div style="font-weight: 900; font-size: 1rem; color: var(--text-main);">{{ $m->team2->name ?? 'Team 2' }}</div>
                                <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-muted);">
                                    {{ $m->team2_score ? $m->team2_score . ($m->team2_wickets !== null ? '/' . $m->team2_wickets : '') : '-' }}
                                    @if($m->team2_overs) <span style="font-size: 0.72rem; color: var(--text-dim);">({{ $m->team2_overs }} ov)</span> @endif
                                </div>
                            </div>
                            @if(!empty($m->team2->logo))
                                <img src="{{ $m->team2->logo }}" alt="{{ $m->team2->name }}" style="width: 32px; height: 32px; border-radius: 6px; object-fit: contain;" onerror="this.style.display='none';">
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 10px; font-size: 0.82rem;">
                        <div style="color: #4ade80; font-weight: 800;">
                            {{ $m->winning_title ?: ($m->custom_note ?: 'Match Scheduled') }}
                        </div>
                        <a href="{{ $m->url }}" style="color: #38bdf8; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            <span>View Full Scorecard</span> &rarr;
                        </a>
                    </div>

                </div>
            @empty
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
                    No fixtures scheduled yet for this series.
                </div>
            @endforelse
        </div>
    </div>

    <!-- =========================================================================
         TAB 4: POINTS TABLE
         ========================================================================= -->
    <div id="tab-points" class="series-tab-pane" style="{{ $curTab === 'points' ? '' : 'display: none;' }}">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>🏆</span> OFFICIAL POINTS TABLE &amp; STANDINGS
            </h3>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color); color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; font-weight: 800;">
                            <th style="padding: 12px 16px; text-align: left;">Team</th>
                            <th style="padding: 12px 10px;">P</th>
                            <th style="padding: 12px 10px;">W</th>
                            <th style="padding: 12px 10px;">L</th>
                            <th style="padding: 12px 10px;">NR</th>
                            <th style="padding: 12px 10px; color: #38bdf8;">PTS</th>
                            <th style="padding: 12px 14px;">NRR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointsTable as $index => $row)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.15s ease;" onmouseover="this.style.background='var(--bg-card-secondary)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 12px 16px; text-align: left; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                                    <span style="color: var(--text-dim); font-size: 0.78rem; width: 18px;">{{ $index + 1 }}</span>
                                    @if(!empty($row['team']->logo))
                                        <img src="{{ $row['team']->logo }}" alt="{{ $row['team']->name }}" style="width: 24px; height: 24px; border-radius: 4px; object-fit: contain;" onerror="this.style.display='none';">
                                    @endif
                                    <a href="javascript:void(0)" onclick="switchSeriesTab('tab-teams'); setTimeout(() => { const el = document.getElementById('series-team-{{ strtolower(preg_replace('/[^a-z0-9]/', '', $row['team']->name)) }}'); if(el) el.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 100);" style="color: inherit; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                                        {{ $row['team']->name }}
                                    </a>
                                </td>
                                <td style="padding: 12px 10px; font-weight: 700; color: var(--text-muted);">{{ $row['p'] }}</td>
                                <td style="padding: 12px 10px; font-weight: 700; color: #22c55e;">{{ $row['w'] }}</td>
                                <td style="padding: 12px 10px; font-weight: 700; color: #ef4444;">{{ $row['l'] }}</td>
                                <td style="padding: 12px 10px; font-weight: 700; color: var(--text-dim);">0</td>
                                <td style="padding: 12px 10px; font-weight: 900; color: #38bdf8; font-size: 1rem;">{{ $row['pts'] }}</td>
                                <td style="padding: 12px 14px; font-weight: 700; color: var(--text-dim);">{{ $row['nrr'] ?? '0.00' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 24px; color: var(--text-muted); font-style: italic;">
                                    No matches played yet to generate points table.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB 5: PLAYERS DIRECTORY (In-Series Roster with Realtime Team Filter & Full Profiles)
         ========================================================================= -->
    <div id="tab-players" class="series-tab-pane" style="{{ $curTab === 'players' ? '' : 'display: none;' }}">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                    <span>👤</span> ALL TOURNAMENT PLAYERS (<span id="playersCountDisplay">{{ count($players) }}</span>)
                </h3>
                <div id="activeTeamFilterBadge" style="display: none; align-items: center; gap: 8px; margin-top: 8px;">
                    <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 700;">Showing Squad:</span>
                    <span id="activeTeamFilterName" style="background: rgba(37, 99, 235, 0.2); color: #38bdf8; border: 1px solid rgba(37, 99, 235, 0.4); padding: 3px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 800;"></span>
                    <button type="button" onclick="clearSeriesPlayerTeamFilter()" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 0.75rem; font-weight: 800; cursor: pointer; padding: 3px 8px; border-radius: 6px;">✕ Show All Players</button>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Team Filter Dropdown for In-Series Players -->
                <select id="seriesPlayerTeamSelect" onchange="filterTournamentPlayers()" style="background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; outline: none; cursor: pointer;">
                    <option value="all">All Teams ({{ count($teams) }})</option>
                    @foreach($teams as $tm)
                        <option value="{{ strtolower($tm->name) }}">{{ $tm->name }}</option>
                    @endforeach
                </select>

                <div style="min-width: 220px;">
                    <input type="text" id="tournamentPlayerSearch" oninput="filterTournamentPlayers()" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 0.88rem; color: var(--text-main); outline: none;">
                </div>
            </div>
        </div>

        <div id="tournamentPlayersGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @forelse($players as $p)
                @php
                    $isFemale = (isset($p->is_female) && $p->is_female) 
                        || str_contains(strtolower(($p->name ?? '') . ' ' . ($p->team_name ?? '') . ' ' . ($p->role ?? '')), 'women');
                    $avatarSvg = $isFemale ? asset('images/default-player-girl.svg') : asset('images/default-player-boy.svg');
                    $playerImg = !empty($p->profile_image) ? $p->profile_image : $avatarSvg;
                    $searchIndex = strtolower(($p->name ?? '') . ' ' . ($p->team_name ?? '') . ' ' . ($p->role ?? '') . ' ' . ($p->batting_style ?? '') . ' ' . ($p->bowling_style ?? ''));
                @endphp
                <div class="tournament-player-card" data-team="{{ strtolower($p->team_name ?? '') }}" data-search="{{ $searchIndex }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, border-color 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
                    
                    <div>
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
                            <img src="{{ $playerImg }}" alt="{{ $p->name }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #38bdf8; background: var(--bg-card-secondary); flex-shrink: 0;" onerror="this.onerror=null; this.src='{{ $avatarSvg }}';">

                            <div style="min-width: 0; flex: 1;">
                                <a href="{{ $p->url }}" style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-main)'">
                                    {{ $p->name }}
                                </a>
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-size: 0.72rem; font-weight: 800; background: rgba(37, 99, 235, 0.15); color: #38bdf8; padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(37, 99, 235, 0.3);">
                                        {{ $p->role ?: 'Player' }}
                                    </span>
                                    <span style="font-size: 0.72rem; font-weight: 800; background: var(--bg-card-secondary); color: var(--text-muted); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border-color);">
                                        🛡️ {{ $p->team_name ?? 'Squad Member' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Batting & Bowling Info Snippet -->
                        <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 12px; font-size: 0.78rem; display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; justify-content: space-between; gap: 8px;">
                                <span style="color: var(--text-dim); font-weight: 700;">🏏 Batting:</span>
                                <span style="color: var(--text-main); font-weight: 800; text-align: right;">{{ $p->batting_style ?: 'Right-hand bat' }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; gap: 8px;">
                                <span style="color: var(--text-dim); font-weight: 700;">🎯 Bowling:</span>
                                <span style="color: var(--text-main); font-weight: 800; text-align: right;">{{ $p->bowling_style ?: 'Right-arm medium' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- View Profile Action Button -->
                    <a href="{{ $p->url }}" style="background: #2563eb; color: #ffffff; text-align: center; padding: 8px 14px; border-radius: 10px; font-size: 0.82rem; font-weight: 800; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                        <span>View Profile</span> &rarr;
                    </a>

                </div>
            @empty
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
                    No players registered in tournament teams yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- =========================================================================
         TAB 6: TEAMS & SQUADS (In-place Team Rosters, Full Squads & Instant Search)
         ========================================================================= -->
    <div id="tab-teams" class="series-tab-pane" style="{{ $curTab === 'teams' ? '' : 'display: none;' }}">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> PARTICIPATING TEAMS &amp; SQUADS ({{ count($teams) }})
            </h3>
            <div style="max-width: 280px; width: 100%;">
                <input type="text" id="seriesTeamSearch" oninput="filterSeriesTeamsTab()" placeholder="Search team or squad player..." style="width: 100%; padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 0.82rem; outline: none; box-sizing: border-box;">
            </div>
        </div>

        <div id="series-teams-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px; align-items: start;">
            @forelse($teams as $team)
                @php
                    $teamDomId = 'series-team-' . strtolower(preg_replace('/[^a-z0-9]/', '', $team->name));
                    $rosterDomId = 'roster-' . strtolower(preg_replace('/[^a-z0-9]/', '', $team->name));
                    $allPlayerNamesStr = strtolower($team->players->pluck('name')->implode(' '));
                    $teamSearchAttr = strtolower($team->name . ' ' . ($team->short_name ?? '') . ' ' . $allPlayerNamesStr);
                @endphp
                <div class="series-team-card" id="{{ $teamDomId }}" data-team-search="{{ $teamSearchAttr }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: transform 0.2s ease, border-color 0.2s ease;">
                    
                    <!-- Team Header (Stays right here on Teams tab!) -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 4px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="flex-shrink: 0;">
                                @if(!empty($team->logo))
                                    <img src="{{ $team->logo }}" alt="{{ $team->name }}" style="width: 52px; height: 52px; border-radius: 12px; object-fit: contain; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); padding: 4px;" onerror="this.onerror=null; this.src='https://flagcdn.com/w80/{{ strtolower(substr($team->short_name ?: $team->name, 0, 2)) }}.png';">
                                @else
                                    <div style="width: 52px; height: 52px; border-radius: 12px; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.15rem; color: #38bdf8;">
                                        {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $team->name), 0, 2)) ?: 'TM' }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0;">
                                    {{ $team->name }}
                                </h4>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 0.72rem; font-weight: 800; background: var(--bg-card-secondary); color: var(--text-muted); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border-color);">
                                        🏏 {{ count($team->players) }} Squad Players
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="toggleSeriesTeamFullSquad('{{ $rosterDomId }}', this)" style="background: rgba(37, 99, 235, 0.12); color: #38bdf8; border: 1px solid rgba(37, 99, 235, 0.3); padding: 6px 12px; border-radius: 8px; font-size: 0.76rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; transition: all 0.15s;">
                            <span>View Full Squad</span> ▾
                        </button>
                    </div>

                    <!-- Squad Players List with Smooth Scroll Container -->
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                            <span>Squad Roster ({{ count($team->players) }})</span>
                            <span style="font-size: 0.7rem; color: #38bdf8; font-weight: 700;">Click player for stats</span>
                        </div>
                        <div id="{{ $rosterDomId }}" class="series-team-roster-box" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap: 8px; max-height: 280px; overflow-y: auto; scrollbar-width: thin; padding-right: 4px; transition: max-height 0.3s ease;">
                            @forelse($team->players as $p)
                                @php
                                    $pAvatar = $p->default_avatar;
                                    $pImg = !empty($p->profile_image) ? $p->profile_image : $pAvatar;
                                @endphp
                                <a href="{{ $p->url }}" style="text-decoration: none; display: flex; align-items: center; gap: 8px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 8px; font-size: 0.78rem; font-weight: 700; color: var(--text-main); transition: all 0.15s ease;" onmouseover="this.style.borderColor='#38bdf8'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='translateY(0)';">
                                    <img src="{{ $pImg }}" alt="{{ $p->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1px solid #38bdf8; background: var(--bg-card-secondary); flex-shrink: 0;" onerror="this.onerror=null; this.src='{{ $pAvatar }}';">
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 800; font-size: 0.78rem; color: var(--text-main);">
                                            {{ $p->name }}
                                        </div>
                                        @if(!empty($p->role))
                                            <div style="font-size: 0.65rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $p->role }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic; grid-column: 1 / -1;">No players added yet.</span>
                            @endforelse
                        </div>
                    </div>

                </div>
            @empty
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
                    No teams added to this tournament yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- =========================================================================
         TAB 7: NEWS & ARTICLES (Related Content + Admin Guidance Box)
         ========================================================================= -->
    <div id="tab-news" class="series-tab-pane" style="{{ $curTab === 'news' ? '' : 'display: none;' }}">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>📰</span> SERIES NEWS, ARTICLES &amp; MATCH REPORTS
            </h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px; margin-bottom: 28px;">
            @php
                $allContentItems = collect();
                if(isset($seriesNews)) {
                    foreach($seriesNews as $n) { $n->content_type = 'NEWS'; $allContentItems->push($n); }
                }
                if(isset($seriesArticles)) {
                    foreach($seriesArticles as $a) { $a->content_type = 'ARTICLE'; $allContentItems->push($a); }
                }
            @endphp

            @forelse($allContentItems as $item)
                <a href="{{ $item->url }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; text-decoration: none; display: flex; flex-direction: column; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
                    @if(!empty($item->image_url))
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width: 100%; height: 160px; object-fit: cover;" onerror="this.style.display='none';">
                    @endif
                    <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 10px;">
                        <div>
                            <span style="font-size: 0.68rem; font-weight: 900; background: rgba(37, 99, 235, 0.15); color: #38bdf8; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 6px;">
                                {{ $item->content_type }}
                            </span>
                            <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1.35;">
                                {{ $item->title }}
                            </h4>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                            <span>{{ $item->created_at ? $item->created_at->diffForHumans() : 'Recent' }}</span>
                            <span>{{ $item->read_time ?? '3 MIN READ' }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
                    No news or articles published for this series yet.
                </div>
            @endforelse
        </div>

        <!-- Super Admin Publishing Info Box -->
        <div style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.25); border-radius: 14px; padding: 18px 22px; display: flex; align-items: flex-start; gap: 14px;">
            <div style="font-size: 1.5rem; line-height: 1;">💡</div>
            <div>
                <h4 style="font-size: 0.92rem; font-weight: 900; color: #38bdf8; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.03em;">
                    Super Admin Guidance: How to Add &amp; Tag News &amp; Articles to this Series
                </h4>
                <p style="color: var(--text-muted); font-size: 0.84rem; margin: 0; line-height: 1.5;">
                    To have articles or news automatically appear under this series tab, go to the <strong>Super Admin Dashboard &rarr; News / Articles</strong>. When adding or editing an article/news post, include the series name (e.g. <code>{{ $tournament->name }}</code>) or its keywords in the title or content. The system will automatically tag and link it to this series hub!
                </p>
            </div>
        </div>

    </div>

</div>

<!-- Interactive Scripts for Realtime Tab Switching, In-Place Stats & In-Series Team Filtering -->
<script>
const statInfoMap = {
    'most-runs': { name: 'Most Runs', icon: '🏏' },
    'most-fours': { name: 'Most Fours', icon: '⚡' },
    'most-sixes': { name: 'Most Sixes', icon: '💥' },
    'most-fifties': { name: 'Most Fifties', icon: '🎖️' },
    'most-centuries': { name: 'Most Centuries', icon: '💯' },
    'fours-innings': { name: 'Most Fours (Innings)', icon: '⚡' },
    'sixes-innings': { name: 'Most Sixes (Innings)', icon: '💥' },
    'best-strike-rates': { name: 'Best Strike Rates', icon: '🚀' },
    'highest-scores': { name: 'Highest Individual Scores', icon: '👑' },
    'top-wickets': { name: 'Top Wicket Takers', icon: '🎯' },
    'four-wickets': { name: 'Four Wickets', icon: '🔥' },
    'five-wickets': { name: 'Five Wickets', icon: '🌟' },
    'maidens': { name: 'Maidens', icon: '🛡️' },
    'best-bowling-averages': { name: 'Best Bowling Averages', icon: '📊' },
    'best-bowling-avg': { name: 'Best Bowling Averages', icon: '📊' },
    'best-figures': { name: 'Best Bowling Figures', icon: '💎' },
    'best-economy': { name: 'Best Economy Rates', icon: '🛡️' },
    'best-economies': { name: 'Best Economy Rates', icon: '🛡️' },
    'team-runs': { name: 'Total Runs', icon: '🛡️' },
    'team-wickets': { name: 'Total Wickets', icon: '🎯' },
    'highest-team-totals': { name: 'Highest Team Totals', icon: '🏆' },
    'team-highest-totals': { name: 'Highest Team Totals', icon: '🏆' },
    'team-fifties': { name: 'Most Fifties (Team)', icon: '🎖️' },
    'team-centuries': { name: 'Most Centuries (Team)', icon: '💯' }
};

const seriesBaseUrl = "{{ url('/series/' . $tournament->slug . '-' . $tournament->id) }}";
const seriesTabSlugMap = {
    'tab-home': '',
    'tab-stats': 'stats',
    'tab-fixtures': 'fixtures',
    'tab-points': 'points-table',
    'tab-players': 'players',
    'tab-teams': 'teams',
    'tab-news': 'news'
};

function switchSeriesTab(tabId, updateUrl = true) {
    document.querySelectorAll('.series-tab-pane').forEach(p => p.style.display = 'none');
    const target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
    }

    document.querySelectorAll('.series-tab-btn').forEach(btn => {
        btn.style.background = 'var(--bg-card)';
        btn.style.color = 'var(--text-muted)';
        btn.style.borderColor = 'var(--border-color)';
        btn.classList.remove('active-tab-btn');
    });

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.style.background = '#2563eb';
        activeBtn.style.color = '#ffffff';
        activeBtn.style.borderColor = '#2563eb';
        activeBtn.classList.add('active-tab-btn');
    }

    // Update URL path cleanly without #hash (e.g. /series/{slug}-{id}/teams)
    if (updateUrl && history.pushState) {
        const subSlug = seriesTabSlugMap[tabId];
        const newUrl = subSlug ? (seriesBaseUrl + '/' + subSlug) : seriesBaseUrl;
        if (window.location.href !== newUrl) {
            history.pushState({ tabId: tabId }, '', newUrl);
        }
    }
}

// Toggle full squad view in Teams tab
function toggleSeriesTeamFullSquad(rosterId, btn) {
    const roster = document.getElementById(rosterId);
    if (!roster) return;
    if (roster.style.maxHeight === 'none') {
        roster.style.maxHeight = '280px';
        if (btn) btn.innerHTML = '<span>View Full Squad</span> ▾';
    } else {
        roster.style.maxHeight = 'none';
        if (btn) btn.innerHTML = '<span>Collapse Squad</span> ▴';
    }
}

// Filter teams in Teams tab
function filterSeriesTeamsTab() {
    const q = (document.getElementById('seriesTeamSearch').value || '').toLowerCase().trim();
    document.querySelectorAll('.series-team-card').forEach(card => {
        const text = (card.getAttribute('data-team-search') || '').toLowerCase();
        if (!q || text.includes(q)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// When user clicks the top "STATS" tab button, always reset to the 3-column stats hub overview!
function openStatsTabHub() {
    switchSeriesTab('tab-stats');
    closeStatDetail();
}

function openStatDetail(rawStatKey) {
    let statKey = rawStatKey;
    if (statKey === 'best-bowling-avg') statKey = 'best-bowling-averages';
    if (statKey === 'best-economies') statKey = 'best-economy';
    if (statKey === 'team-highest-totals') statKey = 'highest-team-totals';

    const info = statInfoMap[statKey] || { name: statKey.replace('-', ' ').toUpperCase(), icon: '📊' };

    // Hide category overview, show detail container
    const overview = document.getElementById('stats-category-overview');
    const container = document.getElementById('stats-detail-container');
    if (overview) overview.style.display = 'none';
    if (container) container.style.display = 'block';

    // Hide all stat panes and show target
    document.querySelectorAll('.stat-table-pane').forEach(el => el.style.display = 'none');
    const targetPane = document.getElementById('stat-pane-' + statKey);
    if (targetPane) {
        targetPane.style.display = 'block';
    }

    // Update title & icon
    const elName = document.getElementById('stat-detail-name');
    const elIcon = document.getElementById('stat-detail-icon');
    if (elName) elName.textContent = info.name;
    if (elIcon) elIcon.textContent = info.icon;

    // Update dropdown switcher value to match
    const switcher = document.getElementById('statCategorySwitcher');
    if (switcher) switcher.value = statKey;

    // Update Quick Stat Switcher Pills active state
    document.querySelectorAll('.stat-pill-btn').forEach(pill => {
        if (pill.getAttribute('data-stat') === statKey) {
            pill.classList.add('active-stat-pill');
        } else {
            pill.classList.remove('active-stat-pill');
        }
    });

    // Reset team filter
    const teamSelect = document.getElementById('statTeamFilterSelect');
    if (teamSelect) teamSelect.value = 'all';
    filterStatLeaderboardByTeam('all');

    // Update URL hash without page jump
    if (history.replaceState) {
        history.replaceState(null, null, '#tab-stats&stat=' + statKey);
    }
}

function closeStatDetail() {
    const overview = document.getElementById('stats-category-overview');
    const container = document.getElementById('stats-detail-container');
    if (overview) overview.style.display = 'block';
    if (container) container.style.display = 'none';

    if (history.replaceState) {
        history.replaceState(null, null, '#tab-stats');
    }
}

function filterStatLeaderboardByTeam(teamId) {
    const rows = document.querySelectorAll('#stats-detail-container tbody tr.stat-row');
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

// Filter Players in Players Tab (by Search & Team)
function filterTournamentPlayers() {
    const query = (document.getElementById('tournamentPlayerSearch').value || '').toLowerCase().trim();
    const teamSelect = document.getElementById('seriesPlayerTeamSelect');
    const selectedTeam = teamSelect ? (teamSelect.value || '').toLowerCase().trim() : 'all';
    
    const badge = document.getElementById('activeTeamFilterBadge');
    const badgeName = document.getElementById('activeTeamFilterName');
    
    if (selectedTeam && selectedTeam !== 'all') {
        if (badge) badge.style.display = 'inline-flex';
        if (badgeName) {
            const opt = teamSelect.options[teamSelect.selectedIndex];
            badgeName.textContent = opt ? opt.text : selectedTeam;
        }
    } else {
        if (badge) badge.style.display = 'none';
    }

    const cards = document.querySelectorAll('.tournament-player-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const searchData = (card.getAttribute('data-search') || '').toLowerCase();
        const cardTeam = (card.getAttribute('data-team') || '').toLowerCase();
        
        const matchesQuery = !query || searchData.includes(query);
        const matchesTeam = (selectedTeam === 'all') || cardTeam.includes(selectedTeam) || searchData.includes(selectedTeam);
        
        if (matchesQuery && matchesTeam) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const countDisplay = document.getElementById('playersCountDisplay');
    if (countDisplay) {
        countDisplay.textContent = visibleCount;
    }
}

function clearSeriesPlayerTeamFilter() {
    const teamSelect = document.getElementById('seriesPlayerTeamSelect');
    if (teamSelect) teamSelect.value = 'all';
    const searchInput = document.getElementById('tournamentPlayerSearch');
    if (searchInput) searchInput.value = '';
    filterTournamentPlayers();
}

function filterPlayersByTeamInSeries(teamKey, teamName) {
    switchSeriesTab('tab-players');
    const select = document.getElementById('seriesPlayerTeamSelect');
    if (select) {
        select.value = teamKey;
    }
    filterTournamentPlayers();
}

// Handle browser back and forward navigation
window.addEventListener('popstate', function(e) {
    const path = window.location.pathname.replace(/\/+$/, '');
    const segments = path.split('/');
    const lastSeg = segments[segments.length - 1];
    let target = 'tab-home';
    for (const [tId, slug] of Object.entries(seriesTabSlugMap)) {
        if (slug && (lastSeg === slug || (slug === 'points-table' && (lastSeg === 'points' || lastSeg === 'points-table')))) {
            target = tId;
            break;
        }
    }
    switchSeriesTab(target, false);
});

// Auto-switch tab on page load based on backend activeTab, URL pathname, or hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    const activeStatFromBackend = @json($activeStat ?? null);
    const activeTabFromBackend = @json($activeTab ?? null);

    const path = window.location.pathname.replace(/\/+$/, '');
    const segments = path.split('/');
    const lastSeg = segments[segments.length - 1];

    if (activeStatFromBackend) {
        switchSeriesTab('tab-stats', false);
        openStatDetail(activeStatFromBackend);
    } else if (activeTabFromBackend && activeTabFromBackend !== 'home') {
        const targetId = activeTabFromBackend.startsWith('tab-') ? activeTabFromBackend : ('tab-' + (activeTabFromBackend === 'points-table' ? 'points' : activeTabFromBackend));
        switchSeriesTab(targetId, false);
    } else {
        let matched = null;
        for (const [tId, slug] of Object.entries(seriesTabSlugMap)) {
            if (slug && (lastSeg === slug || (slug === 'points-table' && (lastSeg === 'points' || lastSeg === 'points-table')))) {
                matched = tId;
                break;
            }
        }
        if (matched) {
            switchSeriesTab(matched, false);
        } else if (hash) {
            if (hash.includes('&stat=')) {
                const parts = hash.split('&stat=');
                switchSeriesTab(parts[0], false);
                openStatDetail(parts[1]);
            } else {
                let targetId = hash.startsWith('tab-') ? hash : ('tab-' + hash);
                const pane = document.getElementById(targetId);
                if (pane) {
                    switchSeriesTab(targetId, false);
                }
            }
        }
    }
});
</script>
@endsection
