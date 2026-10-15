@extends('layouts.app')

@php
    if ($selectedTeam) {
        $pageTitle = $selectedTeam->name . ' Squad & Players List — CricketKaScore';
        $metaDesc = "Explore the full team squad of {$selectedTeam->name} on CricketKaScore. Detailed player profiles, batting & bowling roles, and player stats.";
        $metaKeywords = "{$selectedTeam->name} squad, {$selectedTeam->name} players, {$selectedTeam->name} team, cricket players, CricketKaScore";
        $canonicalUrl = route('players', ['team' => $selectedTeam->id]);
    } else {
        $pageTitle = 'All Cricket Players — Profiles, Records & Stats | CricketKaScore';
        $metaDesc = 'Browse all international, domestic, and local cricket players on CricketKaScore. View detailed career stats, batting & bowling styles, and player profiles.';
        $metaKeywords = 'cricket players, all players list, player profiles, cricket career stats, bowler stats, batsman stats, CricketKaScore';
        $canonicalUrl = route('players');
    }
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_title', $pageTitle)
@section('og_description', $metaDesc)
@section('og_url', $canonicalUrl)

@section('content')
<main class="container" style="margin: 0 auto; padding: 24px 20px 80px; font-family: var(--font-body, 'Inter', sans-serif); max-width: 1300px;">

    <!-- Header -->
    <div style="text-align: center; margin-bottom: 28px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: #38bdf8; text-transform: uppercase; display: block; margin-bottom: 6px;">
            CRICKET PLAYERS DIRECTORY
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 8px 0;">
            @if($selectedTeam)
                🛡️ {{ $selectedTeam->name }} Squad
            @else
                All Cricket Players
            @endif
        </h1>
        <p style="font-size: 0.95rem; color: var(--text-muted); margin: 0 auto; max-width: 600px; line-height: 1.5;">
            @if($selectedTeam)
                Showing registered squad players for <strong>{{ $selectedTeam->name }}</strong> ({{ $allPlayers->count() }} {{ Str::plural('Player', $allPlayers->count()) }}).
                <span style="display: block; margin-top: 6px;">
                    <a href="{{ route('players') }}" style="color: #38bdf8; text-decoration: none; font-weight: 700; font-size: 0.85rem;">&larr; View All Cricket Players</a>
                </span>
            @else
                Explore cricket players, batting &amp; bowling profiles, career stats, and team rosters.
            @endif
        </p>

        @if($selectedTeam)
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-left: 4px solid {{ $selectedTeam->color_code ?? '#38bdf8' }}; border-radius: 8px; padding: 14px 20px; margin: 18px auto 0; max-width: 800px; text-align: left; font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                <strong style="color: var(--text-main); font-weight: 800;">About {{ $selectedTeam->name }}:</strong>
                {{ $selectedTeam->description ?: ($selectedTeam->name . ' is an active ' . strtolower($selectedTeam->team_type ?: 'cricket') . ' team ' . ($selectedTeam->city ? 'from ' . $selectedTeam->city . ', ' : '') . ($selectedTeam->country ?: 'India') . ' competing across official matches and tournaments.') }}
            </div>
        @endif
    </div>

    <!-- Search & Filter Bar -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 18px 20px; margin-bottom: 28px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
        <form method="GET" action="{{ route('players') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; align-items: flex-end;">
            
            <!-- Search by Name / Role -->
            <div style="flex: 2; min-width: 200px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-dim); margin-bottom: 6px;">
                    Search Player
                </label>
                <input type="text" name="search" id="player-search-input" value="{{ $search ?? '' }}" 
                    oninput="filterPlayersLive(this.value)"
                    style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 10px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.9rem; outline: none;">
            </div>

            <!-- Filter by Team Dropdown -->
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-dim); margin-bottom: 6px;">
                    Filter By Team
                </label>
                <select name="team" id="player-team-select" onchange="this.form.submit()" 
                    style="width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 10px; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.9rem; font-weight: 600; outline: none; cursor: pointer;">
                    <option value="">All Teams (Select...)</option>
                    @foreach($teams as $tm)
                        <option value="{{ $tm->id }}" {{ ($teamId ?? request('team')) == $tm->id ? 'selected' : '' }}>
                            🛡️ {{ $tm->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="submit" style="flex: 1; background: #0284c7; color: #ffffff; border: none; border-radius: 10px; padding: 10px 18px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#0369a1';" onmouseout="this.style.background='#0284c7';">
                    Search
                </button>
                @if(!empty($search) || !empty($teamId) || request('team'))
                    <a href="{{ route('players') }}" style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: #f87171; text-decoration: none; border-radius: 10px; padding: 10px 14px; font-weight: 800; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center;" title="Reset Filters">
                        ✕ Clear
                    </a>
                @endif
            </div>

        </form>

        <!-- Showing Players Count Badge -->
        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 0.82rem; color: var(--text-muted);">
            <div>
                Showing <strong id="visible-player-count" style="color: var(--text-main);">{{ $allPlayers->count() }}</strong> {{ $selectedTeam ? 'players in ' . $selectedTeam->name : 'players' }}
            </div>
            @if($selectedTeam)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                        🛡️ {{ $selectedTeam->name }}
                    </span>
                    <a href="{{ route('players') }}" style="color: var(--text-dim); text-decoration: underline; font-size: 0.78rem;">Clear Team Filter</a>
                </div>
            @elseif(!empty($search) || !empty($teamId) || request('team'))
                <span style="color: #38bdf8; font-weight: 700; font-size: 0.78rem;">Filtered results</span>
            @endif
        </div>
    </div>

    <!-- Players Grid -->
    <div id="players-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
        @forelse($allPlayers as $player)
            <div class="player-card"
                data-name="{{ strtolower($player->name) }}"
                data-role="{{ strtolower($player->role ?? '') }}"
                data-team="{{ strtolower($player->team?->name ?? '') }}"
                data-country="{{ strtolower($player->country ?? '') }}"
                style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px 18px 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.06);"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='rgba(56, 189, 248, 0.4)'; this.style.boxShadow='0 12px 28px rgba(0,0,0,0.2)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)';"
            >
                <!-- Card Header & Info -->
                <div style="text-align: center;">
                    
                    <!-- Avatar or Photo -->
                    <a href="{{ $player->url }}" style="display: inline-block; margin-bottom: 12px; text-decoration: none;">
                        <img src="{{ $player->profile_image ?: $player->default_avatar }}" 
                            alt="{{ $player->name }}" 
                            loading="lazy" 
                            style="width: 68px; height: 68px; border-radius: 50%; object-fit: cover; border: 2.5px solid #38bdf8; background: var(--bg-card-secondary); box-shadow: 0 4px 12px rgba(0,0,0,0.25); display: block; margin: 0 auto;" 
                            onerror="this.onerror=null; this.src='{{ $player->default_avatar }}';">
                    </a>

                    <!-- Player Name -->
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <a href="{{ $player->url }}" style="text-decoration: none; color: inherit;">
                            {{ $player->name }}
                        </a>
                    </h3>

                    <!-- Role & Team Badges -->
                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap; margin-bottom: 14px;">
                        <span style="font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);">
                            {{ $player->role ?? 'Player' }}
                        </span>
                        @if($player->team)
                            <span style="font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: var(--bg-card-secondary); color: var(--text-dim); border: 1px solid var(--border-color); max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $player->team->name }}">
                                🛡️ {{ $player->team->short_name ?? $player->team->name }}
                            </span>
                        @elseif($player->country)
                            <span style="font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: var(--bg-card-secondary); color: var(--text-dim); border: 1px solid var(--border-color);">
                                📍 {{ $player->country }}
                            </span>
                        @endif
                    </div>

                    <!-- Batting & Bowling Specs -->
                    <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 12px; font-size: 0.78rem; text-align: left; display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">🏏 Batting:</span>
                            <strong style="color: var(--text-main); font-size: 0.76rem; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $player->batting_style ?? 'Right-hand' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">🎯 Bowling:</span>
                            <strong style="color: var(--text-main); font-size: 0.76rem; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $player->bowling_style ?? 'Right-arm' }}</strong>
                        </div>
                    </div>

                </div>

                <!-- Profile Action Button -->
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                    <a href="{{ $player->url }}" style="display: flex; align-items: center; justify-content: center; width: 100%; box-sizing: border-box; background: #0284c7; color: #ffffff; font-size: 0.82rem; font-weight: 800; padding: 8px 12px; border-radius: 8px; text-decoration: none; transition: background 0.15s, opacity 0.15s;" onmouseover="this.style.background='#0369a1';" onmouseout="this.style.background='#0284c7';">
                        View Profile &rarr;
                    </a>
                </div>

            </div>
        @empty
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 48px 24px; text-align: center; grid-column: 1 / -1; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                <div style="font-size: 3rem; margin-bottom: 12px;">🏏</div>
                <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin: 0 0 8px 0;">No players found</h3>
                <p style="font-size: 0.88rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 18px; line-height: 1.5;">No players match your search filter. Try searching a different keyword or selecting another team.</p>
                <a href="{{ route('players') }}" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-weight: 800; font-size: 0.82rem; padding: 8px 16px; border-radius: 8px; text-decoration: none;">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>

</main>

<script>
    // Live instant search as user types
    function filterPlayersLive(val) {
        val = val.trim().toLowerCase();
        const cards = document.querySelectorAll('.player-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const role = card.getAttribute('data-role') || '';
            const team = card.getAttribute('data-team') || '';
            const country = card.getAttribute('data-country') || '';

            if (val === '' || name.includes(val) || role.includes(val) || team.includes(val) || country.includes(val)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countElem = document.getElementById('visible-player-count');
        if (countElem) {
            countElem.textContent = visibleCount;
        }
    }
</script>
@endsection
