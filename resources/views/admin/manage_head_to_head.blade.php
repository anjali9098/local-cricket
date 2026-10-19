@extends('layouts.admin')

@section('content')
<div style="max-width: 1380px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <!-- Flash Notifications -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 18px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
            <div>✓ {{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.1rem; color: #15803d; cursor: pointer;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 18px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
            <div>✗ {{ session('error') }}</div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.1rem; color: #dc2626; cursor: pointer;">&times;</button>
        </div>
    @endif

    <!-- Top Action Toolbar -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left breadcrumbs / filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Dashboard
            </a>

            <!-- Format Filter -->
            <form method="GET" action="{{ route('admin.head-to-head') }}" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                <select name="format" onchange="this.form.submit()" style="padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 130px; cursor: pointer;">
                    <option value="" {{ empty($format) ? 'selected' : '' }}>All Formats ({{ $statsSummary['total'] }})</option>
                    <option value="TEST" {{ $format === 'TEST' ? 'selected' : '' }}>TEST ({{ $statsSummary['test'] }})</option>
                    <option value="ODI" {{ $format === 'ODI' ? 'selected' : '' }}>ODI ({{ $statsSummary['odi'] }})</option>
                    <option value="T20" {{ $format === 'T20' ? 'selected' : '' }}>T20 ({{ $statsSummary['t20'] }})</option>
                </select>

                <!-- Search Input -->
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search team name..." style="padding: 7px 12px; padding-left: 30px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; width: 190px;">
                    <span style="position: absolute; left: 10px; color: #94a3b8; font-size: 0.85rem;">🔍</span>
                </div>
                <button type="submit" style="padding: 7px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background: #0284c7; color: white; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Filter
                </button>
                @if(!empty($format) || !empty($search))
                    <a href="{{ route('admin.head-to-head') }}" style="padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Right Action Buttons -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" onclick="toggleScraperPanel()" style="display: inline-flex; align-items: center; gap: 7px; padding: 7px 15px; border: 1px solid #bbf7d0; border-radius: 6px; background: #f0fdf4; color: #15803d; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                ⚡ Auto Sync Records
            </button>
            <button type="button" onclick="toggleH2HForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border: 1px solid #cbd5e1; border-radius: 6px; background: #0284c7; color: white; font-weight: 800; font-size: 0.85rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span>+</span> Add Head-to-Head
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards Bar -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Matchups</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #0f172a; margin-top: 2px;">{{ $statsSummary['total'] }}</div>
            </div>
            <div style="font-size: 1.8rem; background: #f1f5f9; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">⚔️</div>
        </div>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #dc2626; text-transform: uppercase;">TEST Cricket Records</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #dc2626; margin-top: 2px;">{{ $statsSummary['test'] }}</div>
            </div>
            <div style="font-size: 1.8rem; background: #fef2f2; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">🔴</div>
        </div>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #0284c7; text-transform: uppercase;">ODI Cricket Records</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #0284c7; margin-top: 2px;">{{ $statsSummary['odi'] }}</div>
            </div>
            <div style="font-size: 1.8rem; background: #f0f9ff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">⚪</div>
        </div>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.78rem; font-weight: 700; color: #16a34a; text-transform: uppercase;">T20 Cricket Records</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #16a34a; margin-top: 2px;">{{ $statsSummary['t20'] }}</div>
            </div>
            <div style="font-size: 1.8rem; background: #f0fdf4; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">⚡</div>
        </div>
    </div>

    <!-- Auto Sync Panel (AJAX Powered - No Page Freeze) -->
    <div id="scraper-panel" style="display: none; background: #f8fafc; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.05); animation: fadeIn 0.25s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                ⚡ Auto Sync Head-to-Head Records
            </h4>
            <button type="button" onclick="toggleScraperPanel()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <p style="margin: 0 0 16px 0; font-size: 0.86rem; color: #475569; line-height: 1.6;">
            Automatically fetch and update real head-to-head match stats (Total Matches, Won, Lost, Tied, Drawn, Home & Away wins) into the database with live execution speed metrics.
        </p>

        <form id="ajax-sync-form" onsubmit="runAjaxSync(event)" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            @csrf
            <div>
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Cricket Format:</label>
                <select id="sync-format" name="format" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; font-weight: 600; min-width: 140px; background: white;">
                    <option value="TEST">TEST Only</option>
                    <option value="ODI">ODI Only</option>
                    <option value="T20">T20 Only</option>
                    <option value="ALL">ALL Formats</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Sync Batch (Limit):</label>
                <select id="sync-limit" name="limit" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; font-weight: 600; min-width: 160px; background: white;">
                    <option value="5">⚡ 5 Matchups (Fast ~2s)</option>
                    <option value="10" selected>⚡ 10 Matchups (~4s)</option>
                    <option value="25">📦 25 Matchups (~10s)</option>
                    <option value="50">📦 50 Matchups (~20s)</option>
                    <option value="0">🌐 All Available</option>
                </select>
            </div>
            <div>
                <button type="submit" id="sync-submit-btn" style="background: #16a34a; color: white; border: none; padding: 9px 24px; border-radius: 6px; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
                    <span id="sync-btn-icon">▶</span>
                    <span id="sync-btn-text">Start Auto Sync</span>
                </button>
            </div>
        </form>

        <!-- Live Sync Status / Feedback Container -->
        <div id="sync-status-box" style="display: none; margin-top: 16px; padding: 14px 18px; border-radius: 6px; font-size: 0.88rem;"></div>

        <!-- Synced Details Log -->
        <div id="sync-log-box" style="display: none; margin-top: 10px; max-height: 180px; overflow-y: auto; background: white; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; font-size: 0.8rem; font-family: monospace; color: #334155;"></div>
    </div>

    <!-- Manual Add / Edit Form Panel -->
    <div id="h2h-form-container" style="display: {{ $editH2h ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.25s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                @if($editH2h)
                    ✏️ Edit Head to Head: {{ $editH2h->team1_name }} vs {{ $editH2h->team2_name }} ({{ $editH2h->format }})
                @else
                    ➕ Add Head to Head Matchup Record
                @endif
            </h3>
            <button type="button" onclick="toggleH2HForm()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form method="POST" action="{{ $editH2h ? route('admin.head-to-head.update', $editH2h->id) : route('admin.head-to-head.post') }}">
            @csrf

            <!-- Team 1, Team 2 & Format Selectors -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Team 1 <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="team1_id" id="form-team1-select" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; font-weight: 600; color: #0f172a;" {{ $editH2h ? 'disabled' : '' }}>
                        <option value="">-- Select Team 1 --</option>
                        @foreach($allTeams as $t)
                            <option value="{{ $t->id }}" {{ ($editH2h && $editH2h->team1_id == $t->id) ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($editH2h)
                        <input type="hidden" name="team1_id" value="{{ $editH2h->team1_id }}">
                    @endif
                </div>

                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Team 2 (Opponent Team) <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="team2_id" id="form-team2-select" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; font-weight: 600; color: #0f172a;" {{ $editH2h ? 'disabled' : '' }}>
                        <option value="">-- Select Team 2 --</option>
                        @foreach($allTeams as $t)
                            <option value="{{ $t->id }}" {{ ($editH2h && $editH2h->team2_id == $t->id) ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($editH2h)
                        <input type="hidden" name="team2_id" value="{{ $editH2h->team2_id }}">
                    @endif
                </div>

                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Format <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="format" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; font-weight: 700; color: #0f172a;" {{ $editH2h ? 'disabled' : '' }}>
                        <option value="TEST" {{ ($editH2h && $editH2h->format === 'TEST') ? 'selected' : '' }}>TEST</option>
                        <option value="ODI" {{ ($editH2h && $editH2h->format === 'ODI') ? 'selected' : '' }}>ODI</option>
                        <option value="T20" {{ ($editH2h && $editH2h->format === 'T20') ? 'selected' : '' }}>T20</option>
                        <option value="T10" {{ ($editH2h && $editH2h->format === 'T10') ? 'selected' : '' }}>T10</option>
                        <option value="IPL" {{ ($editH2h && $editH2h->format === 'IPL') ? 'selected' : '' }}>IPL</option>
                        <option value="ALL" {{ ($editH2h && $editH2h->format === 'ALL') ? 'selected' : '' }}>ALL Formats</option>
                    </select>
                    @if($editH2h)
                        <input type="hidden" name="format" value="{{ $editH2h->format }}">
                    @endif
                </div>
            </div>

            <!-- Match Stats Breakdown (Won, Lost, Drawn, Tied, No Result) -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 20px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Match Results Breakdown
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Total Matches:</label>
                        <input type="number" name="total_matches" value="{{ $editH2h->total_matches ?? 0 }}" min="0" placeholder="Auto" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700;">
                        <small style="color: #94a3b8; font-size: 0.72rem;">Leave 0 to auto-sum</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #16a34a; margin-bottom: 4px;">Team 1 Won:</label>
                        <input type="number" name="team1_won" value="{{ $editH2h->team1_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700; color: #16a34a;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #0284c7; margin-bottom: 4px;">Team 2 Won:</label>
                        <input type="number" name="team2_won" value="{{ $editH2h->team2_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700; color: #0284c7;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #64748b; margin-bottom: 4px;">Drawn (Test):</label>
                        <input type="number" name="drawn" value="{{ $editH2h->drawn ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #f59e0b; margin-bottom: 4px;">Tied:</label>
                        <input type="number" name="tied" value="{{ $editH2h->tied ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700; color: #b45309;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">No Result / Abandoned:</label>
                        <input type="number" name="no_result" value="{{ $editH2h->no_result ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem; font-weight: 700;">
                    </div>
                </div>
            </div>

            <!-- Home / Away / Neutral Venue Breakdown -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 20px;">
                <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Venue Performance Breakdown (Home / Away / Neutral)
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T1 Home Wins:</label>
                        <input type="number" name="team1_home_won" value="{{ $editH2h->team1_home_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T2 Home Wins:</label>
                        <input type="number" name="team2_home_won" value="{{ $editH2h->team2_home_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T1 Away Wins:</label>
                        <input type="number" name="team1_away_won" value="{{ $editH2h->team1_away_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T2 Away Wins:</label>
                        <input type="number" name="team2_away_won" value="{{ $editH2h->team2_away_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T1 Neutral Wins:</label>
                        <input type="number" name="team1_neutral_won" value="{{ $editH2h->team1_neutral_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 4px;">T2 Neutral Wins:</label>
                        <input type="number" name="team2_neutral_won" value="{{ $editH2h->team2_neutral_won ?? 0 }}" min="0" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.88rem;">
                    </div>
                </div>
            </div>

            <!-- Narrative / Summary Description -->
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    Narrative Summary Text (Displays in frontend comparison header)
                </label>
                <textarea name="summary_narrative" rows="3" placeholder="e.g. India and Australia have faced each other in 112 matches in Test. Out of these 112 games, Australia have won 46 whereas India have come out victorious on 33 occasions. 32 matches ended with draw." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; line-height: 1.5; font-family: inherit;">{{ $editH2h->summary_narrative ?? '' }}</textarea>
                <small style="color: #64748b; font-size: 0.75rem;">If left empty, a descriptive narrative is generated automatically from Won, Lost, and Drawn stats.</small>
            </div>

            <!-- Form Buttons -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="submit" style="background: #0284c7; color: white; border: none; padding: 9px 24px; border-radius: 6px; font-weight: 800; font-size: 0.9rem; cursor: pointer;">
                    {{ $editH2h ? 'Update Record' : 'Save Head to Head' }}
                </button>
                @if($editH2h)
                    <a href="{{ route('admin.head-to-head', ['format' => $format]) }}" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 9px 18px; border-radius: 6px; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        &larr; Back to Records List
                    </a>
                @else
                    <button type="button" onclick="toggleH2HForm()" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 9px 18px; border-radius: 6px; font-weight: 700; font-size: 0.9rem; cursor: pointer;">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    @if(!$editH2h)
    <!-- Records Table Card (Only shown when not editing) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">
                📋 Head-to-Head Records List
                <span style="font-weight: 600; font-size: 0.85rem; color: #64748b;">(Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }})</span>
            </div>
            <div style="font-size: 0.82rem; color: #64748b;">
                Head-to-head matchups database
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 16px;">Format</th>
                        <th style="padding: 12px 16px;">Matchup (Team 1 vs Team 2)</th>
                        <th style="padding: 12px 16px; text-align: center;">Total Played</th>
                        <th style="padding: 12px 16px; text-align: center;">T1 Won</th>
                        <th style="padding: 12px 16px; text-align: center;">T2 Won</th>
                        <th style="padding: 12px 16px; text-align: center;">Drawn</th>
                        <th style="padding: 12px 16px; text-align: center;">Tied</th>
                        <th style="padding: 12px 16px;">Home / Away Wins</th>
                        <th style="padding: 12px 16px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        @php
                            $fmtBg = '#f1f5f9';
                            $fmtColor = '#475569';
                            if ($rec->format === 'TEST') { $fmtBg = '#fef2f2'; $fmtColor = '#dc2626'; }
                            elseif ($rec->format === 'ODI') { $fmtBg = '#f0f9ff'; $fmtColor = '#0284c7'; }
                            elseif ($rec->format === 'T20') { $fmtBg = '#f0fdf4'; $fmtColor = '#16a34a'; }
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            
                            <!-- Format Badge -->
                            <td style="padding: 12px 16px;">
                                <span style="background: {{ $fmtBg }}; color: {{ $fmtColor }}; font-weight: 800; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                    {{ $rec->format }}
                                </span>
                            </td>

                            <!-- Matchup -->
                            <td style="padding: 12px 16px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="font-weight: 700; color: #0f172a;">
                                        {{ $rec->team1_name }}
                                    </div>
                                    <span style="font-size: 0.72rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 1px 6px; border-radius: 4px;">VS</span>
                                    <div style="font-weight: 700; color: #0f172a;">
                                        {{ $rec->team2_name }}
                                    </div>
                                </div>
                                @if(!empty($rec->summary_narrative))
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 3px; max-width: 440px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $rec->summary_narrative }}">
                                        {{ $rec->summary_narrative }}
                                    </div>
                                @endif
                            </td>

                            <!-- Total -->
                            <td style="padding: 12px 16px; text-align: center; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                                {{ $rec->total_matches }}
                            </td>

                            <!-- T1 Won -->
                            <td style="padding: 12px 16px; text-align: center; font-weight: 800; color: #16a34a;">
                                {{ $rec->team1_won }}
                            </td>

                            <!-- T2 Won -->
                            <td style="padding: 12px 16px; text-align: center; font-weight: 800; color: #0284c7;">
                                {{ $rec->team2_won }}
                            </td>

                            <!-- Drawn -->
                            <td style="padding: 12px 16px; text-align: center; font-weight: 700; color: #64748b;">
                                {{ $rec->drawn }}
                            </td>

                            <!-- Tied -->
                            <td style="padding: 12px 16px; text-align: center; font-weight: 700; color: #b45309;">
                                {{ $rec->tied }}
                            </td>

                            <!-- Home / Away -->
                            <td style="padding: 12px 16px; font-size: 0.78rem; color: #475569;">
                                <div><strong>Home:</strong> {{ $rec->team1_name }}: {{ $rec->team1_home_won }} | {{ $rec->team2_name }}: {{ $rec->team2_home_won }}</div>
                                <div><strong>Away:</strong> {{ $rec->team1_name }}: {{ $rec->team1_away_won }} | {{ $rec->team2_name }}: {{ $rec->team2_away_won }}</div>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="{{ route('admin.head-to-head', array_merge(request()->query(), ['edit_id' => $rec->id])) }}" style="background: #f1f5f9; color: #0284c7; padding: 4px 10px; border-radius: 4px; font-size: 0.78rem; font-weight: 700; text-decoration: none; border: 1px solid #cbd5e1;">
                                        Edit
                                    </a>
                                    <a href="{{ route('head-to-head', ['team1' => $rec->team1_id, 'team2' => $rec->team2_id, 'format' => $rec->format]) }}" target="_blank" style="background: #f0fdf4; color: #16a34a; padding: 4px 10px; border-radius: 4px; font-size: 0.78rem; font-weight: 700; text-decoration: none; border: 1px solid #bbf7d0;" title="View on site">
                                        View ↗
                                    </a>
                                    <form method="POST" action="{{ route('admin.head-to-head.delete', $rec->id) }}" style="margin: 0; display: inline;" onsubmit="return confirm('Delete head to head record between {{ addslashes($rec->team1_name) }} and {{ addslashes($rec->team2_name) }} ({{ $rec->format }})?');">
                                        @csrf
                                        <button type="submit" style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 4px; font-size: 0.78rem; font-weight: 700; border: 1px solid #fecaca; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 36px 20px; text-align: center; color: #64748b;">
                                <div style="font-size: 2rem; margin-bottom: 8px;">🏏</div>
                                <div style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">No Head-to-Head Records Found</div>
                                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 14px;">Try clearing filters, sync latest records, or add a new matchup record.</div>
                                <div style="display: inline-flex; gap: 10px; justify-content: center;">
                                    <button type="button" onclick="toggleScraperPanel()" style="background: #16a34a; color: white; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                                        ⚡ Auto Sync Records
                                    </button>
                                    <button type="button" onclick="toggleH2HForm()" style="background: #0284c7; color: white; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                                        + Add Head-to-Head
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($records->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                {{ $records->links() }}
            </div>
        @endif
    </div>
    @endif

</div>


<script>
function toggleH2HForm() {
    const el = document.getElementById('h2h-form-container');
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    if (el.style.display === 'block') {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function toggleScraperPanel() {
    const el = document.getElementById('scraper-panel');
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    if (el.style.display === 'block') {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

let syncTimerInterval = null;

async function runAjaxSync(e) {
    e.preventDefault();
    const btn = document.getElementById('sync-submit-btn');
    const icon = document.getElementById('sync-btn-icon');
    const text = document.getElementById('sync-btn-text');
    const statusBox = document.getElementById('sync-status-box');
    const logBox = document.getElementById('sync-log-box');
    const format = document.getElementById('sync-format').value;
    const limit = document.getElementById('sync-limit').value;

    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'wait';
    icon.textContent = '⏳';
    text.textContent = 'Syncing...';

    statusBox.style.display = 'block';
    statusBox.style.background = '#eff6ff';
    statusBox.style.border = '1px solid #bfdbfe';
    statusBox.style.color = '#1d4ed8';
    
    let seconds = 0;
    statusBox.innerHTML = `<strong>Connecting...</strong> Fetching official records for <strong>${format}</strong> (Batch: ${limit === '0' ? 'All' : limit}). <span style="font-weight: 800; margin-left: 8px;">⏱️ 0.0s elapsed</span>`;
    
    syncTimerInterval = setInterval(() => {
        seconds += 0.2;
        statusBox.innerHTML = `<strong>Syncing in progress...</strong> Fetching official records for <strong>${format}</strong>. <span style="font-weight: 800; margin-left: 8px;">⏱️ ${seconds.toFixed(1)}s elapsed</span>`;
    }, 200);

    logBox.style.display = 'none';
    logBox.innerHTML = '';

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('format', format);
    formData.append('limit', limit);

    try {
        const res = await fetch('{{ route("admin.head-to-head.sync-scraper") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        clearInterval(syncTimerInterval);
        const data = await res.json();

        if (data.success) {
            statusBox.style.background = '#f0fdf4';
            statusBox.style.border = '1px solid #bbf7d0';
            statusBox.style.color = '#15803d';
            statusBox.innerHTML = `<strong>✓ Success:</strong> ${data.message} <button onclick="window.location.reload()" style="margin-left: 12px; background: #16a34a; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 0.78rem; cursor: pointer;">Refresh List ↻</button>`;

            if (data.details && data.details.length > 0) {
                logBox.style.display = 'block';
                logBox.innerHTML = '<strong>Updated Matchups:</strong><br>' + data.details.map(d => `<div style="padding: 2px 0;">${d}</div>`).join('');
            }

            // Auto reload list after 3 seconds so user sees updated table automatically
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            statusBox.style.background = '#fef2f2';
            statusBox.style.border = '1px solid #fecaca';
            statusBox.style.color = '#dc2626';
            statusBox.innerHTML = `<strong>✗ Error:</strong> ${data.message || 'Scraper failed.'} (Time taken: ${data.duration || '0s'})`;
        }
    } catch (err) {
        clearInterval(syncTimerInterval);
        statusBox.style.background = '#fef2f2';
        statusBox.style.border = '1px solid #fecaca';
        statusBox.style.color = '#dc2626';
        statusBox.innerHTML = `<strong>✗ Request Error:</strong> ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        icon.textContent = '▶';
        text.textContent = 'Start Auto Sync';
    }
}
</script>
@endsection
