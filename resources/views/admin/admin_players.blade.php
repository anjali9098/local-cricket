@extends('layouts.admin')

@section('content')
<div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Top Action Toolbar -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <!-- Left buttons & filters -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Home
            </a>

            <!-- Server Search & Filter Form -->
            <form id="player-filter-form" method="GET" action="{{ route('admin.players') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0;">
                @if(request('edit'))
                    <input type="hidden" name="edit" value="{{ request('edit') }}">
                @endif

                <!-- Role Filter -->
                <select name="role" id="filter-player-role" onchange="this.form.submit()" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: #1e293b; background: white; outline: none; min-width: 140px; cursor: pointer;">
                    <option value="">All Roles</option>
                    <option value="batsman" {{ strtolower($roleFilter ?? '') === 'batsman' ? 'selected' : '' }}>Batsman</option>
                    <option value="bowler" {{ strtolower($roleFilter ?? '') === 'bowler' ? 'selected' : '' }}>Bowler</option>
                    <option value="all-rounder" {{ strtolower($roleFilter ?? '') === 'all-rounder' ? 'selected' : '' }}>All-Rounder</option>
                    <option value="wk-batsman" {{ strtolower($roleFilter ?? '') === 'wk-batsman' ? 'selected' : '' }}>WK-Batsman</option>
                    <option value="wicket-keeper" {{ strtolower($roleFilter ?? '') === 'wicket-keeper' ? 'selected' : '' }}>Wicketkeeper</option>
                </select>

                <!-- Search input & buttons -->
                <div style="display: flex; align-items: center; gap: 6px; position: relative;">
                    <input type="text" name="search" id="player-search-input" value="{{ $search ?? '' }}" placeholder="Search by name, team, country..." style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; width: 240px;">
                    @if(!empty($search))
                        <button type="button" onclick="clearPlayerSearch()" style="position: absolute; right: 140px; background: none; border: none; font-size: 1.1rem; color: #94a3b8; cursor: pointer; padding: 0 4px; line-height: 1;" title="Clear search">&times;</button>
                    @endif
                    <button type="submit" style="padding: 6px 14px; border: 1px solid #0284c7; border-radius: 4px; background: #0284c7; color: white; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: background 0.15s;">
                        Search
                    </button>
                    <a href="{{ route('admin.players') }}" style="display: inline-flex; align-items: center; padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 0.85rem; text-decoration: none; cursor: pointer;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Right: + Add New Button -->
        <div>
            <button type="button" onclick="togglePlayerForm()" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; color: #0f172a; font-weight: 800; font-size: 0.88rem; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="font-size: 1.1rem; line-height: 1; color: #0284c7;">+</span> Add New Player
            </button>
        </div>
    </div>

    <!-- Add / Edit Player Form Panel -->
    <div id="player-form-container" style="display: {{ $editItem ? 'block' : 'none' }}; background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); animation: fadeIn 0.3s ease;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                {{ $editItem ? '✏️ Edit Player: ' . $editItem->name : '👤 Add New Cricket Player' }}
            </h3>
            <button type="button" onclick="togglePlayerForm()" style="background: transparent; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 4px;" title="Close Form">&times;</button>
        </div>

        <form method="POST" action="{{ $editItem ? route('admin.players.update', $editItem->id) : route('admin.players.post') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            @csrf

            <!-- ROW 1: Player Name, Nickname | Team | Role | Display Order -->
            <div style="display: grid; grid-template-columns: 2fr 1.3fr 1.2fr 0.8fr; gap: 16px; align-items: flex-start;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Player Full Name <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="player_name" name="name" value="{{ old('name', $editItem->name ?? '') }}" required placeholder="" onkeyup="autoSlugify(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box; margin-bottom: 8px;">
                    
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 0.78rem; color: #475569;">
                            Nickname
                        </label>
                        <input type="text" name="nickname" value="{{ old('nickname', $editItem->nickname ?? '') }}" placeholder="" style="width: 100%; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>

                    <input type="hidden" id="player_slug" name="slug" value="{{ old('slug', $editItem->slug ?? '') }}">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Current Team
                    </label>
                    <select name="team_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                        <option value="">-- No Team / Free Agent --</option>
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}" {{ (old('team_id', $editItem->team_id ?? null) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Player Role
                    </label>
                    @php $curRole = old('role', $editItem->role ?? 'Batsman'); @endphp
                    <select name="role" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                        @foreach(['Batsman', 'Bowler', 'All-Rounder', 'Wicket-Keeper', 'WK-Batsman'] as $role)
                            <option value="{{ $role }}" {{ strcasecmp($curRole, $role) === 0 ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Display Order
                    </label>
                    <input type="number" name="display_order" value="{{ old('display_order', $editItem->display_order ?? '') }}" min="1" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 2: Physical & Personal Specs: Birthplace | Height | DOB | Nationality | Jersey # -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.2fr 1fr 0.8fr; gap: 14px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        📍 Birthplace
                    </label>
                    <input type="text" name="birthplace" value="{{ old('birthplace', $editItem->birthplace ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        📏 Height
                    </label>
                    <input type="text" name="height" value="{{ old('height', $editItem->height ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        🎂 Date of Birth / DOB
                    </label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $editItem->date_of_birth ?? '') }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Country / Nationality
                    </label>
                    <input type="text" name="country" value="{{ old('country', $editItem->country ?? ($editItem->nationality ?? 'India')) }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Jersey #
                    </label>
                    <input type="text" name="jersey_number" value="{{ old('jersey_number', $editItem->jersey_number ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 3: Batting & Bowling Style & Played Teams -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 14px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Batting Style
                    </label>
                    <select name="batting_style" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                        <option value="">-- Select Batting Style --</option>
                        <option value="Right-Hand Bat" {{ (old('batting_style', $editItem->batting_style ?? '') === 'Right-Hand Bat') ? 'selected' : '' }}>Right-Hand Bat</option>
                        <option value="Left-Hand Bat" {{ (old('batting_style', $editItem->batting_style ?? '') === 'Left-Hand Bat') ? 'selected' : '' }}>Left-Hand Bat</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Bowling Style
                    </label>
                    <input type="text" name="bowling_style" value="{{ old('bowling_style', $editItem->bowling_style ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        🏏 Played for Teams (Comma-separated)
                    </label>
                    <input type="text" name="played_teams" value="{{ old('played_teams', $editItem->played_teams ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 4: Family Details Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="font-size: 0.88rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <span>👨‍👩‍👧‍👦</span> Family Details
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.78rem; color: #475569;">
                            Father's Name
                        </label>
                        <input type="text" name="father_name" value="{{ old('father_name', $editItem->father_name ?? '') }}" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.78rem; color: #475569;">
                            Mother's Name
                        </label>
                        <input type="text" name="mother_name" value="{{ old('mother_name', $editItem->mother_name ?? '') }}" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.78rem; color: #475569;">
                            Spouse / Wife
                        </label>
                        <input type="text" name="spouse_name" value="{{ old('spouse_name', $editItem->spouse_name ?? '') }}" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.78rem; color: #475569;">
                            Children
                        </label>
                        <input type="text" name="children" value="{{ old('children', $editItem->children ?? '') }}" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 700; font-size: 0.78rem; color: #475569;">
                            Siblings (Brother/Sister)
                        </label>
                        <input type="text" name="siblings" value="{{ old('siblings', $editItem->siblings ?? '') }}" placeholder="" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; color: #0f172a; outline: none; box-sizing: border-box; background: white;">
                    </div>
                </div>
            </div>

            <!-- ROW 5: Full In-depth Biography -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                    📖 Profile / Detailed Biography Narrative
                    <span style="font-weight: 500; font-size: 0.75rem; color: #64748b;">(Full player overview, debut story, records and paragraphs)</span>
                </label>
                <textarea name="bio" rows="4" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; color: #0f172a; outline: none; box-sizing: border-box; font-family: inherit; line-height: 1.5;">{{ old('bio', $editItem->bio ?? '') }}</textarea>
            </div>

            <!-- ROW 6: Short Bio / Keywords -->
            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Short Tagline / Catchphrase
                    </label>
                    <input type="text" name="description" value="{{ old('description', $editItem->description ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                        Article Keywords &amp; Tags <span style="font-weight: 500; font-size: 0.75rem; color: #64748b;">(for matching articles)</span>
                    </label>
                    <input type="text" name="keywords" value="{{ old('keywords', $editItem->keywords ?? '') }}" placeholder="" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.88rem; color: #0f172a; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <!-- ROW 4: Photo / Poster | Popular toggle | SUBMIT -->
            <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                
                <!-- Poster Image -->
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                            Player Photo / Poster
                        </label>
                        <span id="player-poster-badge" style="display: none;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="file" name="poster_file" accept="image/*" onchange="previewAndConvertImage(this, 'player_profile_image_input', 'player-poster-preview', 'player-poster-badge')" style="font-size: 0.82rem; color: #475569;">
                        <input type="text" id="player_profile_image_input" name="profile_image" value="{{ old('profile_image', $editItem->profile_image ?? '') }}" oninput="previewUrlImage(this, 'player-poster-preview')" placeholder="Image URL or auto-filled from upload" style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem;">
                        <img id="player-poster-preview" src="{{ old('profile_image', $editItem->profile_image ?? '') }}" alt="Photo" style="height: 38px; width: 38px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; display: {{ !empty(old('profile_image', $editItem->profile_image ?? '')) ? 'block' : 'none' }};" onerror="this.style.display='none';">
                    </div>
                </div>

                <!-- Popular & SUBMIT Button -->
                <div style="display: flex; align-items: center; gap: 16px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.88rem; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $editItem->is_popular ?? false) ? 'checked' : '' }} style="width: 15px; height: 15px; accent-color: #0284c7;">
                        Featured / Popular Player
                    </label>

                    <button type="submit" style="background: #0284c7; color: white; font-weight: 800; padding: 9px 28px; border-radius: 4px; border: none; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                        SUBMIT
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Existing Players List Table (Matching Exact Series Style) -->
    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        @if($players->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="player-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; color: #0284c7; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 10px 12px; width: 65px;"># EDIT</th>
                            <th style="padding: 10px 12px; width: 65px;">ORDER</th>
                            <th style="padding: 10px 12px; width: 90px;">POSTER</th>
                            <th style="padding: 10px 14px; min-width: 220px;">NAME</th>
                            <th style="padding: 10px 14px; min-width: 220px;">PAGE LINK / TEAM</th>
                            <th style="padding: 10px 12px;">ROLE &amp; COUNTRY</th>
                            <th style="padding: 10px 14px; min-width: 180px;">ADD/UPDATE</th>
                            <th style="padding: 10px 12px; text-align: right; width: 80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($players as $item)
                            <tr class="tbl-player-row" data-role="{{ strtolower($item->role ?? '') }}" data-team="{{ strtolower($item->team->name ?? '') }}" data-country="{{ strtolower($item->country ?? '') }}" data-name="{{ strtolower($item->name ?? '') }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
                                
                                <!-- # EDIT -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <a href="{{ route('admin.players', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                                             {{ $item->id }}
                                        </a>
                                        <a href="{{ route('admin.players', ['edit' => $item->id]) }}" title="Edit Player" style="color: #0284c7; text-decoration: none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                        Jersey: <strong>#{{ $item->jersey_number ?: '-' }}</strong>
                                    </div>
                                </td>

                                <!-- ORDER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    <input type="number" value="{{ $item->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
                                </td>

                                <!-- POSTER -->
                                <td style="padding: 12px 10px; vertical-align: middle;">
                                    @if(!empty($item->profile_image))
                                        <img src="{{ $item->profile_image }}" alt="Photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; display: block;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                                        <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; border: 1px solid #cbd5e1; display: none; align-items: center; justify-content: center; font-size: 0.8rem; color: #0284c7; font-weight: 800;">
                                            {{ $item->initials ?: substr($item->name, 0, 2) }}
                                        </div>
                                    @else
                                        <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #0284c7; font-weight: 800;">
                                            {{ $item->initials ?: substr($item->name, 0, 2) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- NAME -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px; display: flex; align-items: center; gap: 6px;">
                                        {{ $item->name }}
                                        @if($item->is_popular)
                                            <span style="font-size: 0.68rem; background: #fef3c7; color: #b45309; font-weight: 800; padding: 2px 6px; border-radius: 4px;">★ POPULAR</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                                        {{ $item->batting_style ?: 'Batting' }} &bull; {{ $item->bowling_style ?: 'Bowling' }}
                                    </div>
                                </td>

                                <!-- PAGE LINK / TEAM -->
                                <td style="padding: 12px 14px; vertical-align: middle;">
                                    <div style="margin-bottom: 2px;">
                                        <a href="{{ route('players') }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                                            {{ $item->slug ?: \Illuminate\Support\Str::slug($item->name) }}
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                                        Team: <strong>{{ $item->team ? $item->team->name : 'No Team' }}</strong>
                                    </div>
                                </td>

                                <!-- ROLE, DOB & COUNTRY -->
                                <td style="padding: 12px 12px; vertical-align: middle; font-weight: 700; color: #1e293b;">
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                            {{ $item->role }}
                                        </span>
                                        @if($item->date_of_birth)
                                            <span style="background: #fdf2f8; color: #db2777; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                                🎂 {{ date('d M Y', strtotime($item->date_of_birth)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                                        {{ $item->country ?: ($item->nationality ?: 'India') }}
                                    </div>
                                </td>

                                <!-- ADD/UPDATE -->
                                <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
                                    <div>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                    <div>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
                                </td>

                                <!-- ACTION -->
                                <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
                                    <form method="POST" action="{{ route('admin.players.delete', $item->id) }}" onsubmit="return confirm('Delete player \'{{ addslashes($item->name) }}\'?');" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; font-weight: 700; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- 10-item Pagination Container -->
            <div id="player-table-pagination" style="padding: 14px 18px; background: white; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 0.85rem; color: #475569;">
                    Showing <strong>{{ $players->firstItem() ?? 0 }}</strong> to <strong>{{ $players->lastItem() ?? 0 }}</strong> of <strong>{{ $players->total() }}</strong> players
                    @if(!empty($search) || !empty($roleFilter))
                        <span style="color: #0284c7; font-weight: 700; margin-left: 4px;">(filtered)</span>
                    @endif
                </div>
                <div class="player-pagination-links">
                    {{ $players->links() }}
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 48px; color: #94a3b8; font-weight: 600;">
                @if(!empty($search) || !empty($roleFilter))
                    <div style="font-size: 1.1rem; color: #1e293b; font-weight: 700; margin-bottom: 6px;">No matching players found</div>
                    <div style="font-size: 0.85rem; margin-bottom: 14px;">No players match your search criteria.</div>
                    <a href="{{ route('admin.players') }}" style="display: inline-block; padding: 7px 18px; background: #0284c7; color: white; border-radius: 4px; font-weight: 700; text-decoration: none; font-size: 0.85rem;">
                        Clear Search &amp; Filters
                    </a>
                @else
                    No players available yet. Click <strong>+ Add New Player</strong> above to add one!
                @endif
            </div>
        @endif
    </div>

</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.player-pagination-links nav svg {
    width: 16px !important;
    height: 16px !important;
    display: inline-block !important;
}
.player-pagination-links nav p {
    margin: 0 !important;
    font-size: 0.82rem !important;
    color: #64748b !important;
}
.player-pagination-links nav span[aria-current="page"] span {
    background-color: #0284c7 !important;
    border-color: #0284c7 !important;
    color: white !important;
}
</style>

<script>
function togglePlayerForm() {
    const container = document.getElementById('player-form-container');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        const nameInput = document.getElementById('player_name');
        if (nameInput) {
            nameInput.focus();
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        container.style.display = 'none';
    }
}

function autoSlugify(text) {
    const slugInput = document.getElementById('player_slug');
    if (slugInput && (!slugInput.dataset.manual || slugInput.value === '')) {
        slugInput.value = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
    }
}

document.getElementById('player_slug')?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});

function clearPlayerSearch() {
    const input = document.getElementById('player-search-input');
    if (input) input.value = '';
    const form = document.getElementById('player-filter-form');
    if (form) form.submit();
}
</script>
@endsection
