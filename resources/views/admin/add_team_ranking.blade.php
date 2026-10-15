@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar matching Image 3 -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>

            <!-- Type Filter Dropdown -->
            <select id="filter-ranking-type" onchange="filterRankingTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 150px;">
                <option value="">All Rankings</option>
                <option value="team">Team Standings</option>
                <option value="batting">Batting Rankings</option>
                <option value="bowling">Bowling Rankings</option>
                <option value="all_rounder">All-Rounder Rankings</option>
            </select>

            <!-- Format Filter Dropdown -->
            <select id="filter-ranking-format" onchange="filterRankingTable()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 110px;">
                <option value="">All Formats</option>
                <option value="TEST">TEST</option>
                <option value="ODI">ODI</option>
                <option value="T20">T20</option>
            </select>

            <!-- Search input & buttons -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="admin-search-wrapper">
                    <input type="text" id="ranking-search-input" class="admin-search-input" oninput="filterRankingTable()" onkeyup="filterRankingTable()" onkeydown="if(event.key==='Enter'){event.preventDefault(); filterRankingTable();}" style="width: 200px;">
                    <button type="button" class="admin-search-clear-btn" title="Clear search">&times;</button>
                </div>
                <button type="button" onclick="filterRankingTable()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Search
                </button>
                <button type="button" onclick="resetRankingSearch()" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                    Reset
                </button>
            </div>
        </div>

        <!-- Right Action Buttons -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" onclick="toggleApiModal()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #0284c7; font-weight: 800; font-size: 0.82rem; cursor: pointer;">
                🔄 Sync ICC API
            </button>
            <button type="button" onclick="toggleRankingForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add Ranking
            </button>
        </div>
    </div>

    <!-- API SYNC MODAL -->
    <div id="api-sync-modal" style="display: none; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 20px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a;">
                🌐 Fetch & Synchronize ICC Rankings API
            </h4>
            <button type="button" onclick="toggleApiModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <p style="margin: 0 0 14px 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
            You can enter your custom ICC rankings API endpoint below, or click <strong>Refresh Official ICC Baseline</strong> to reload authentic multi-format data.
        </p>
        <form method="POST" action="{{ route('admin.ranking.sync-api') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
            @csrf
            <input type="url" name="api_url" placeholder="https://api.example.com/v1/icc-rankings (Optional)" style="flex: 1; min-width: 260px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem;">
            <button type="submit" style="background: #0284c7; color: white; border: none; padding: 8px 20px; border-radius: 4px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                Fetch & Save API Data
            </button>
        </form>
    </div>

    <!-- Add / Edit Ranking Form Panel -->
    @php
        $isEditing = $editTeam || $editPlayer;
        $activeFormType = $editPlayer ? ($editPlayer->category ?? 'batting') : 'team';
    @endphp
    <div id="ranking-form-container" style="display: {{ $isEditing ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                @if($editTeam)
                    ✏️ Edit Team Standing: {{ $editTeam->team_name }} ({{ $editTeam->format }})
                @elseif($editPlayer)
                    ✏️ Edit Player Ranking: {{ $editPlayer->player_name }} ({{ strtoupper($editPlayer->category) }} &bull; {{ $editPlayer->format }})
                @else
                    🏆 Add Team / Player Ranking
                @endif
            </h3>
            <button type="button" onclick="{{ $isEditing ? "window.location.href='" . route('admin.ranking') . "'" : "toggleRankingForm()" }}" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <!-- TYPE SELECTOR -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 18px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 800; font-size: 0.85rem; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;">
                SELECT RANKING CATEGORY <span style="color:#ef4444;">*</span>
            </label>
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.88rem;">
                    <input type="radio" name="ranking_category_switch" value="team" onchange="switchRankingFormType('team')" {{ $activeFormType === 'team' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🛡️ TEAM STANDINGS</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.88rem;">
                    <input type="radio" name="ranking_category_switch" value="batting" onchange="switchRankingFormType('batting')" {{ $activeFormType === 'batting' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🏏 BATTING RANKINGS</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.88rem;">
                    <input type="radio" name="ranking_category_switch" value="bowling" onchange="switchRankingFormType('bowling')" {{ $activeFormType === 'bowling' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>🎯 BOWLING RANKINGS</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; cursor: pointer; font-size: 0.88rem;">
                    <input type="radio" name="ranking_category_switch" value="all_rounder" onchange="switchRankingFormType('all_rounder')" {{ $activeFormType === 'all_rounder' ? 'checked' : '' }} style="accent-color: #0284c7; width: 17px; height: 17px;">
                    <span>⚡ ALL-ROUNDER RANKINGS</span>
                </label>
            </div>
        </div>

        <!-- FORM 1: TEAM STANDING -->
        <div id="form-team-standing" style="display: {{ $activeFormType === 'team' ? 'block' : 'none' }};">
            <form method="POST" action="{{ $editTeam ? route('admin.ranking.update', $editTeam->id) : route('admin.ranking.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf

                <!-- ROW 1: Team Name | Format | Gender | Rank # -->
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 16px; align-items: flex-start;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Team Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="team_ranking_name" name="team_name" value="{{ old('team_name', $editTeam->team_name ?? '') }}" required onkeyup="autoSlugify(this.value, 'team_ranking_slug')" placeholder="e.g. Australia" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                        <input type="text" id="team_ranking_slug" name="slug" value="{{ old('slug', $editTeam->slug ?? '') }}" placeholder="slug" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Format <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="format" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; font-weight: 700; color: #0f172a; background: white; outline: none;">
                            <option value="TEST" {{ old('format', $editTeam->format ?? '') === 'TEST' ? 'selected' : '' }}>TEST</option>
                            <option value="ODI" {{ old('format', $editTeam->format ?? '') === 'ODI' ? 'selected' : '' }}>ODI</option>
                            <option value="T20" {{ old('format', $editTeam->format ?? '') === 'T20' ? 'selected' : '' }}>T20</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Gender <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="gender" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; font-weight: 700; color: #0f172a; background: white; outline: none;">
                            <option value="men" {{ old('gender', $editTeam->gender ?? '') === 'men' ? 'selected' : '' }}>Men's</option>
                            <option value="women" {{ old('gender', $editTeam->gender ?? '') === 'women' ? 'selected' : '' }}>Women's</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rank # <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rank_num" min="1" value="{{ old('rank_num', $editTeam->rank_num ?? '') }}" required placeholder="1" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 2: Matches | Won | Rating | Points -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Matches Played
                        </label>
                        <input type="number" name="matches_played" min="0" value="{{ old('matches_played', $editTeam->matches_played ?? '') }}" placeholder="27" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Won
                        </label>
                        <input type="number" name="won" min="0" value="{{ old('won', $editTeam->won ?? '') }}" placeholder="18" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rating <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rating" min="0" value="{{ old('rating', $editTeam->rating ?? '') }}" required placeholder="126" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Points (PTS) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="points" min="0" value="{{ old('points', $editTeam->points ?? '') }}" required placeholder="3411" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 3: Logo / Flag & Submit -->
                <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                    <div style="flex: 1; min-width: 280px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Team Flag / Logo (File or URL)
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="file" name="poster_file" accept="image/*" onchange="previewAndConvertImage(this, 'ranking_team_logo_input', 'ranking-team-logo-preview')" style="font-size: 0.82rem; color: #475569;">
                            <input type="text" id="ranking_team_logo_input" name="logo_url" value="{{ old('logo_url', $editTeam->logo_url ?? '') }}" placeholder="https://flagcdn.com/w80/au.png" oninput="previewUrlImage(this, 'ranking-team-logo-preview')" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                            <img id="ranking-team-logo-preview" src="{{ old('logo_url', $editTeam->logo_url ?? '') }}" alt="Logo" style="height: 34px; border-radius: 4px; border: 1px solid #cbd5e1; display: {{ !empty(old('logo_url', $editTeam->logo_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                        </div>
                    </div>

                    <div>
                        <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                            {{ $editTeam ? 'UPDATE TEAM RANKING' : 'SAVE TEAM RANKING' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- FORM 2: PLAYER RANKINGS (Batting / Bowling / All-Rounder) -->
        <div id="form-player-ranking" style="display: {{ in_array($activeFormType, ['batting', 'bowling', 'all_rounder']) ? 'block' : 'none' }};">
            <form method="POST" action="{{ $editPlayer ? route('admin.player-ranking.update', $editPlayer->id) : route('admin.player-ranking.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf
                <input type="hidden" id="player_ranking_type_input" name="type" value="{{ old('type', $editPlayer->category ?? 'batting') }}">

                <!-- ROW 1: Player Name | Format | Gender | Rank # -->
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 16px; align-items: flex-start;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Player Name <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="player_ranking_name" name="player_name" value="{{ old('player_name', $editPlayer->player_name ?? '') }}" required onkeyup="autoSlugify(this.value, 'player_ranking_slug')" placeholder="e.g. Virat Kohli" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                        <input type="text" id="player_ranking_slug" name="slug" value="{{ old('slug', $editPlayer->slug ?? '') }}" placeholder="slug" style="width: 100%; padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #64748b; outline: none; box-sizing: border-box; background: #fafafa;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Format <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="format" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; font-weight: 700; color: #0f172a; background: white; outline: none;">
                            <option value="TEST" {{ old('format', $editPlayer->format ?? '') === 'TEST' ? 'selected' : '' }}>TEST</option>
                            <option value="ODI" {{ old('format', $editPlayer->format ?? '') === 'ODI' ? 'selected' : '' }}>ODI</option>
                            <option value="T20" {{ old('format', $editPlayer->format ?? '') === 'T20' ? 'selected' : '' }}>T20</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Gender <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="gender" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; font-weight: 700; color: #0f172a; background: white; outline: none;">
                            <option value="men" {{ old('gender', $editPlayer->gender ?? '') === 'men' ? 'selected' : '' }}>Men's</option>
                            <option value="women" {{ old('gender', $editPlayer->gender ?? '') === 'women' ? 'selected' : '' }}>Women's</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rank # <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rank_num" min="1" value="{{ old('rank_num', $editPlayer->current_rank ?? '') }}" required placeholder="1" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 2: Country Code | Rating | Career Best | Matches -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Team / Country (e.g. IND) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="badge_text" maxlength="6" value="{{ old('badge_text', $editPlayer->country ?? 'IND') }}" required placeholder="IND" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; text-transform: uppercase;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Rating <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" name="rating" min="0" value="{{ old('rating', $editPlayer->rating ?? '') }}" required placeholder="893" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Best Rank / Rating
                        </label>
                        <input type="text" name="best_rank" value="{{ old('best_rank', $editPlayer->best_rank ?? '') }}" placeholder="e.g. 1 or 920" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Matches Played
                        </label>
                        <input type="number" name="matches" min="0" value="{{ old('matches', $editPlayer->matches ?? '') }}" placeholder="45" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <!-- ROW 3: Player Photo / Cutout & Submit -->
                <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                    <div style="flex: 1; min-width: 280px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Player Photo / Cutout Image (File or URL)
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="file" name="poster_file" accept="image/*" onchange="previewAndConvertImage(this, 'ranking_player_photo_input', 'ranking-player-photo-preview')" style="font-size: 0.82rem; color: #475569;">
                            <input type="text" id="ranking_player_photo_input" name="photo_url" value="{{ old('photo_url', $editPlayer->photo_url ?? '') }}" placeholder="https://..." oninput="previewUrlImage(this, 'ranking-player-photo-preview')" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                            <img id="ranking-player-photo-preview" src="{{ old('photo_url', $editPlayer->photo_url ?? '') }}" alt="Photo" style="height: 38px; width: 38px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; display: {{ !empty(old('photo_url', $editPlayer->photo_url ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                        </div>
                    </div>

                    <div>
                        <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                            {{ $editPlayer ? 'UPDATE PLAYER RANKING' : 'SAVE PLAYER RANKING' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- Existing Rankings List Table (Matching Exact Image 3 Style) -->
    <div id="ranking-table-container" style="display: {{ $isEditing ? 'none' : 'block' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        <div style="overflow-x: auto;">
            <table id="ranking-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                        <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                        <th style="padding: 10px 14px; min-width: 220px;">NAME</th>
                        <th style="padding: 10px 12px; width: 150px;">TYPE</th>
                        <th style="padding: 10px 14px; min-width: 180px;">PTS / STATS</th>
                        <th style="padding: 10px 14px; min-width: 150px;">PAGE LINK</th>
                        <th style="padding: 10px 14px; min-width: 150px;">ADD/UPDATE</th>
                        <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Team Standings Rows -->
                    @foreach($teamRankings as $tr)
                        <tr class="tbl-ranking-row" data-type="team" data-format="{{ $tr->format }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                            
                            <!-- # EDIT -->
                            <td style="padding: 12px 10px; vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <a href="{{ route('admin.ranking', ['edit_team' => $tr->slug . '-' . $tr->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        #{{ $loop->iteration }}
                                    </a>
                                    <a href="{{ route('admin.ranking', ['edit_team' => $tr->slug . '-' . $tr->id]) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;" title="Edit Standing">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                </div>
                            </td>

                            <!-- POSTER / LOGO -->
                            <td style="padding: 10px 12px; vertical-align: middle;">
                                <img src="{{ $tr->logo_url }}" alt="{{ $tr->team_name }}" style="width: 44px; height: 32px; object-fit: contain; border-radius: 4px; border: 1px solid #e2e8f0; background: #fafafa; display: block;" onerror="this.onerror=null; this.src='https://flagcdn.com/w80/in.png';">
                            </td>

                            <!-- NAME -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                    {{ $tr->team_name }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    Rank #{{ $tr->rank_num }} &bull; {{ $tr->format }} ({{ strtoupper($tr->gender ?? 'MEN') }})
                                </div>
                            </td>

                            <!-- TYPE -->
                            <td style="padding: 12px 12px; vertical-align: middle;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em; white-space: nowrap;">
                                    {{ $tr->format }} TEAM
                                </span>
                            </td>

                            <!-- PTS / STATS -->
                            <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.85rem; color: #1e293b;">
                                <strong style="color: #0f172a;">{{ $tr->rating ?: $tr->points }} Rating</strong> 
                                <span style="color: #64748b; font-size: 0.8rem;">({{ $tr->points }} PTS &bull; P:{{ $tr->matches_played }} | W:{{ $tr->won }})</span>
                            </td>

                            <!-- PAGE LINK -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <a href="{{ route('rankings', ['category' => 'teams', 'format' => $tr->format, 'gender' => $tr->gender ?? 'men']) }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;">
                                    /rankings
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </td>

                            <!-- ADD/UPDATE -->
                            <td style="padding: 12px 14px; vertical-align: middle; color: #475569; font-size: 0.8rem;">
                                {{ $tr->updated_at ? \Carbon\Carbon::parse($tr->updated_at)->format('d M Y, h:i A') : 'Active' }}
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

                    <!-- Player Rankings Rows (Batting / Bowling / All-Rounder) -->
                    @foreach($playerRankings as $pr)
                        <tr class="tbl-ranking-row" data-type="{{ strtolower($pr->category) }}" data-format="{{ $pr->format }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                            
                            <!-- # EDIT -->
                            <td style="padding: 12px 10px; vertical-align: middle;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <a href="{{ route('admin.ranking', ['edit_player' => ($pr->slug ?: $pr->id) . '-' . $pr->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                        #{{ $loop->iteration + $teamRankings->count() }}
                                    </a>
                                    <a href="{{ route('admin.ranking', ['edit_player' => ($pr->slug ?: $pr->id) . '-' . $pr->id]) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;" title="Edit Player Ranking">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                </div>
                            </td>

                            <!-- POSTER / LOGO -->
                            <td style="padding: 10px 12px; vertical-align: middle;">
                                <div style="position: relative; width: 40px; height: 40px;">
                                    <img src="{{ $pr->photo_url }}" alt="{{ $pr->player_name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1.5px solid #cbd5e1; display: block;" onerror="this.onerror=null; this.src='{{ asset('images/default-player-boy.svg') }}';">
                                    <img src="{{ $pr->country_flag }}" alt="{{ $pr->country }}" style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 10px; object-fit: cover; border-radius: 1px;" onerror="this.style.display='none';">
                                </div>
                            </td>

                            <!-- NAME -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                    {{ $pr->player_name }}
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                    Rank #{{ $pr->current_rank }} &bull; {{ $pr->country }} &bull; Best: #{{ $pr->best_rank ?? '--' }}
                                </div>
                            </td>

                            <!-- TYPE -->
                            <td style="padding: 12px 12px; vertical-align: middle;">
                                @if(strtolower($pr->category) === 'batting')
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em; white-space: nowrap;">
                                        🏏 {{ $pr->format }} BATTING
                                    </span>
                                @elseif(strtolower($pr->category) === 'bowling')
                                    <span style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em; white-space: nowrap;">
                                        🎯 {{ $pr->format }} BOWLING
                                    </span>
                                @else
                                    <span style="background: #f3e8ff; color: #7e22ce; padding: 3px 8px; border-radius: 4px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em; white-space: nowrap;">
                                        ⚡ {{ $pr->format }} ALL-ROUNDER
                                    </span>
                                @endif
                            </td>

                            <!-- PTS / STATS -->
                            <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.88rem; color: #1e293b;">
                                <strong style="color: #0f172a;">{{ $pr->rating }} Rating</strong>
                                <span style="color: #64748b; font-size: 0.8rem;">(Matches: {{ $pr->matches ?: '--' }})</span>
                            </td>

                            <!-- PAGE LINK -->
                            <td style="padding: 12px 14px; vertical-align: middle;">
                                <a href="{{ route('rankings', ['category' => $pr->category, 'format' => $pr->format, 'gender' => $pr->gender ?? 'men']) }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;">
                                    /rankings
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </td>

                            <!-- ADD/UPDATE -->
                            <td style="padding: 12px 14px; vertical-align: middle; color: #475569; font-size: 0.8rem;">
                                {{ $pr->updated_at ? \Carbon\Carbon::parse($pr->updated_at)->format('d M Y, h:i A') : 'Active' }}
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
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8; font-weight: 600;">
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
    const tableContainer = document.getElementById('ranking-table-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        if (tableContainer) tableContainer.style.display = 'none';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        container.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'block';
    }
}

function toggleApiModal() {
    const modal = document.getElementById('api-sync-modal');
    modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'block' : 'none';
}

function switchRankingFormType(type) {
    const teamForm = document.getElementById('form-team-standing');
    const playerForm = document.getElementById('form-player-ranking');
    const typeInput = document.getElementById('player_ranking_type_input');

    if (type === 'team') {
        teamForm.style.display = 'block';
        playerForm.style.display = 'none';
    } else {
        teamForm.style.display = 'none';
        playerForm.style.display = 'block';
        if (typeInput) typeInput.value = type;
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

// Custom Client-side Filter for Type and Format
function filterRankingTable() {
    const typeVal = document.getElementById('filter-ranking-type').value.toLowerCase();
    const formatVal = document.getElementById('filter-ranking-format').value.toUpperCase();
    const searchVal = document.getElementById('ranking-search-input').value.toLowerCase().trim();

    const rows = document.querySelectorAll('.tbl-ranking-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowType = (row.getAttribute('data-type') || '').toLowerCase();
        const rowFormat = (row.getAttribute('data-format') || '').toUpperCase();
        const rowText = row.innerText.toLowerCase();

        const matchType = !typeVal || (typeVal === 'team' && rowType === 'team') || (rowType === typeVal);
        const matchFormat = !formatVal || (rowFormat === formatVal);
        const matchSearch = !searchVal || rowText.includes(searchVal);

        if (matchType && matchFormat && matchSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRow = document.getElementById('no-rankings-row');
    if (noRow) {
        noRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function resetRankingSearch() {
    document.getElementById('filter-ranking-type').value = '';
    document.getElementById('filter-ranking-format').value = '';
    document.getElementById('ranking-search-input').value = '';
    filterRankingTable();
}
</script>
@endsection
