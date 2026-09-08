@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 40px 24px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Header & Search Box -->
    <div style="text-align: center; margin-bottom: 32px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: #38bdf8; text-transform: uppercase; display: block; margin-bottom: 6px;">
            CRICKET SEARCH ENGINE
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 16px 0;">
            Search CricketKaScore
        </h1>
        
        <!-- Search Input Form -->
        <form action="{{ route('search') }}" method="GET" style="max-width: 680px; margin: 0 auto; position: relative; display: flex; align-items: center; box-shadow: 0 8px 30px rgba(0,0,0,0.15); border-radius: 14px;">
            <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
            <div style="position: absolute; left: 18px; color: var(--text-muted); font-size: 1.1rem; pointer-events: none;">
                🔍
            </div>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search players, teams, matches, series, news, venues, glossary..." autofocus
                   style="width: 100%; padding: 16px 120px 16px 50px; background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 14px; font-size: 1.05rem; font-weight: 600; color: var(--text-main); outline: none; transition: border-color 0.2s;"
                   onfocus="this.style.borderColor='#38bdf8';" onblur="this.style.borderColor='var(--border-color)';">
            
            <button type="submit" style="position: absolute; right: 8px; padding: 10px 20px; background: #0284c7; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.15s;"
                    onmouseover="this.style.background='#0369a1';" onmouseout="this.style.background='#0284c7';">
                Search
            </button>
        </form>
    </div>

    <!-- Category Filter Tabs / Pills -->
    @if(!empty($q))
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 30px; scrollbar-width: thin;">
        @php
            $tabs = [
                'all' => ['label' => 'All Results', 'count' => $totalCount],
                'players' => ['label' => 'Players', 'count' => $players->count()],
                'teams' => ['label' => 'Teams', 'count' => $teams->count()],
                'matches' => ['label' => 'Matches', 'count' => $matches->count()],
                'series' => ['label' => 'Series', 'count' => $tournaments->count()],
                'articles' => ['label' => 'Articles', 'count' => $articles->count()],
                'news' => ['label' => 'News', 'count' => $news->count()],
                'predictions' => ['label' => 'Predictions', 'count' => $predictions->count() + $fantasyTips->count()],
                'venues' => ['label' => 'Venues', 'count' => $venues->count()],
                'stories' => ['label' => 'Web Stories', 'count' => $webStories->count()],
                'glossary' => ['label' => 'Glossary', 'count' => $glossary->count()],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tabInfo)
            <a href="{{ route('search', ['q' => $q, 'type' => $tabKey]) }}" 
               style="text-decoration: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; {{ $type === $tabKey || ($tabKey === 'all' && empty($type)) ? 'background: #0284c7; color: white;' : 'background: var(--bg-card); color: var(--text-muted); border: 1px solid var(--border-color);' }}">
                <span>{{ $tabInfo['label'] }}</span>
                <span style="font-size: 0.75rem; padding: 2px 6px; border-radius: 10px; {{ $type === $tabKey || ($tabKey === 'all' && empty($type)) ? 'background: rgba(255,255,255,0.25); color: white;' : 'background: var(--bg-card-secondary); color: var(--text-dim);' }}">
                    {{ $tabInfo['count'] }}
                </span>
            </a>
        @endforeach
    </div>
    @endif

    <!-- Results Overview Bar -->
    @if(!empty($q))
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 18px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.9rem; color: var(--text-muted);">
                Found <strong style="color: var(--text-main);">{{ $totalCount }}</strong> results for <span style="color: #38bdf8; font-weight: 800;">"{{ $q }}"</span>
            </div>
            <a href="{{ route('search') }}" style="text-decoration: none; font-size: 0.82rem; font-weight: 700; color: #f87171;">
                ✕ Clear Search
            </a>
        </div>
    @endif

    <!-- Search Content Display -->
    @if(empty($q))
        <!-- Default State: Popular Searches & Suggestions -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 40px 24px; text-align: center; max-width: 800px; margin: 40px auto;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🏏</div>
            <h3 style="font-size: 1.3rem; font-weight: 900; color: var(--text-main); margin-bottom: 8px;">Explore Everything in Cricket</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 500px; margin: 0 auto 24px;">Search through all international & local players, team squads, live match scorecards, series, news, venues, and dictionary terms.</p>
            
            <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 12px;">Popular Searches</div>
            <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 8px;">
                @foreach(['India', 'Virat Kohli', 'Rohit Sharma', 'Mumbai Indians', 'Chennai Super Kings', 'T20', 'Chinnaswamy', 'Yorker', 'Century'] as $term)
                    <a href="{{ route('search', ['q' => $term]) }}" style="text-decoration: none; padding: 6px 14px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 20px; color: var(--text-main); font-size: 0.82rem; font-weight: 600; transition: all 0.15s;"
                       onmouseover="this.style.borderColor='#38bdf8'; this.style.color='#38bdf8';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-main)';">
                        🔍 {{ $term }}
                    </a>
                @endforeach
            </div>
        </div>
    @elseif($totalCount === 0)
        <!-- Empty Results -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 60px 24px; text-align: center; max-width: 650px; margin: 40px auto;">
            <div style="font-size: 3rem; margin-bottom: 14px;">🔍</div>
            <h3 style="font-size: 1.3rem; font-weight: 900; color: var(--text-main); margin-bottom: 8px;">No Results Found</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">We couldn't find any players, teams, matches, or articles matching <strong>"{{ $q }}"</strong>.</p>
            <div style="display: flex; justify-content: center; gap: 10px;">
                <a href="{{ route('search') }}" style="text-decoration: none; padding: 8px 18px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); font-weight: 700; font-size: 0.85rem;">Try Another Term</a>
                <a href="{{ route('home') }}" style="text-decoration: none; padding: 8px 18px; background: #0284c7; border-radius: 8px; color: white; font-weight: 700; font-size: 0.85rem;">Back to Home</a>
            </div>
        </div>
    @else

        <!-- 1. PLAYERS SECTION -->
        @if($players->count() > 0 && ($type === 'all' || $type === 'players'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🏏</span> Players ({{ $players->count() }})
                </h2>
                @if($type === 'all' && $players->count() > 4)
                    <a href="{{ route('search', ['q' => $q, 'type' => 'players']) }}" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">View All Players &rarr;</a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $players->take(4) : $players) as $player)
                    <a href="{{ route('player.profile', $player->id) }}" style="text-decoration: none; display: flex; align-items: center; gap: 14px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        @if($player->profile_image)
                            <img src="{{ $player->profile_image }}" alt="{{ $player->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #38bdf8;">
                        @else
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; color: #38bdf8;">
                                {{ strtoupper(substr($player->name, 0, 2)) }}
                            </div>
                        @endif
                        <div style="overflow: hidden;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $player->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">{{ $player->role ?: 'Player' }}</div>
                            <div style="font-size: 0.72rem; color: #38bdf8; font-weight: 700;">{{ $player->team ? $player->team->name : ($player->country ?: 'International') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 2. TEAMS SECTION -->
        @if($teams->count() > 0 && ($type === 'all' || $type === 'teams'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🛡️</span> Teams ({{ $teams->count() }})
                </h2>
                @if($type === 'all' && $teams->count() > 4)
                    <a href="{{ route('search', ['q' => $q, 'type' => 'teams']) }}" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">View All Teams &rarr;</a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $teams->take(4) : $teams) as $team)
                    <a href="{{ route('players', ['team' => $team->id]) }}" style="text-decoration: none; display: flex; align-items: center; gap: 14px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        @if($team->logo_url || $team->logo)
                            <img src="{{ $team->logo_url ?: $team->logo }}" alt="{{ $team->name }}" style="width: 46px; height: 46px; border-radius: 10px; object-fit: contain; background: white; padding: 2px;">
                        @else
                            <div style="width: 46px; height: 46px; border-radius: 10px; background: {{ $team->color_code ?: '#2563eb' }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem;">
                                {{ $team->short_name ?: strtoupper(substr($team->name, 0, 3)) }}
                            </div>
                        @endif
                        <div style="overflow: hidden;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $team->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">Code: {{ $team->short_name ?: '-' }} &bull; {{ ucfirst($team->team_type ?? 'Team') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 3. MATCHES SECTION -->
        @if($matches->count() > 0 && ($type === 'all' || $type === 'matches'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>⚡</span> Matches ({{ $matches->count() }})
                </h2>
                @if($type === 'all' && $matches->count() > 3)
                    <a href="{{ route('search', ['q' => $q, 'type' => 'matches']) }}" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">View All Matches &rarr;</a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $matches->take(3) : $matches) as $match)
                    <a href="{{ route('matches.detail', $match->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; {{ $match->status === 'live' ? 'background: #ef4444; color: white;' : ($match->status === 'completed' ? 'background: #10b981; color: white;' : 'background: #f59e0b; color: white;') }}">
                                {{ $match->status === 'live' ? '🔴 LIVE' : ($match->status === 'completed' ? '🏁 COMPLETED' : '📅 UPCOMING') }}
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">{{ $match->match_type ?? 'T20' }}</span>
                        </div>
                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-main); margin-bottom: 6px;">
                            {{ $match->team1?->name ?? 'Team 1' }} vs {{ $match->team2?->name ?? 'Team 2' }}
                        </div>
                        <div style="font-size: 0.82rem; color: #38bdf8; font-weight: 700; margin-bottom: 6px;">
                            @if($match->team1_score !== null)
                                {{ $match->team1_score }}/{{ $match->team1_wickets }}
                            @endif
                            @if($match->team2_score !== null)
                                &nbsp;vs&nbsp; {{ $match->team2_score }}/{{ $match->team2_wickets }}
                            @endif
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                            {{ $match->result_text ?: ($match->custom_note ?: 'Click for full ball-by-ball scorecard') }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 4. SERIES / TOURNAMENTS SECTION -->
        @if($tournaments->count() > 0 && ($type === 'all' || $type === 'series' || $type === 'tournaments'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🏆</span> Tournaments &amp; Series ({{ $tournaments->count() }})
                </h2>
                @if($type === 'all' && $tournaments->count() > 3)
                    <a href="{{ route('search', ['q' => $q, 'type' => 'series']) }}" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">View All Series &rarr;</a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $tournaments->take(3) : $tournaments) as $tour)
                    <a href="{{ route('tournament.public', $tour->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 8px;">
                                🏆 {{ $tour->category ?: 'SERIES' }}
                            </span>
                            <div style="font-weight: 800; font-size: 1rem; color: var(--text-main); margin-bottom: 4px;">{{ $tour->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">
                                {{ $tour->year ?? '2026' }} &bull; {{ $tour->hosting_country ?: ($tour->city ?: 'International') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 5. ARTICLES & NEWS SECTION -->
        @if(($articles->count() > 0 || $news->count() > 0) && ($type === 'all' || $type === 'articles' || $type === 'news'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>📰</span> Articles &amp; News ({{ $articles->count() + $news->count() }})
                </h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach($articles->take($type === 'all' ? 2 : 10) as $art)
                    <a href="{{ route('article.show', $art->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        @if($art->image_url)
                            <img src="{{ $art->image_url }}" alt="{{ $art->title }}" style="width: 100%; height: 140px; object-fit: cover;">
                        @endif
                        <div style="padding: 14px;">
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #a855f7; display: block; margin-bottom: 4px;">
                                📝 ARTICLE &bull; {{ $art->published_date ?: 'Sep 03' }}
                            </span>
                            <div style="font-weight: 800; font-size: 0.92rem; color: var(--text-main); line-height: 1.35; margin-bottom: 6px;">{{ $art->title }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($art->summary, 80) }}</div>
                        </div>
                    </a>
                @endforeach

                @foreach($news->take($type === 'all' ? 2 : 10) as $nw)
                    <a href="{{ route('news.show', $nw->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        @if($nw->image_url)
                            <img src="{{ $nw->image_url }}" alt="{{ $nw->title }}" style="width: 100%; height: 140px; object-fit: cover;">
                        @endif
                        <div style="padding: 14px;">
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #38bdf8; display: block; margin-bottom: 4px;">
                                📰 NEWS &bull; {{ $nw->published_date ?: 'Sep 03' }}
                            </span>
                            <div style="font-weight: 800; font-size: 0.92rem; color: var(--text-main); line-height: 1.35; margin-bottom: 6px;">{{ $nw->title }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($nw->summary, 80) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 6. PREDICTIONS & FANTASY TIPS -->
        @if(($predictions->count() > 0 || $fantasyTips->count() > 0) && ($type === 'all' || $type === 'predictions'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🎯</span> Match Predictions &amp; Fantasy Tips ({{ $predictions->count() + $fantasyTips->count() }})
                </h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach($predictions->take($type === 'all' ? 2 : 10) as $pred)
                    <a href="{{ route('news.show', $pred->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        <div>
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #f59e0b; display: block; margin-bottom: 4px;">
                                🎯 {{ $pred->tag ?: 'MATCH PREVIEW' }}
                            </span>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main); margin-bottom: 6px;">{{ $pred->title }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($pred->summary, 85) }}</div>
                        </div>
                    </a>
                @endforeach

                @foreach($fantasyTips->take($type === 'all' ? 2 : 10) as $tip)
                    <a href="{{ route('news.show', $tip->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        <div>
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #10b981; display: block; margin-bottom: 4px;">
                                ⚡ FANTASY TIP
                            </span>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main); margin-bottom: 6px;">{{ $tip->title }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($tip->summary, 85) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 7. VENUES SECTION -->
        @if($venues->count() > 0 && ($type === 'all' || $type === 'venues'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🏟️</span> Stadiums &amp; Venues ({{ $venues->count() }})
                </h2>
                @if($type === 'all' && $venues->count() > 3)
                    <a href="{{ route('search', ['q' => $q, 'type' => 'venues']) }}" style="font-size: 0.82rem; font-weight: 800; color: #38bdf8; text-decoration: none;">View All Venues &rarr;</a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $venues->take(3) : $venues) as $venue)
                    <a href="{{ route('venues.show', $venue->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        @if($venue->image_url)
                            <img src="{{ $venue->image_url }}" alt="{{ $venue->name }}" style="width: 100%; height: 120px; object-fit: cover;">
                        @endif
                        <div style="padding: 14px;">
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #38bdf8; display: block; margin-bottom: 4px;">
                                🏟️ VENUE
                            </span>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main); margin-bottom: 4px;">{{ $venue->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">
                                {{ $venue->city ? $venue->city . ', ' : '' }}{{ $venue->country }}
                                @if($venue->capacity)
                                    &bull; Cap: {{ is_numeric($venue->capacity) ? number_format((float)$venue->capacity) : $venue->capacity }}
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 8. WEB STORIES SECTION -->
        @if($webStories->count() > 0 && ($type === 'all' || $type === 'stories'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>📱</span> Web Stories ({{ $webStories->count() }})
                </h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                @foreach(($type === 'all' ? $webStories->take(4) : $webStories) as $story)
                    <a href="{{ route('webstories.show', $story->id) }}" style="text-decoration: none; position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 9/16; max-height: 280px; display: block; background: #000; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.15s;"
                       onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='scale(1)';">
                        @if($story->image_url)
                            <img src="{{ $story->image_url }}" alt="{{ $story->title }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85;">
                        @endif
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 60%); padding: 12px; display: flex; flex-direction: column; justify-content: flex-end;">
                            <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: #e11d48; color: white; padding: 2px 6px; border-radius: 4px; align-self: flex-start; margin-bottom: 4px;">
                                📱 STORY
                            </span>
                            <div style="color: white; font-weight: 800; font-size: 0.85rem; line-height: 1.3;">{{ $story->title }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 9. GLOSSARY SECTION -->
        @if($glossary->count() > 0 && ($type === 'all' || $type === 'glossary'))
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h2 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>📖</span> Cricket Glossary Terms ({{ $glossary->count() }})
                </h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
                @foreach(($type === 'all' ? $glossary->take(4) : $glossary) as $term)
                    <a href="{{ route('glossary.show', $term->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px; transition: transform 0.15s, border-color 0.15s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#38bdf8';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                        <div style="font-weight: 800; font-size: 1rem; color: #38bdf8; margin-bottom: 4px;">{{ $term->term }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">{{ Str::limit($term->definition, 90) }}</div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    @endif

</main>
@endsection
