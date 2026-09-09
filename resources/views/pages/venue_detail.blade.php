@extends('layouts.app')

@section('content')
<main class="container" style="max-width: 1100px; margin: 0 auto; padding: 40px 20px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Breadcrumbs & Actions -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-dim);">
            <a href="{{ route('home') }}" style="color: var(--text-dim); text-decoration: none;">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ route('venues') }}" style="color: var(--text-dim); text-decoration: none;">Venues</a>
            <span>&rsaquo;</span>
            <span style="color: #10b981;">{{ $venue->name }}</span>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ $mapsLink }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #10b981; color: white; border-radius: 8px; font-weight: 800; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                Open in Google Maps
            </a>
            <a href="{{ route('venues') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                &larr; All Venues
            </a>
        </div>
    </div>

    <!-- Stadium Hero Card -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        
        <!-- Stadium Photo Banner -->
        <div style="position: relative; height: 320px; width: 100%; overflow: hidden;">
            @if(!empty($venue->image_url))
                <img src="{{ $venue->image_url }}" alt="{{ $venue->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div style=\'width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; font-size: 5rem;\'>🏟️</div>';">
            @else
                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; font-size: 5rem;">
                    🏟️
                </div>
            @endif
            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(11,15,23,0.9) 0%, rgba(11,15,23,0.2) 60%, transparent 100%);"></div>
            
            <div style="position: absolute; bottom: 24px; left: 28px; right: 28px;">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: #10b981; color: white; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 4px; margin-bottom: 8px;">
                    CRICKET STADIUM
                </div>
                <h1 style="font-size: 2.2rem; font-weight: 900; color: white; margin: 0 0 6px 0; letter-spacing: -0.02em;">
                    {{ $venue->name }}
                </h1>
                <div style="color: #cbd5e1; font-size: 0.95rem; font-weight: 600;">
                    📍 {{ $venue->city ? $venue->city . ', ' : '' }}{{ $venue->country ?? 'India' }}
                </div>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div style="padding: 24px 28px; background: var(--bg-card); display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; border-top: 1px solid var(--border-color);">
            <div style="background: var(--bg-card-secondary); padding: 14px 18px; border-radius: 10px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim); display: block; text-transform: uppercase;">Seating Capacity</span>
                <strong style="font-size: 1.25rem; font-weight: 900; color: #10b981;">
                    {{ $venue->capacity ? (is_numeric($venue->capacity) ? number_format($venue->capacity) : $venue->capacity) : 'Standard Capacity' }}
                </strong>
            </div>

            <div style="background: var(--bg-card-secondary); padding: 14px 18px; border-radius: 10px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim); display: block; text-transform: uppercase;">City / Region</span>
                <strong style="font-size: 1.15rem; font-weight: 800; color: var(--text-main);">
                    {{ $venue->city ?: 'Multiple' }}
                </strong>
            </div>

            <div style="background: var(--bg-card-secondary); padding: 14px 18px; border-radius: 10px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim); display: block; text-transform: uppercase;">Country</span>
                <strong style="font-size: 1.15rem; font-weight: 800; color: var(--text-main);">
                    {{ $venue->country ?: 'India' }}
                </strong>
            </div>

            <div style="background: var(--bg-card-secondary); padding: 14px 18px; border-radius: 10px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim); display: block; text-transform: uppercase;">Matches Scheduled / Hosted</span>
                <strong style="font-size: 1.25rem; font-weight: 900; color: #38bdf8;">
                    {{ $venueMatches->count() }} Matches
                </strong>
            </div>
        </div>

    </div>

    <!-- Venue Overview / Description -->
    @if(!empty($venue->description))
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px 28px; margin-bottom: 30px;">
            <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span>🏟️</span> About {{ $venue->name }}
            </h2>
            <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin: 0;">
                {{ $venue->description }}
            </p>
        </div>
    @endif

    <!-- Matches Played at this Venue -->
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <span>🏏</span> Matches at this Stadium
        </h2>

        @if($venueMatches->isNotEmpty())
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
                @foreach($venueMatches as $m)
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.15s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: #38bdf8;">
                                    {{ $m->tournament->name ?? 'Cricket Match' }}
                                </span>
                                <span style="font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: {{ $m->status === 'live' ? '#ef4444' : ($m->status === 'completed' ? '#10b981' : '#0284c7') }}; color: white; text-transform: uppercase;">
                                    {{ $m->status }}
                                </span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">{{ $m->team1->name ?? 'Team 1' }}</span>
                                <span style="font-weight: 900; color: var(--text-main);">{{ $m->team1_score ?? '-' }}</span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="font-weight: 800; color: var(--text-main); font-size: 0.95rem;">{{ $m->team2->name ?? 'Team 2' }}</span>
                                <span style="font-weight: 900; color: var(--text-main);">{{ $m->team2_score ?? '-' }}</span>
                            </div>
                        </div>

                        <div style="padding-top: 10px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.76rem; color: var(--text-dim);">
                                📅 {{ $m->match_date ? \Carbon\Carbon::parse($m->match_date)->format('d M, Y') : 'Scheduled' }}
                            </span>
                            <a href="{{ route('matches.detail', $m->id) }}" style="font-size: 0.8rem; font-weight: 800; color: #0284c7; text-decoration: none;">
                                Match Scorecard &rarr;
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 32px; text-align: center; color: var(--text-muted);">
                No recorded match fixtures at this stadium yet.
            </div>
        @endif
    </div>

</main>
@endsection
