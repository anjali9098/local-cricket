@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="tournament-hero-banner" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 36px 20px; transition: background-color 0.3s ease, border-color 0.3s ease;">
    <div class="max-w-6xl mx-auto">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 0.82rem; font-weight: 700; flex-wrap: wrap;">
            <span style="background: #f97316; color: #ffffff; padding: 3px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em;">{{ $tournament->format }}</span>
            <span style="border: 1px solid var(--border-color); background: var(--bg-card-secondary); color: var(--text-muted); padding: 3px 12px; border-radius: 9999px; text-transform: lowercase; font-size: 0.72rem; font-weight: 700;">{{ $tournament->status }}</span>
            <span style="color: var(--text-dim); display: inline-flex; align-items: center; gap: 4px;"><span>📍</span> {{ $tournament->city ?? 'Unknown' }}</span>
        </div>
        
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: -0.02em; margin: 0 0 10px 0; line-height: 1.2;">
            {{ $tournament->name }}
        </h1>
        
        <div style="font-size: 0.88rem; color: var(--text-muted); font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span>📅</span> {{ \Carbon\Carbon::parse($tournament->start_date)->format('n/j/Y') }} &mdash; {{ \Carbon\Carbon::parse($tournament->end_date)->format('n/j/Y') }}
        </div>
        
        <div style="font-size: 0.85rem; color: var(--text-dim); max-width: 680px; font-weight: 500; line-height: 1.5;">
            {{ $tournament->description ?? 'Official tournament page.' }}
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 pb-28">
    
    <!-- Live Now Section -->
    <div class="mb-8">
        <h3 style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.04em;">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ef4444; animation: pulse 2s infinite;"></span>
            LIVE NOW
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $liveMatches = $tournament->matches->whereIn('status', ['ongoing', 'live']);
            @endphp
            
            @if($liveMatches->count() > 0)
                @foreach($liveMatches as $liveMatch)
                <a href="{{ route('matches.detail', $liveMatch->id) }}" style="display: block; background: var(--bg-card); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; padding: 18px; text-decoration: none; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="display: inline-block; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">&bull; LIVE</div>
                    <div style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin-bottom: 8px;">
                        {{ $liveMatch->team1->name ?? 'TBA' }} <span style="color: #ef4444;">VS</span> {{ $liveMatch->team2->name ?? 'TBA' }}
                    </div>
                    <div style="font-size: 0.82rem; color: #38bdf8; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                        <span>Click to view live scorer</span> &rarr;
                    </div>
                </a>
                @endforeach
            @else
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; color: var(--text-muted); font-weight: 600; font-size: 0.88rem;">
                    No live matches at the moment.
                </div>
            @endif
        </div>
    </div>

    <!-- Tournament Interactive Navigation Tabs -->
    <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
        <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;" class="no-scrollbar">
            <button type="button" onclick="switchTournamentTab('tab-points')" id="btn-tab-points" class="tournament-tab-btn active-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--primary); background: var(--primary); color: #ffffff; transition: all 0.2s;">
                <span>🏆</span> POINTS TABLE
            </button>
            <button type="button" onclick="switchTournamentTab('tab-teams')" id="btn-tab-teams" class="tournament-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>👥</span> TEAMS <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($teams) }}</span>
            </button>
            <button type="button" onclick="switchTournamentTab('tab-highest-score')" id="btn-tab-highest-score" class="tournament-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>🏏</span> HIGHEST SCORE
            </button>
            <button type="button" onclick="switchTournamentTab('tab-players')" id="btn-tab-players" class="tournament-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>👤</span> PLAYERS <span style="background: var(--bg-card-secondary); font-size: 0.75rem; padding: 2px 7px; border-radius: 9999px; border: 1px solid var(--border-color);">{{ count($players) }}</span>
            </button>
            <button type="button" onclick="switchTournamentTab('tab-most-wickets')" id="btn-tab-most-wickets" class="tournament-tab-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-muted); transition: all 0.2s;">
                <span>🎯</span> MOST WICKETS
            </button>
        </div>
    </div>

    <!-- ==========================================
         TAB 1: POINTS TABLE
         ========================================== -->
    <div id="tab-points" class="tournament-tab-pane">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>🏆</span> TOURNAMENT STANDINGS
            </h3>
            <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-dim);">Win = 2 Pts &bull; Tie/NR = 1 Pt</span>
        </div>
        
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table class="cricket-table" style="min-width: 500px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Team</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">P</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">W</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">L</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Pts</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">NRR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointsTable as $index => $row)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="width: 22px; height: 22px; border-radius: 50%; background: var(--bg-card-secondary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 900; color: var(--text-dim);">
                                        {{ $index + 1 }}
                                    </span>
                                    <span>{{ $row['team']->name }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $row['p'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $row['w'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: #ef4444; text-align: center; font-size: 0.88rem;">{{ $row['l'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 900; color: var(--primary); text-align: center; font-size: 0.95rem;">{{ $row['pts'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $row['nrr'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">No teams registered in this tournament yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==========================================
         TAB 2: TEAMS SHOW
         ========================================== -->
    <div id="tab-teams" class="tournament-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> PARTICIPATING TEAMS ({{ count($teams) }})
            </h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px;">
            @forelse($teams as $team)
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <!-- Team Header -->
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                        @if(!empty($team->logo))
                            <img src="{{ $team->logo }}" alt="{{ $team->name }}" style="width: 52px; height: 52px; border-radius: 12px; object-fit: cover; border: 1.5px solid var(--border-color);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        @endif
                        <div style="width: 52px; height: 52px; border-radius: 12px; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: {{ !empty($team->logo) ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; color: var(--primary);">
                            {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $team->name), 0, 2)) ?: 'TM' }}
                        </div>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0;">
                                {{ $team->name }}
                            </h4>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span style="font-size: 0.72rem; font-weight: 800; background: var(--bg-card-secondary); color: var(--text-muted); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border-color);">
                                    🏏 {{ count($team->players) }} Players
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Squad Players List Preview -->
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; margin-top: 6px;">
                        <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); margin-bottom: 8px;">
                            Squad Roster
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @forelse($team->players as $p)
                                <a href="{{ route('player.profile', $p->id) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 4px 8px; font-size: 0.78rem; font-weight: 700; color: var(--text-main); transition: border-color 0.15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                                    <span>👤</span> {{ $p->name }}
                                </a>
                            @empty
                                <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic;">No players added yet.</span>
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

    <!-- ==========================================
         TAB 3: HIGHEST SCORE / TOP RUNS
         ========================================== -->
    <div id="tab-highest-score" class="tournament-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>🏏</span> HIGHEST RUN SCORERS &amp; BATTING STATS
            </h3>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table class="cricket-table" style="min-width: 600px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">#</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Batter</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Inns</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Runs</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Highest Score</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Balls</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">4s</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">6s</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Strike Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topBatters as $index => $b)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem;">{{ $index + 1 }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span>🏏</span>
                                    <span>{{ $b->player_name }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->innings }}</td>
                            <td style="padding: 14px 18px; font-weight: 900; color: var(--primary); text-align: center; font-size: 0.95rem;">{{ $b->runs }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.9rem;">{{ $b->highest_score }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->balls }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->fours }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $b->sixes }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #10b981; text-align: center; font-size: 0.88rem;">{{ $b->strike_rate }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">
                                No batting records recorded yet for this tournament.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==========================================
         TAB 4: PLAYERS
         ========================================== -->
    <div id="tab-players" class="tournament-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> TOURNAMENT PLAYERS ({{ count($players) }})
            </h3>
            
            <div style="display: flex; align-items: center; gap: 10px; min-width: 260px;">
                <input type="text" id="tournamentPlayerSearch" oninput="filterTournamentPlayers()" placeholder="Search players by name or team..." style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 0.88rem; color: var(--text-main); outline: none;">
            </div>
        </div>

        <div id="tournamentPlayersGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @forelse($players as $p)
                <div class="tournament-player-card" data-search="{{ strtolower($p->name . ' ' . ($p->team_name ?? '') . ' ' . ($p->role ?? '')) }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 14px; transition: transform 0.2s ease, border-color 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
                    
                    @if(!empty($p->profile_image))
                        <img src="{{ $p->profile_image }}" alt="{{ $p->name }}" style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-color);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    @endif
                    <div style="width: 46px; height: 46px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: {{ !empty($p->profile_image) ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-weight: 900; font-size: 0.95rem; color: var(--primary); flex-shrink: 0;">
                        {{ $p->initials ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $p->name), 0, 2)) }}
                    </div>

                    <div style="min-width: 0; flex: 1;">
                        <a href="{{ route('player.profile', $p->id) }}" style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                            {{ $p->name }}
                        </a>
                        <div style="font-size: 0.76rem; color: var(--text-muted); font-weight: 700; margin-top: 2px;">
                            {{ $p->team_name ?? 'Team Member' }}
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

    <!-- ==========================================
         TAB 5: MOST WICKETS
         ========================================== -->
    <div id="tab-most-wickets" class="tournament-tab-pane" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
                <span>🎯</span> MOST WICKETS &amp; BOWLING STATS
            </h3>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table class="cricket-table" style="min-width: 600px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">#</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Bowler</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Inns</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Overs</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Runs Conceded</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Wickets</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Economy</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Best Figures</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topBowlers as $index => $w)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 900; color: var(--text-dim); font-size: 0.85rem;">{{ $index + 1 }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span>🎯</span>
                                    <span>{{ $w->player_name }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->innings }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->overs }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $w->runs }}</td>
                            <td style="padding: 14px 18px; font-weight: 900; color: #ef4444; text-align: center; font-size: 0.95rem;">{{ $w->wickets }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #10b981; text-align: center; font-size: 0.88rem;">{{ $w->economy }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $w->best_figure }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">
                                No bowling records recorded yet for this tournament.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Interactive Tab Switcher & Search Script -->
<script>
function switchTournamentTab(tabId) {
    // Hide all panes
    const panes = document.querySelectorAll('.tournament-tab-pane');
    panes.forEach(pane => pane.style.display = 'none');

    // Show target pane
    const target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
    }

    // Update buttons
    const buttons = document.querySelectorAll('.tournament-tab-btn');
    buttons.forEach(btn => {
        btn.style.background = 'var(--bg-card)';
        btn.style.color = 'var(--text-muted)';
        btn.style.borderColor = 'var(--border-color)';
    });

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.style.background = 'var(--primary)';
        activeBtn.style.color = '#ffffff';
        activeBtn.style.borderColor = 'var(--primary)';
    }
}

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
</script>
@endsection

