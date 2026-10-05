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

    <!-- 7 ORDERED TABS NAVIGATION (1. Home, 2. Stats, 3. Fixtures, 4. Points Table, 5. Players, 6. Squads, 7. News & Articles) -->
    <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;" class="no-scrollbar">
            
            <!-- TAB 1: HOME -->
            <button type="button" onclick="switchSeriesTab('tab-home')" id="btn-tab-home" class="series-tab-btn active-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid #2563eb; background: #2563eb; color: #ffffff; transition: all 0.2s;">
                <span>🏠</span> HOME
            </button>

            <!-- TAB 2: STATS -->
            <button type="button" onclick="switchSeriesTab('tab-stats')" id="btn-tab-stats" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>📊</span> STATS
            </button>

            <!-- TAB 3: FIXTURES -->
            <button type="button" onclick="switchSeriesTab('tab-fixtures')" id="btn-tab-fixtures" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>📅</span> FIXTURES <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ $tournament->matches->count() }}</span>
            </button>

            <!-- TAB 4: POINTS TABLE -->
            <button type="button" onclick="switchSeriesTab('tab-points')" id="btn-tab-points" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>🏆</span> POINTS TABLE
            </button>

            <!-- TAB 5: PLAYERS -->
            <button type="button" onclick="switchSeriesTab('tab-players')" id="btn-tab-players" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>👤</span> PLAYERS <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($players) }}</span>
            </button>

            <!-- TAB 6: SQUADS -->
            <button type="button" onclick="switchSeriesTab('tab-teams')" id="btn-tab-teams" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>👥</span> SQUADS <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($teams) }}</span>
            </button>

            <!-- TAB 7: NEWS & ARTICLES -->
            <button type="button" onclick="switchSeriesTab('tab-news')" id="btn-tab-news" class="series-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>📰</span> NEWS &amp; ARTICLES <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ (isset($seriesNews) ? $seriesNews->count() : 0) + (isset($seriesArticles) ? $seriesArticles->count() : 0) }}</span>
            </button>

        </div>
    </div>

    <!-- =========================================================================
         TAB 1: HOME (Series Overview, Quick Schedule, Standings & Leaders Snapshot)
         ========================================================================= -->
    <!-- =========================================================================
         TAB 1: HOME (Comprehensive Series Overview, Key Facts, Rules & Tournament Hub)
         ========================================================================= -->
    <div id="tab-home" class="series-tab-pane">
        
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
                            <button type="button" onclick="switchSeriesTab('tab-stats')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                📊 Stats Leaderboards
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-fixtures')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                📅 Match Schedule
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-points')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                🏆 Points Standings
                            </button>
                            <button type="button" onclick="switchSeriesTab('tab-teams')" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 0.8rem; padding: 8px; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.15s;" onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                                👥 Squad Rosters
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
         TAB 2: STATS HUB & LEADERBOARD (Batting, Bowling, Team Stats)
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
    </style>

    <div id="tab-stats" class="series-tab-pane" style="display: none;">
        
        <!-- 3-Column Stats Category Hub -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
            
            <!-- COLUMN 1: BATTING STATS -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase;">
                    Batting Stats
                </div>
                <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'most-runs']) }}" class="stat-hub-card-link">
                        <span>Most Runs</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'most-fours']) }}" class="stat-hub-card-link">
                        <span>Most Fours</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'most-sixes']) }}" class="stat-hub-card-link">
                        <span>Most Sixes</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'most-fifties']) }}" class="stat-hub-card-link">
                        <span>Most Fifties</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'most-centuries']) }}" class="stat-hub-card-link">
                        <span>Most Centuries</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'fours-innings']) }}" class="stat-hub-card-link">
                        <span>Most Fours (Innings)</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'sixes-innings']) }}" class="stat-hub-card-link">
                        <span>Most Sixes (Innings)</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'best-strike-rates']) }}" class="stat-hub-card-link">
                        <span>Best Strike Rates</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'highest-scores']) }}" class="stat-hub-card-link">
                        <span>Highest Individual Scores</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                </div>
            </div>

            <!-- COLUMN 2: BOWLING STATS -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase;">
                    Bowling Stats
                </div>
                <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'top-wickets']) }}" class="stat-hub-card-link">
                        <span>Top Wicket Takers</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'four-wickets']) }}" class="stat-hub-card-link">
                        <span>Four Wickets</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'five-wickets']) }}" class="stat-hub-card-link">
                        <span>Five Wickets</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'maidens']) }}" class="stat-hub-card-link">
                        <span>Maidens</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'best-figures']) }}" class="stat-hub-card-link">
                        <span>Best Bowling Figures</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'best-economy']) }}" class="stat-hub-card-link">
                        <span>Best Economy</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'best-bowling-averages']) }}" class="stat-hub-card-link">
                        <span>Best Bowling Averages</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                </div>
            </div>

            <!-- COLUMN 3: TEAM STATS -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="background: #2563eb; color: #ffffff; padding: 12px 18px; font-weight: 900; font-size: 0.95rem; text-align: center; letter-spacing: 0.04em; text-transform: uppercase;">
                    Team Stats
                </div>
                <div style="padding: 14px; display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'team-runs']) }}" class="stat-hub-card-link">
                        <span>Total Runs</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'team-wickets']) }}" class="stat-hub-card-link">
                        <span>Total Wickets</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'team-highest-totals']) }}" class="stat-hub-card-link">
                        <span>Highest Team Totals</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'team-fifties']) }}" class="stat-hub-card-link">
                        <span>Most Fifties</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('series.stat.slug', ['slug' => $tournament->slug ?? \Illuminate\Support\Str::slug($tournament->name), 'id' => $tournament->id, 'stat' => 'team-centuries']) }}" class="stat-hub-card-link">
                        <span>Most Centuries</span>
                        <span class="stat-hub-arrow">&rsaquo;</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- =========================================================================
         TAB 3: FIXTURES & SCHEDULE
         ========================================================================= -->
    <div id="tab-fixtures" class="series-tab-pane" style="display: none;">
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
    <div id="tab-points" class="series-tab-pane" style="display: none;">
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
                                    <span>{{ $row['team']->name }}</span>
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
         TAB 5: PLAYERS DIRECTORY (With Default Boy / Girl Avatar Fallbacks)
         ========================================================================= -->
    <div id="tab-players" class="series-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> ALL TOURNAMENT PLAYERS ({{ count($players) }})
            </h3>
            
            <div style="display: flex; align-items: center; gap: 10px; min-width: 260px;">
                <input type="text" id="tournamentPlayerSearch" oninput="filterTournamentPlayers()" placeholder="Search players by name or team..." style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 0.88rem; color: var(--text-main); outline: none;">
            </div>
        </div>

        <div id="tournamentPlayersGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @forelse($players as $p)
                @php
                    $isFemale = (isset($p->is_female) && $p->is_female) 
                        || str_contains(strtolower(($p->name ?? '') . ' ' . ($p->team_name ?? '') . ' ' . ($p->role ?? '')), 'women');
                    $avatarSvg = $isFemale ? asset('images/default-player-girl.svg') : asset('images/default-player-boy.svg');
                    $playerImg = !empty($p->profile_image) ? $p->profile_image : $avatarSvg;
                @endphp
                <div class="tournament-player-card" data-search="{{ strtolower($p->name . ' ' . ($p->team_name ?? '') . ' ' . ($p->role ?? '')) }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 14px; transition: transform 0.2s ease, border-color 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
                    
                    <img src="{{ $playerImg }}" alt="{{ $p->name }}" style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 1.5px solid #38bdf8; flex-shrink: 0;" onerror="this.onerror=null; this.src='{{ $avatarSvg }}';">

                    <div style="min-width: 0; flex: 1;">
                        <a href="{{ $p->url }}" style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                            {{ $p->name }}
                        </a>
                        <div style="font-size: 0.76rem; color: var(--text-muted); font-weight: 700; margin-top: 2px;">
                            {{ $p->team_name ?? 'Squad Member' }}
                        </div>
                        @if(!empty($p->role))
                            <span style="font-size: 0.68rem; font-weight: 800; background: var(--bg-card-secondary); color: var(--text-dim); padding: 1px 6px; border-radius: 4px; border: 1px solid var(--border-color); display: inline-block; margin-top: 4px;">
                                {{ $p->role }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
                    No players registered in tournament teams yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- =========================================================================
         TAB 6: SQUADS & TEAMS (With Official Logos & Country Flag Fallbacks)
         ========================================================================= -->
    <div id="tab-teams" class="series-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> PARTICIPATING TEAMS &amp; SQUADS ({{ count($teams) }})
            </h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; align-items: start;">
            @forelse($teams as $team)
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: flex-start; gap: 14px;">
                    
                    <!-- Team Header with Official Logo / Country Flag -->
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
                        @if(!empty($team->logo))
                            <img src="{{ $team->logo }}" alt="{{ $team->name }}" style="width: 52px; height: 52px; border-radius: 12px; object-fit: contain; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); padding: 4px;" onerror="this.onerror=null; this.src='https://flagcdn.com/w80/{{ strtolower(substr($team->short_name ?: $team->name, 0, 2)) }}.png';">
                        @else
                            <div style="width: 52px; height: 52px; border-radius: 12px; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.15rem; color: #38bdf8;">
                                {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $team->name), 0, 2)) ?: 'TM' }}
                            </div>
                        @endif
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

                    <!-- Squad Players List Preview -->
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                            <span>Squad Roster ({{ count($team->players) }})</span>
                            <span style="font-size: 0.7rem; color: #38bdf8; font-weight: 700;">Click player for profile</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap: 8px;">
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
    <div id="tab-news" class="series-tab-pane" style="display: none;">
        
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

<!-- Interactive Scripts for Realtime Tab Switching & Search -->
<script>
function switchSeriesTab(tabId) {
    document.querySelectorAll('.series-tab-pane').forEach(p => p.style.display = 'none');
    const target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
    }

    document.querySelectorAll('.series-tab-btn').forEach(btn => {
        btn.style.background = 'var(--bg-card)';
        btn.style.color = 'var(--text-muted)';
        btn.style.borderColor = 'var(--border-color)';
    });

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.style.background = '#2563eb';
        activeBtn.style.color = '#ffffff';
        activeBtn.style.borderColor = '#2563eb';
    }

    // Update URL hash without jumping
    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabId);
    }
}

// Search Players in Players tab
function filterTournamentPlayers() {
    const query = document.getElementById('tournamentPlayerSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.tournament-player-card');
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search') || '';
        if (!query || searchData.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// Auto-switch tab if URL has hash (e.g. #tab-fixtures or #stats)
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        let targetId = hash;
        if (!targetId.startsWith('tab-')) {
            targetId = 'tab-' + targetId;
        }
        const pane = document.getElementById(targetId);
        if (pane) {
            switchSeriesTab(targetId);
        }
    }
});
</script>
@endsection
