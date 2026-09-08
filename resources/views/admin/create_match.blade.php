@extends('layouts.admin')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">
    
    <!-- Top Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.02em;">
                📺 Live & Upcoming Matches
            </h1>
            <p style="font-size: 0.95rem; color: #64748b; margin: 4px 0 0 0;">
                Track ongoing live matches, schedule upcoming games, and view completed match results with winning scores.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="switchMatchTab('create')" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border: 1px solid #0284c7; border-radius: 6px; background: #0284c7; color: white; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 2px 6px rgba(2,132,199,0.25);">
                + Create New Match
            </button>
            <a href="{{ route('admin.dashboard') }}" style="text-decoration: none; font-weight: 700; color: #64748b; font-size: 0.88rem; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 6px; background: white;">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Status Tabs & Search Toolbar -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
        <div style="display: flex; align-items: center; gap: 10px; overflow-x: auto;">
            <button type="button" id="tab-btn-live" onclick="switchMatchTab('live')" class="match-tab active" style="padding: 10px 18px; border: none; background: transparent; font-weight: 800; font-size: 0.92rem; color: #ef4444; border-bottom: 3px solid #ef4444; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <span>⚡ Live Matches</span>
                <span style="background: #fee2e2; color: #ef4444; padding: 2px 7px; border-radius: 12px; font-size: 0.75rem;">{{ $liveMatches->count() }}</span>
            </button>

            <button type="button" id="tab-btn-upcoming" onclick="switchMatchTab('upcoming')" class="match-tab" style="padding: 10px 18px; border: none; background: transparent; font-weight: 700; font-size: 0.92rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <span>⏳ Upcoming</span>
                <span style="background: #f1f5f9; color: #475569; padding: 2px 7px; border-radius: 12px; font-size: 0.75rem;">{{ $upcomingMatches->count() }}</span>
            </button>

            <button type="button" id="tab-btn-completed" onclick="switchMatchTab('completed')" class="match-tab" style="padding: 10px 18px; border: none; background: transparent; font-weight: 700; font-size: 0.92rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <span>✅ Completed</span>
                <span style="background: #dcfce7; color: #16a34a; padding: 2px 7px; border-radius: 12px; font-size: 0.75rem;">{{ $completedMatches->count() }}</span>
            </button>

            <button type="button" id="tab-btn-create" onclick="switchMatchTab('create')" class="match-tab" style="padding: 10px 18px; border: none; background: transparent; font-weight: 700; font-size: 0.92rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <span>➕ Create Match</span>
            </button>
        </div>

        <!-- Live Matches Search -->
        <div style="display: flex; align-items: center; gap: 6px;">
            <input type="text" id="match-search-input" oninput="filterMatches()" onkeyup="filterMatches()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterMatches();}" placeholder="Search matches..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
            <button type="button" onclick="filterMatches()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                Search
            </button>
            <button type="button" onclick="document.getElementById('match-search-input').value=''; filterMatches();" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                Refresh
            </button>
        </div>
    </div>

    <!-- 1. LIVE MATCHES PANEL -->
    <div id="panel-live" class="tab-panel" style="display: flex; flex-direction: column; gap: 14px;">
        @forelse($liveMatches as $m)
            <div class="match-card-item" style="background: white; border: 1px solid #fecaca; border-left: 5px solid #ef4444; border-radius: 10px; padding: 20px 24px; box-shadow: 0 2px 6px rgba(239,68,68,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <span style="background: #ef4444; color: white; font-weight: 800; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 6px; height: 6px; background: white; border-radius: 50%; display: inline-block;"></span> LIVE
                        </span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                            {{ $m->match_type }} &bull; {{ $m->tournament->name ?? ($m->level_type ?? 'Match') }}
                        </span>
                    </div>

                    <!-- Teams & Scores -->
                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin-bottom: 6px;">
                        <span>{{ $m->team1?->name ?? 'Team 1' }}</span> 
                        <span style="color: #0284c7; font-weight: 800;">{{ $m->team1_score }}/{{ $m->team1_wickets }}</span> 
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">({{ $m->team1_overs }} ov)</span>
                        <span style="color: #94a3b8; margin: 0 6px;">vs</span>
                        <span>{{ $m->team2?->name ?? 'Team 2' }}</span> 
                        <span style="color: #0284c7; font-weight: 800;">{{ $m->team2_score }}/{{ $m->team2_wickets }}</span>
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">({{ $m->team2_overs }} ov)</span>
                    </div>

                    <div style="font-size: 0.85rem; color: #475569; font-weight: 600;">
                        {{ $m->custom_note && strlen($m->custom_note) < 60 ? $m->custom_note : 'Match currently in progress' }}
                        @if($m->venue) &bull; 📍 {{ $m->venue->name }} @endif
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.scorer', $m->id) }}" style="background: #0284c7; color: white; padding: 9px 18px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; text-decoration: none; box-shadow: 0 2px 4px rgba(2,132,199,0.25);">
                        ⚡ Live Scorer
                    </a>
                    
                    <button type="button" onclick="openCompleteModal({{ $m->id }}, '{{ addslashes($m->team1?->name ?? 'Team 1') }}', '{{ addslashes($m->team2?->name ?? 'Team 2') }}', {{ $m->team1_score }}, {{ $m->team2_score }})" style="background: #10b981; color: white; padding: 9px 14px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        ✓ Mark Completed
                    </button>

                    <a href="{{ route('admin.match.detail', $m->id) }}" style="background: white; border: 1px solid #cbd5e1; color: #334155; padding: 8px 12px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                        Details
                    </a>
                </div>
            </div>
        @empty
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 48px; text-align: center; color: #94a3b8;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🏏</div>
                <div style="font-weight: 700; color: #475569; font-size: 1rem;">No matches currently live.</div>
                <p style="font-size: 0.88rem; margin: 4px 0 16px 0;">Start an upcoming match to track live ball-by-ball score.</p>
                <button type="button" onclick="switchMatchTab('upcoming')" style="background: #0284c7; color: white; padding: 8px 20px; border-radius: 6px; border: none; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    View Upcoming Matches
                </button>
            </div>
        @endforelse
        <div id="panel-live-pagination"></div>
    </div>

    <!-- 2. UPCOMING MATCHES PANEL -->
    <div id="panel-upcoming" class="tab-panel" style="display: none; flex-direction: column; gap: 14px;">
        @forelse($upcomingMatches as $m)
            <div class="match-card-item" style="background: white; border: 1px solid #e2e8f0; border-left: 5px solid #0284c7; border-radius: 10px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                            ⏳ UPCOMING
                        </span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b;">
                            {{ $m->match_date ? date('M d, Y - h:i A', strtotime($m->match_date)) : 'Date Scheduled' }}
                        </span>
                        @if($m->tournament)
                            <span style="font-size: 0.8rem; color: #0284c7; font-weight: 700;">&bull; {{ $m->tournament->name }}</span>
                        @endif
                    </div>

                    <div style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin-bottom: 4px;">
                        {{ $m->team1?->name ?? 'Team 1' }} <span style="color: #94a3b8; font-weight: 600; font-size: 0.9rem;">vs</span> {{ $m->team2?->name ?? 'Team 2' }}
                    </div>

                    <div style="font-size: 0.82rem; color: #64748b;">
                        Format: <strong>{{ $m->match_type }}</strong> &bull; Level: <strong>{{ $m->level_type ?? 'Global' }}</strong>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.toss', $m->id) }}" style="background: #0284c7; color: white; padding: 8px 18px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; text-decoration: none;">
                        Start Match / Toss
                    </a>
                    <a href="{{ route('admin.match.detail', $m->id) }}" style="background: white; border: 1px solid #cbd5e1; color: #334155; padding: 8px 12px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                        Details
                    </a>
                </div>
            </div>
        @empty
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 48px; text-align: center; color: #94a3b8;">
                <div style="font-size: 2rem; margin-bottom: 8px;">📅</div>
                <div style="font-weight: 700; color: #475569; font-size: 1rem;">No upcoming matches scheduled.</div>
                <p style="font-size: 0.88rem; margin: 4px 0 16px 0;">Create a match under a series to schedule upcoming fixtures.</p>
                <button type="button" onclick="switchMatchTab('create')" style="background: #0284c7; color: white; padding: 8px 20px; border-radius: 6px; border: none; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    + Create Match
                </button>
            </div>
        @endforelse
        <div id="panel-upcoming-pagination"></div>
    </div>

    <!-- 3. COMPLETED MATCHES PANEL (With Winner Title & Scores) -->
    <div id="panel-completed" class="tab-panel" style="display: none; flex-direction: column; gap: 14px;">
        @forelse($completedMatches as $m)
            <div class="match-card-item" style="background: white; border: 1px solid #bbf7d0; border-left: 5px solid #10b981; border-radius: 10px; padding: 20px 24px; box-shadow: 0 1px 4px rgba(16,185,129,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="background: #dcfce7; color: #15803d; font-weight: 800; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                            ✓ COMPLETED
                        </span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b;">
                            {{ $m->match_date ? date('M d, Y', strtotime($m->match_date)) : 'Finished' }}
                        </span>
                        @if($m->tournament)
                            <span style="font-size: 0.8rem; color: #0284c7; font-weight: 700;">&bull; {{ $m->tournament->name }}</span>
                        @endif
                    </div>

                    <!-- Teams & Final Scores -->
                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin-bottom: 6px;">
                        <span>{{ $m->team1?->name ?? 'Team 1' }}</span> 
                        <span style="color: #0f172a; font-weight: 800;">{{ $m->team1_score }}/{{ $m->team1_wickets }}</span> 
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">({{ $m->team1_overs }} ov)</span>
                        <span style="color: #94a3b8; margin: 0 6px;">vs</span>
                        <span>{{ $m->team2?->name ?? 'Team 2' }}</span> 
                        <span style="color: #0f172a; font-weight: 800;">{{ $m->team2_score }}/{{ $m->team2_wickets }}</span>
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">({{ $m->team2_overs }} ov)</span>
                    </div>

                    <!-- Prominent Winner Title -->
                    <div style="font-size: 0.95rem; color: #16a34a; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                        <span>🏆 {{ $m->winning_title }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.scorer', $m->id) }}" style="background: #10b981; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; text-decoration: none;">
                        Scorecard
                    </a>
                    <a href="{{ route('admin.match.detail', $m->id) }}" style="background: white; border: 1px solid #cbd5e1; color: #334155; padding: 8px 12px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                        Details
                    </a>
                </div>
            </div>
        @empty
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 48px; text-align: center; color: #94a3b8;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🏆</div>
                <div style="font-weight: 700; color: #475569; font-size: 1rem;">No completed matches recorded yet.</div>
                <p style="font-size: 0.88rem; margin: 4px 0 0 0;">When a match finishes, its final scores and winner title will be displayed here.</p>
            </div>
        @endforelse
        <div id="panel-completed-pagination"></div>
    </div>

    <!-- 4. CREATE MATCH PANEL (Linked to Series) -->
    <div id="panel-create" class="tab-panel" style="display: none;">
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <form method="POST" action="{{ route('admin.match.post') }}">
                @csrf
                
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    🏆 Step 1: Select or Create Series / Tournament
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">
                            Choose Existing Series / Tournament
                        </label>
                        <select name="tournament_id" id="tournament_select" onchange="toggleNewSeriesInput(this.value)" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                            <option value="">-- Select Existing Series --</option>
                            @foreach($tournaments as $t)
                                <option value="{{ $t->id }}" data-teams-count="{{ $t->teams->count() }}">
                                    {{ $t->name }} ({{ $t->category ?? 'Global' }}) &bull; {{ $t->teams->count() }} Teams
                                </option>
                            @endforeach
                            <option value="new">+ Create New Series For This Match</option>
                        </select>
                        <div id="series_teams_alert" style="display: none; margin-top: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 0.88rem; color: #92400e;">
                            <div style="font-weight: 800; margin-bottom: 4px;">⚠️ Notice: This series currently has less than 2 teams!</div>
                            <div>A match requires at least 2 teams. Please create teams first or type team names below to create and link them.</div>
                            <div style="margin-top: 8px;">
                                <a id="series_teams_link" href="{{ route('admin.teams') }}" style="background: #f59e0b; color: #000; font-weight: 700; padding: 6px 14px; border-radius: 6px; text-decoration: none; display: inline-block; font-size: 0.82rem;">
                                    ➕ Manage &amp; Add Teams to this Series
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id="new_series_wrapper">
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">
                            Or Enter New Series Name
                        </label>
                        <input type="text" name="name" id="new_series_input" placeholder="" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    🏏 Step 2: Teams Playing
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">
                            Team 1 Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="team1_name" required placeholder="" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">
                            Team 2 Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="team2_name" required placeholder="" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                    ⚙️ Step 3: Match Details & Schedule
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Match Format *</label>
                        <select name="match_type" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                            <option value="T20">T20</option>
                            <option value="ODI">ODI</option>
                            <option value="Test">Test</option>
                            <option value="T10">T10</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Match Subtitle / Level</label>
                        <input type="text" name="level_type" placeholder="" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Status</label>
                        <select name="status" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                            <option value="upcoming">⏳ Upcoming (Scheduled)</option>
                            <option value="live">⚡ Live (In Progress)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Match Date & Time</label>
                        <input type="datetime-local" name="match_date" value="{{ date('Y-m-d\TH:i') }}" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="grid-column: span 2;">
                        <label style="display:block; margin-bottom: 8px; font-weight: 700; font-size: 0.9rem; color: #334155;">Venue / Stadium</label>
                        <input type="text" name="venue" placeholder="" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="margin-top: 10px;">
                    <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 12px 36px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(2,132,199,0.3);">
                        + Create & Publish Match
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Quick Complete Modal -->
<div id="completeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; max-width: 480px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin: 0 0 12px 0; color: #0f172a;">
            🏆 Mark Match as Completed
        </h3>
        <p style="font-size: 0.88rem; color: #64748b; margin: 0 0 16px 0;" id="modalMatchTitle">
            Update the final match result and winner title.
        </p>

        <form method="POST" action="{{ route('admin.update-match') }}" style="display: flex; flex-direction: column; gap: 14px;">
            @csrf
            <input type="hidden" name="match_id" id="modalMatchId" value="">
            <input type="hidden" name="status" value="completed">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px;" id="lblTeam1">Team 1 Score</label>
                    <input type="number" name="team1_score" id="modalTeam1Score" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px;" id="lblTeam2">Team 2 Score</label>
                    <input type="number" name="team2_score" id="modalTeam2Score" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 4px;">Result Text / Winner Title</label>
                <input type="text" name="result_text" id="modalResultText" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                <button type="button" onclick="closeCompleteModal()" style="padding: 8px 16px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding: 8px 20px; border: none; background: #10b981; color: white; border-radius: 6px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                    Confirm & Complete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchMatchTab(tab) {
    const panels = ['live', 'upcoming', 'completed', 'create'];
    panels.forEach(p => {
        const panel = document.getElementById('panel-' + p);
        const btn = document.getElementById('tab-btn-' + p);
        if (p === tab) {
            panel.style.display = (p === 'create' ? 'block' : 'flex');
            if (btn) {
                btn.style.color = (p === 'live' ? '#ef4444' : (p === 'completed' ? '#16a34a' : '#0284c7'));
                btn.style.borderBottomColor = (p === 'live' ? '#ef4444' : (p === 'completed' ? '#16a34a' : '#0284c7'));
            }
        } else {
            panel.style.display = 'none';
            if (btn) {
                btn.style.color = '#64748b';
                btn.style.borderBottomColor = 'transparent';
            }
        }
    });
}

