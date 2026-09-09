@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 40px 24px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Breadcrumb & Back -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-dim);">
            <a href="{{ route('home') }}" style="color: var(--text-dim); text-decoration: none;">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('players') }}" style="color: var(--text-dim); text-decoration: none;">Players</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">{{ $player->name }}</span>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('compare', ['p1' => $player->id]) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #0284c7, #38bdf8); color: white; border-radius: 8px; font-weight: 800; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 12px rgba(2,132,199,0.3);">
                ⚡ Compare with Other Players
            </a>
            <a href="{{ route('players') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                &larr; All Players
            </a>
        </div>
    </div>

    <!-- Player Hero Profile Card -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 32px 28px; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="position: absolute; top: -60px; right: -60px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="display: flex; gap: 28px; align-items: center; flex-wrap: wrap;">
            
            <!-- Player Image / Avatar -->
            <div style="flex-shrink: 0; width: 120px; height: 120px;">
                @if(!empty($player->profile_image))
                    <img src="{{ $player->profile_image }}" alt="{{ $player->name }}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #38bdf8; box-shadow: 0 6px 20px rgba(56, 189, 248, 0.3);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #38bdf8); display: none; align-items: center; justify-content: center; font-size: 2.8rem; font-weight: 900; color: white; box-shadow: 0 6px 20px rgba(2,132,199,0.35);">
                        {{ strtoupper(substr($player->name, 0, 2)) }}
                    </div>
                @else
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #38bdf8); display: flex; align-items: center; justify-content: center; font-size: 2.8rem; font-weight: 900; color: white; box-shadow: 0 6px 20px rgba(2,132,199,0.35);">
                        {{ strtoupper(substr($player->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                    <h1 style="font-size: 2rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">
                        {{ $player->name }}
                    </h1>
                    @if($player->jersey_number)
                        <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 800; font-size: 0.88rem; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(56, 189, 248, 0.3);">
                            #{{ $player->jersey_number }}
                        </span>
                    @endif
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <span style="background: rgba(34, 197, 94, 0.15); color: #22c55e; font-weight: 800; font-size: 0.8rem; padding: 4px 10px; border-radius: 6px;">
                        {{ strtoupper($player->role ?? 'CRICKETER') }}
                    </span>
                    @if($player->team)
                        <span style="background: var(--bg-card-secondary); color: var(--text-main); font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                            🛡️ {{ $player->team->name }} ({{ $player->team->short_name ?? '' }})
                        </span>
                    @endif
                    <span style="color: var(--text-dim); font-size: 0.85rem; font-weight: 600;">
                        📍 {{ $player->country ?? $player->nationality ?? 'International' }}
                    </span>
                    @if($player->date_of_birth)
                        <span style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; font-weight: 800; font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; border: 1px solid rgba(244, 63, 94, 0.3);">
                            🎂 {{ \Carbon\Carbon::parse($player->date_of_birth)->format('d M') }}
                        </span>
                    @endif
                </div>

                <!-- Specs Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; background: var(--bg-card-secondary); border-radius: 10px; padding: 12px 16px; border: 1px solid var(--border-color);">
                    <div>
                        <span style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block;">Batting Style</span>
                        <strong style="color: var(--text-main); font-size: 0.88rem;">{{ $player->batting_style ?: 'Right-hand bat' }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block;">Bowling Style</span>
                        <strong style="color: var(--text-main); font-size: 0.88rem;">{{ $player->bowling_style ?: 'Right-arm medium' }}</strong>
                    </div>
                    @if($player->date_of_birth)
                        @php
                            $dobCarbon = \Carbon\Carbon::parse($player->date_of_birth);
                            $age = $dobCarbon->age;
                            $formattedDob = $dobCarbon->format('d M Y');
                        @endphp
                        <div>
                            <span style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block;">🎂 Date of Birth / Age</span>
                            <strong style="color: #f43f5e; font-size: 0.88rem;">{{ $formattedDob }} ({{ $age }} yrs)</strong>
                        </div>
                    @endif
                    <div>
                        <span style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block;">Matches Recorded</span>
                        <strong style="color: #38bdf8; font-size: 0.88rem;">{{ $stats['matches'] ?? 15 }} Matches</strong>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Career Statistics Dashboard -->
    <div style="margin-bottom: 36px;">
        <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>📊</span> Career Statistics &amp; Metrics
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <!-- BATTING STATS CARD -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                        <span>🏏</span> Batting Record
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim);">All Formats</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; text-align: center;">
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">TOTAL RUNS</span>
                        <strong style="font-size: 1.4rem; color: var(--text-main); font-weight: 900;">{{ number_format($stats['runs'] ?? 0) }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">HIGHEST SCORE</span>
                        <strong style="font-size: 1.4rem; color: #38bdf8; font-weight: 900;">{{ $stats['highestScore'] ?? 0 }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">AVERAGE</span>
                        <strong style="font-size: 1.4rem; color: var(--text-main); font-weight: 900;">{{ $stats['average'] ?? '0.00' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">STRIKE RATE</span>
                        <strong style="font-size: 1.2rem; color: #22c55e; font-weight: 800;">{{ $stats['strikeRate'] ?? '0.00' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">50s / 100s</span>
                        <strong style="font-size: 1.2rem; color: var(--text-main); font-weight: 800;">{{ $stats['fifties'] ?? 0 }} / {{ $stats['hundreds'] ?? 0 }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">4s / 6s</span>
                        <strong style="font-size: 1.2rem; color: var(--text-main); font-weight: 800;">{{ $stats['fours'] ?? 0 }} / {{ $stats['sixes'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <!-- BOWLING STATS CARD -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #f59e0b; display: flex; align-items: center; gap: 6px;">
                        <span>🎯</span> Bowling Record
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim);">All Formats</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; text-align: center;">
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">WICKETS</span>
                        <strong style="font-size: 1.4rem; color: var(--text-main); font-weight: 900;">{{ number_format($stats['wickets'] ?? 0) }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">BEST BOWLING</span>
                        <strong style="font-size: 1.4rem; color: #f59e0b; font-weight: 900;">{{ $stats['bestBowling'] ?? '-' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">ECONOMY</span>
                        <strong style="font-size: 1.4rem; color: var(--text-main); font-weight: 900;">{{ $stats['economy'] ?? '0.00' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">OVERS BOWLED</span>
                        <strong style="font-size: 1.2rem; color: var(--text-main); font-weight: 800;">{{ $stats['overs'] ?? '0.0' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">BOWLING AVG</span>
                        <strong style="font-size: 1.2rem; color: var(--text-main); font-weight: 800;">{{ $stats['bowlingAvg'] ?? '-' }}</strong>
                    </div>
                    <div style="background: var(--bg-card-secondary); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 0.72rem; color: var(--text-dim); font-weight: 700; display: block; margin-bottom: 4px;">MAIDENS</span>
                        <strong style="font-size: 1.2rem; color: var(--text-main); font-weight: 800;">{{ $stats['maidens'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Teammates -->
    @if($teammates && $teammates->isNotEmpty())
        <div style="margin-bottom: 30px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 14px;">
                👥 Teammates in {{ $player->team->name ?? 'Team' }}
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px;">
                @foreach($teammates as $mate)
                    <a href="{{ route('player.profile', $mate->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 12px; transition: transform 0.15s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-card-secondary); border: 1px solid #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #38bdf8; font-size: 0.9rem;">
                            {{ strtoupper(substr($mate->name, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.88rem; color: var(--text-main);">{{ $mate->name }}</div>
                            <div style="font-size: 0.74rem; color: var(--text-dim);">{{ $mate->role ?? 'Player' }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</main>
@endsection
