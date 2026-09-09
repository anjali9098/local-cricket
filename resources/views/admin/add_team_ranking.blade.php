@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar matching Series Style -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>

            <!-- Type Filter -->
            <select id="filter-ranking-type" onchange="filterRankingTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 140px;">
                <option value="">All Rankings</option>
                <option value="team">Team Standings</option>
                <option value="batting">Batting Leaderboard</option>
                <option value="bowling">Bowling Leaderboard</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="text" id="ranking-search-input" oninput="filterRankingTable()" onkeyup="filterRankingTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterRankingTable();}" placeholder="Search rankings..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 220px;">
                <button type="button" onclick="filterRankingTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetRankingSearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="toggleRankingForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add Ranking
            </button>
        </div>
    </div>

    <!-- Add / Edit Ranking Form Panel -->
    @php
        $isEditing = $editTeam || $editPlayer;
        $activeFormType = $editPlayer ? ($editPlayer->type ?? 'batting') : 'team';
    @endphp
    <div id="ranking-form-container" style="display: {{ $isEditing ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                @if($editTeam)
                    ✏️ Edit Team Standing: {{ $editTeam->team_name }}
                @elseif($editPlayer)
                    ✏️ Edit Player Ranking: {{ $editPlayer->player_name }} ({{ strtoupper($editPlayer->type) }})
                @else
                    🏆 Add Team / Player Ranking
                @endif
            </h3>
            <button type="button" onclick="toggleRankingForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <!-- TYPE SELECTOR -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 18px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 800; font-size: 0.85rem; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">
                SELECT RANKING TYPE <span style="color:#ef4444;">*</span>
            </label>
            <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.9rem;">
                    <input type="radio" name="ranking_category_switch" value="team" onchange="switchRankingFormType('team')" {{ $activeFormType === 'team' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🛡️ TEAM STANDINGS</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.9rem;">
                    <input type="radio" name="ranking_category_switch" value="batting" onchange="switchRankingFormType('batting')" {{ $activeFormType === 'batting' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🏏 BATTING (MOST RUNS)</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.9rem;">
                    <input type="radio" name="ranking_category_switch" value="bowling" onchange="switchRankingFormType('bowling')" {{ $activeFormType === 'bowling' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🎯 BOWLING (MOST WICKETS)</span>
                </label>
            </div>
        </div>

        <!-- FORM 1: TEAM STANDING -->
        <div id="form-team-standing" style="display: {{ $activeFormType === 'team' ? 'block' : 'none' }};">
            <form method="POST" action="{{ $editTeam ? route('admin.ranking.update', $editTeam->id) : route('admin.ranking.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf

                <!-- ROW 1: Team Name & Slug | Rank # | Display Order -->
                <div style="display: grid; grid-template-columns: 2.2fr 1fr 1fr; gap: 16px; align-items: flex-start;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Team Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="team_ranking_name" name="team_name" value="{{ old('team_name', $editTeam->team_name ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value, 'team_ranking_slug')" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                        
                        <input type="text" id="team_ranking_slug" name="slug" value="{{ old('slug', $editTeam->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rank # <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rank_num" min="1" value="{{ old('rank_num', $editTeam->rank_num ?? '') }}" required placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Display Order
                        </label>
                        <input type="number" name="display_order" min="1" value="{{ old('display_order', $editTeam->display_order ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 2: Matches Played | Won | NRR | Points -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Matches Played
                        </label>
                        <input type="number" name="matches_played" min="0" value="{{ old('matches_played', $editTeam->matches_played ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Won
                        </label>
                        <input type="number" name="won" min="0" value="{{ old('won', $editTeam->won ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            NRR (Net Run Rate)
                        </label>
                        <input type="text" name="nrr" value="{{ old('nrr', $editTeam->nrr ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Points <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="points" min="0" value="{{ old('points', $editTeam->points ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 3: Category & Keywords -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Category
                        </label>
                        <input type="text" name="category" value="{{ old('category', $editTeam->category ?? 'ALL') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Keywords
                        </label>
                        <input type="text" name="keywords" value="{{ old('keywords', $editTeam->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 4: Team Logo / Poster & SUBMIT -->
                <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                    <div style="flex: 1; min-width: 280px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                                Team Logo / Poster Image
                            </label>
                            <span id="ranking-team-logo-badge" style="display: none;"></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="file" name="poster_file" accept="image/*" onchange="previewAndConvertImage(this, 'ranking_team_logo_input', 'ranking-team-logo-preview', 'ranking-team-logo-badge')" style="font-size: 0.82rem; color: #475569;">
                            <input type="text" id="ranking_team_logo_input" name="logo_url" value="{{ old('logo_url', $editTeam->logo_url ?? '') }}" oninput="previewUrlImage(this, 'ranking-team-logo-preview')" placeholder="Image URL or auto-filled from upload" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                            <img id="ranking-team-logo-preview" src="{{ old('logo_url', $editTeam->logo_url ?? '') }}" alt="Logo" style="height: 38px; border-radius: 4px; border: 1px solid #cbd5e1; display: {{ !empty(old('logo_url', $editTeam->logo_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                        </div>
                    </div>

                    <div>
                        <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                            {{ $editTeam ? 'UPDATE STANDING' : 'SUBMIT' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- FORM 2: PLAYER LEADERBOARD (Batting / Bowling) -->
        <div id="form-player-ranking" style="display: {{ $activeFormType === 'batting' || $activeFormType === 'bowling' ? 'block' : 'none' }};">
            <form method="POST" action="{{ $editPlayer ? route('admin.player-ranking.update', $editPlayer->id) : route('admin.player-ranking.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf
                <input type="hidden" id="player_ranking_type_input" name="type" value="{{ old('type', $editPlayer->type ?? 'batting') }}">

                <!-- ROW 1: Player Name & Slug | Badge / Country | Rank # | Display Order -->
                <div style="display: grid; grid-template-columns: 2.2fr 1fr 1fr 0.8fr; gap: 16px; align-items: flex-start;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Player Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="player_ranking_name" name="player_name" value="{{ old('player_name', $editPlayer->player_name ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value, 'player_ranking_slug')" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                        
                        <input type="text" id="player_ranking_slug" name="slug" value="{{ old('slug', $editPlayer->slug ?? '') }}" placeholder="" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Badge / Team (e.g. IND) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="badge_text" maxlength="6" value="{{ old('badge_text', $editPlayer->badge_text ?? '') }}" required placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; text-transform: uppercase;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rank # <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rank_num" min="1" value="{{ old('rank_num', $editPlayer->rank_num ?? '') }}" required placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Display Order
                        </label>
                        <input type="number" name="display_order" min="1" value="{{ old('display_order', $editPlayer->display_order ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 2: Stat Value & Keywords -->
                <div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 16px;">
                    <div>
                        <label id="stat_value_label" style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Stat Value (Runs / Wickets) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="stat_value" min="0" value="{{ old('stat_value', $editPlayer->stat_value ?? '') }}" required placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Keywords
                        </label>
                        <input type="text" name="keywords" value="{{ old('keywords', $editPlayer->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 3: Player Photo / Poster & SUBMIT -->
                <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                    <div style="flex: 1; min-width: 280px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                                Player Photo / Poster Image
                            </label>
                            <span id="ranking-player-photo-badge" style="display: none;"></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="file" name="poster_file" accept="image/*" onchange="previewAndConvertImage(this, 'ranking_player_photo_input', 'ranking-player-photo-preview', 'ranking-player-photo-badge')" style="font-size: 0.82rem; color: #475569;">
                            <input type="text" id="ranking_player_photo_input" name="photo_url" value="{{ old('photo_url', $editPlayer->photo_url ?? '') }}" oninput="previewUrlImage(this, 'ranking-player-photo-preview')" placeholder="Image URL or auto-filled from upload" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                            <img id="ranking-player-photo-preview" src="{{ old('photo_url', $editPlayer->photo_url ?? '') }}" alt="Photo" style="height: 38px; border-radius: 4px; border: 1px solid #cbd5e1; display: {{ !empty(old('photo_url', $editPlayer->photo_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                        </div>
                    </div>

                    <div>
                        <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                            {{ $editPlayer ? 'UPDATE PLAYER RANKING' : 'SUBMIT' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- Existing Rankings List Table (Matching Exact Series Style) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        <div style="overflow-x: auto;">
            <table id="ranking-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                        <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                        <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                        <th style="padding: 10px 14px; min-width: 220px;">NAME</th>
                        <th style="padding: 10px 12px; width: 140px;">TYPE</th>
                        <th style="padding: 10px 14px; min-width: 180px;">PTS / STATS</th>
                        <th style="padding: 10px 14px; min-width: 160px;">PAGE LINK</th>
                        <th style="padding: 10px 14px; min-width: 160px;">ADD/UPDATE</th>
                        <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Team Standings Rows -->
                    @foreach($teamRankings as $tr)
                        <tr class="tbl-ranking-row" data-type="team" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                            
                            <!-- # EDIT -->
                            <td style="padding: 12px 10px; vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <a href="{{ route('admin.ranking', ['edit_team' => $tr->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        #{{ $loop->iteration }}
                                    </a>
                                    <a href="{{ route('admin.ranking', ['edit_team' => $tr->id]) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;" title="Edit Standing">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                </div>
                            </td>

                            <!-- ORDER -->
                            <td style="padding: 12px 10px; vertical-align: middle; color: #334155; font-weight: 700;">
                                {{ $tr->display_order ?: $tr->rank_num }}
                            </td>

                            <!-- POSTER / LOGO -->
                            <td style="padding: 10px 12px; vertical-align: middle;">
                                @if($tr->logo_url)
                                    <img src="{{ $tr->logo_url }}" alt="{{ $tr->team_name }}" style="width: 50px; height: 38px; object-fit: contain; border-radius: 4px; border: 1px solid #e2e8f0; background: #fafafa;">
                                @else
                                    <div style="width: 50px; height: 38px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #0284c7; font-weight: 900; font-size: 0.75rem;">
                                        {{ strtoupper(substr($tr->team_name, 0, 3)) }}
                                    </div>
                                @endif
                            </td>

                            <!-- NAME -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                    {{ $tr->team_name }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    Rank #{{ $tr->rank_num }} &bull; {{ $tr->category ?? 'Points Table' }}
                                </div>
                            </td>

                            <!-- TYPE -->
                            <td style="padding: 12px 12px; vertical-align: middle;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em;">
                                    TEAM STANDING
                                </span>
                            </td>

                            <!-- PTS / STATS -->
                            <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.85rem; color: #1e293b;">
                                <strong style="color: #0f172a;">{{ $tr->points }} PTS</strong> 
                                <span style="color: #64748b; font-size: 0.8rem;">(P:{{ $tr->matches_played }} | W:{{ $tr->won }} | NRR:{{ $tr->nrr }})</span>
                            </td>

                            <!-- PAGE LINK -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <a href="{{ url('/stats') }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;">
                                    /stats
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </td>

                            <!-- ADD/UPDATE -->
                            <td style="padding: 12px 14px; vertical-align: middle; color: #475569; font-size: 0.8rem;">
                                {{ $tr->updated_at ? \Carbon\Carbon::parse($tr->updated_at)->format('d M Y, h:i A') : ($tr->created_at ? \Carbon\Carbon::parse($tr->created_at)->format('d M Y, h:i A') : 'Active') }}
                            </td>

                            <!-- ACTION -->
                            <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                <form method="POST" action="{{ route('admin.ranking.delete', $tr->id) }}" onsubmit="return confirm('Are you sure you want to delete this team standing?');" style="margin: 0; display: inline;">
                                    @csrf
                                    <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-weight: 800; font-size: 0.78rem; padding: 4px 10px; border-radius: 4px; cursor: pointer; transition: all 0.2s;">
                                        Delete
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @endforeach

                    <!-- Player Leaderboard Rows (Batting / Bowling) -->
                    @foreach($playerRankings as $pr)
                        <tr class="tbl-ranking-row" data-type="{{ strtolower($pr->type) }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                            
                            <!-- # EDIT -->
                            <td style="padding: 12px 10px; vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <a href="{{ route('admin.ranking', ['edit_player' => $pr->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        #{{ $loop->iteration + $teamRankings->count() }}
                                    </a>
                                    <a href="{{ route('admin.ranking', ['edit_player' => $pr->id]) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;" title="Edit Player Ranking">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                </div>
                            </td>

                            <!-- ORDER -->
                            <td style="padding: 12px 10px; vertical-align: middle; color: #334155; font-weight: 700;">
                                {{ $pr->display_order ?: $pr->rank_num }}
                            </td>

                            <!-- POSTER / LOGO -->
                            <td style="padding: 10px 12px; vertical-align: middle;">
                                @if($pr->photo_url)
                                    <img src="{{ $pr->photo_url }}" alt="{{ $pr->player_name }}" style="width: 50px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                                @else
                                    <div style="width: 50px; height: 38px; background: {{ strtolower($pr->type) === 'batting' ? '#fef3c7' : '#dcfce7' }}; border: 1px solid {{ strtolower($pr->type) === 'batting' ? '#fde68a' : '#bbf7d0' }}; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: {{ strtolower($pr->type) === 'batting' ? '#d97706' : '#15803d' }}; font-weight: 900; font-size: 0.75rem;">
                                        {{ $pr->badge_text ?: 'IND' }}
                                    </div>
                                @endif
                            </td>

                            <!-- NAME -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                    {{ $pr->player_name }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    Rank #{{ $pr->rank_num }} &bull; {{ $pr->badge_text }}
                                </div>
                            </td>

                            <!-- TYPE -->
                            <td style="padding: 12px 12px; vertical-align: middle;">
                                @if(strtolower($pr->type) === 'batting')
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em;">
                                        🏏 BATTING
                                    </span>
                                @else
                                    <span style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em;">
                                        🎯 BOWLING
                                    </span>
                                @endif
                            </td>

                            <!-- PTS / STATS -->
                            <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.88rem; color: #1e293b; font-weight: 800;">
                                {{ number_format($pr->stat_value) }} {{ strtolower($pr->type) === 'batting' ? 'Runs' : 'Wickets' }}
                            </td>

                            <!-- PAGE LINK -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <a href="{{ url('/stats') }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;">
                                    /stats
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </td>

                            <!-- ADD/UPDATE -->
                            <td style="padding: 12px 14px; vertical-align: middle; color: #475569; font-size: 0.8rem;">
                                {{ $pr->updated_at ? \Carbon\Carbon::parse($pr->updated_at)->format('d M Y, h:i A') : ($pr->created_at ? \Carbon\Carbon::parse($pr->created_at)->format('d M Y, h:i A') : 'Active') }}
                            </td>

                            <!-- ACTION -->
                            <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                <form method="POST" action="{{ route('admin.player-ranking.delete', $pr->id) }}" onsubmit="return confirm('Are you sure you want to delete this player ranking?');" style="margin: 0; display: inline;">
                                    @csrf
                                    <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-weight: 800; font-size: 0.78rem; padding: 4px 10px; border-radius: 4px; cursor: pointer; transition: all 0.2s;">
                                        Delete
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @endforeach

                    @if($teamRankings->isEmpty() && $playerRankings->isEmpty())
                        <tr id="no-rankings-row">
                            <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8; font-weight: 600;">
                                No rankings added yet. Click "+ Add Ranking" above to add your first record!
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <!-- 10-item Pagination Container -->
        <div id="ranking-table-pagination"></div>
    </div>

</div>

<!-- JavaScript for Live Filter, Search, and Auto Slugify -->
<script>
function toggleRankingForm() {
    const container = document.getElementById('ranking-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
    }
}

function switchRankingFormType(type) {
    const teamForm = document.getElementById('form-team-standing');
    const playerForm = document.getElementById('form-player-ranking');
    const typeInput = document.getElementById('player_ranking_type_input');
    const statLabel = document.getElementById('stat_value_label');

    if (type === 'team') {
        teamForm.style.display = 'block';
        playerForm.style.display = 'none';
    } else {
        teamForm.style.display = 'none';
        playerForm.style.display = 'block';
        if (typeInput) typeInput.value = type;
        if (statLabel) {
            statLabel.innerHTML = (type === 'batting') ? 'Total Runs <span style="color:#ef4444;">*</span>' : 'Total Wickets <span style="color:#ef4444;">*</span>';
        }
    }
}

function autoSlugify(text, targetId) {
    const slugInput = document.getElementById(targetId);
    if (slugInput) {
        slugInput.value = text.toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
}

// Initialize Table Manager for Rankings
let rankingTableManager;
document.addEventListener('DOMContentLoaded', () => {
    rankingTableManager = new AdminTableManager({
        tableId: 'ranking-table',
        rowSelector: '.tbl-ranking-row',
        searchInputId: 'ranking-search-input',
        filterSelectId: 'filter-ranking-type',
        filterDataAttr: 'type',
        paginationContainerId: 'ranking-table-pagination',
        perPage: 10,
        colSpan: 9,
        noResultsMsg: 'No matching rankings found.'
    });
});

function filterRankingTable() {
    if (rankingTableManager) rankingTableManager.applyFilter(1);
}

function resetRankingSearch() {
    if (rankingTableManager) rankingTableManager.reset();
}
</script>
@endsection