function toggleNewSeriesInput(val) {
    const input = document.getElementById('new_series_input');
    const select = document.getElementById('tournament_select');
    const alertBox = document.getElementById('series_teams_alert');
    const selectedOpt = select.options[select.selectedIndex];
    const teamsCount = selectedOpt ? parseInt(selectedOpt.dataset.teamsCount || '0') : 0;

    if (val && val !== 'new' && val !== '') {
        input.value = '';
        input.placeholder = 'Selected Series: ' + (selectedOpt.text.split('•')[0] || selectedOpt.text).trim();
        input.disabled = true;
        if (teamsCount < 2) {
            alertBox.style.display = 'block';
            document.getElementById('series_teams_link').href = "{{ route('admin.teams') }}?tournament_id=" + val;
        } else {
            alertBox.style.display = 'none';
        }
    } else {
        input.disabled = false;
        input.placeholder = '';
        alertBox.style.display = 'none';
    }
}

function openCompleteModal(matchId, team1, team2, t1Score, t2Score) {
    document.getElementById('modalMatchId').value = matchId;
    document.getElementById('modalMatchTitle').innerText = team1 + ' vs ' + team2;
    document.getElementById('lblTeam1').innerText = team1 + ' Score';
    document.getElementById('lblTeam2').innerText = team2 + ' Score';
    document.getElementById('modalTeam1Score').value = t1Score;
    document.getElementById('modalTeam2Score').value = t2Score;
    
    let defaultResult = '';
    if (t1Score > t2Score) {
        defaultResult = team1 + ' won by ' + (t1Score - t2Score) + ' runs';
    } else if (t2Score > t1Score) {
        defaultResult = team2 + ' won';
    }
    document.getElementById('modalResultText').value = defaultResult;
    
    document.getElementById('completeModal').style.display = 'flex';
}

