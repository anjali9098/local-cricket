@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 40px 24px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Header -->
    <div style="text-align: center; margin-bottom: 32px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: #38bdf8; text-transform: uppercase; display: block; margin-bottom: 6px;">
            CRICKET DIRECTORY
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 6px 0;">
            All Popular Players
        </h1>
        <p style="font-size: 0.95rem; color: var(--text-muted); margin: 0 auto; max-width: 600px;">
            Explore cricket players, batting &amp; bowling profiles, and head-to-head career stats.
        </p>
    </div>

    <!-- Filter by Team Dropdown -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 20px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main);">
            Showing {{ $allPlayers->count() }} Players
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                Filter Team:
            </label>
            <select onchange="if(this.value){ window.location.href='{{ route('players') }}?team=' + this.value; } else { window.location.href='{{ route('players') }}'; }"
                style="padding: 8px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 700; outline: none; cursor: pointer;">
                <option value="">All Teams</option>
                @foreach($teams as $tm)
                    <option value="{{ $tm->id }}" {{ request('team') == $tm->id ? 'selected' : '' }}>
                        {{ $tm->name }}
                    </option>
                @endforeach
            </select>
            @if(request('team'))
                <a href="{{ route('players') }}" style="text-decoration: none; font-size: 0.82rem; font-weight: 700; color: #f87171; background: rgba(239, 68, 68, 0.1); padding: 7px 12px; border-radius: 6px;">
                    ✕ Clear
                </a>
            @endif
        </div>
    </div>

    <!-- Players Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
        @forelse($allPlayers as $player)
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; text-align: center; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;"
                onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                
                <div>
                    <!-- Avatar or Photo -->
                    <a href="{{ route('player.profile', $player->id) }}" style="text-decoration: none; display: block; margin: 0 auto 12px; width: 64px; height: 64px;">
                        @if(!empty($player->profile_image))
                            <img src="{{ $player->profile_image }}" alt="{{ $player->name }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #38bdf8; display: block;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: none; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900; color: #38bdf8;">
                                {{ strtoupper(substr($player->name, 0, 2)) }}
                            </div>
                        @else
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 900; color: #38bdf8;">
                                {{ strtoupper(substr($player->name, 0, 2)) }}
                            </div>
                        @endif
                    </a>

                    <h3 style="font-size: 1.08rem; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">
                        <a href="{{ route('player.profile', $player->id) }}" style="text-decoration: none; color: inherit;">
                            {{ $player->name }}
                        </a>
                    </h3>

                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; margin-bottom: 12px;">
                        <span style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                            {{ $player->role ?? 'Player' }}
                        </span>
                        @if($player->team)
                            <span style="background: var(--bg-card-secondary); color: var(--text-dim); font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 4px;">
                                {{ $player->team->short_name ?? $player->team->name }}
                            </span>
                        @endif
                    </div>

                    <div style="background: var(--bg-card-secondary); border-radius: 8px; padding: 8px 10px; font-size: 0.76rem; color: var(--text-dim); text-align: left; display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px;">
                        <div>🏏 <strong style="color: var(--text-main);">{{ $player->batting_style ?? 'Right-hand bat' }}</strong></div>
                        <div>🎯 <strong style="color: var(--text-main);">{{ $player->bowling_style ?? 'Right-arm' }}</strong></div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 4px;">
                    <a href="{{ route('player.profile', $player->id) }}" style="flex: 1; text-align: center; text-decoration: none; background: #0284c7; color: white; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; border-radius: 8px; transition: opacity 0.15s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
                        Profile 👤
                    </a>
                    <a href="{{ route('compare', ['p1' => $player->id]) }}" style="flex: 1; text-align: center; text-decoration: none; background: rgba(56, 189, 248, 0.1); color: #38bdf8; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.2); transition: background 0.15s;"
                        onmouseover="this.style.background='rgba(56, 189, 248, 0.2)';" onmouseout="this.style.background='rgba(56, 189, 248, 0.1)';">
                        Compare ⚡
                    </a>
                </div>

            </div>
        @empty
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 40px; text-align: center; grid-column: 1/-1;">
                <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">No players found matching this criteria.</p>
            </div>
        @endforelse
    </div>

</main>
@endsection