function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}

// Live, Upcoming, Completed Table Managers
let liveMatchMgr, upcomingMatchMgr, completedMatchMgr;
document.addEventListener('DOMContentLoaded', () => {
    liveMatchMgr = new AdminTableManager({
        tableId: 'panel-live',
        rowSelector: '#panel-live .match-card-item',
        searchInputId: 'match-search-input',
        paginationContainerId: 'panel-live-pagination',
        perPage: 10,
        noResultsMsg: 'No matching live matches found.'
    });

    upcomingMatchMgr = new AdminTableManager({
        tableId: 'panel-upcoming',
        rowSelector: '#panel-upcoming .match-card-item',
        searchInputId: 'match-search-input',
        paginationContainerId: 'panel-upcoming-pagination',
        perPage: 10,
        noResultsMsg: 'No matching upcoming matches found.'
    });

    completedMatchMgr = new AdminTableManager({
        tableId: 'panel-completed',
        rowSelector: '#panel-completed .match-card-item',
        searchInputId: 'match-search-input',
        paginationContainerId: 'panel-completed-pagination',
        perPage: 10,
        noResultsMsg: 'No matching completed matches found.'
    });
});

function filterMatches() {
    if (liveMatchMgr) liveMatchMgr.applyFilter(1);
    if (upcomingMatchMgr) upcomingMatchMgr.applyFilter(1);
    if (completedMatchMgr) completedMatchMgr.applyFilter(1);
}
</script>
@endsection
