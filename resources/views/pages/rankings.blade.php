@extends('layouts.app')

@section('pageTitle', $pageTitle . ' — Official ICC Standings | CricketKaScore')
@section('meta_description', 'Latest official ' . $pageTitle . '. View points, ratings, match stats, and player rankings on CricketKaScore.')

@section('content')
<div style="background: var(--bg-main, #0f172a); min-height: 80vh; padding: 24px 0 60px;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 16px;">

        <!-- BREADCRUMBS -->
        <nav style="font-size: 0.8rem; font-weight: 700; color: var(--text-dim, #64748b); margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
            <a href="{{ route('home') }}" style="color: var(--text-dim, #64748b); text-decoration: none;">HOME</a>
            <span>&rsaquo;</span>
            <a href="{{ route('rankings') }}" style="color: var(--text-dim, #64748b); text-decoration: none;">RANKINGS</a>
            <span>&rsaquo;</span>
            <span style="color: #38bdf8;">{{ $pageTitle }}</span>
        </nav>

        <!-- PAGE HEADER (Image 2 style) -->
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 900; color: var(--text-main, #ffffff); margin: 0 0 6px 0; letter-spacing: -0.02em; text-transform: uppercase;">
                {{ $pageTitle }}
            </h1>
            <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #94a3b8);">
                Rank date &mdash; {{ date('d F Y') }}
            </div>
        </div>

        <!-- NAVIGATION TABS & FORMAT PILLS (Image 2 style) -->
        <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px;">
            
            <!-- ROW 1: GENDER & CATEGORY TABS -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border-color, #334155); padding-bottom: 12px;">
                
                <!-- GENDER SWITCHER -->
                <div style="display: inline-flex; background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, #334155); border-radius: 9999px; padding: 3px;">
                    <a href="{{ route('rankings', ['category' => $category, 'format' => $format, 'gender' => 'men']) }}"
                       style="padding: 6px 18px; border-radius: 9999px; font-size: 0.82rem; font-weight: 800; text-decoration: none; transition: all 0.2s; {{ $gender === 'men' ? 'background: #0284c7; color: #ffffff;' : 'color: var(--text-muted, #94a3b8);' }}">
                        MEN'S
                    </a>
                    <a href="{{ route('rankings', ['category' => $category, 'format' => $format, 'gender' => 'women']) }}"
                       style="padding: 6px 18px; border-radius: 9999px; font-size: 0.82rem; font-weight: 800; text-decoration: none; transition: all 0.2s; {{ $gender === 'women' ? 'background: #0284c7; color: #ffffff;' : 'color: var(--text-muted, #94a3b8);' }}">
                        WOMEN'S
                    </a>
                </div>

                <!-- CATEGORY PILLS -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @php
                        $cats = [
                            'teams' => 'TEAMS',
                            'batting' => 'BATTING',
                            'bowling' => 'BOWLING',
                            'all_rounder' => 'ALL-ROUNDER'
                        ];
                    @endphp
                    @foreach($cats as $catKey => $catLabel)
                        <a href="{{ route('rankings', ['category' => $catKey, 'format' => $format, 'gender' => $gender]) }}"
                           style="padding: 6px 16px; border-radius: 6px; font-size: 0.82rem; font-weight: 800; text-decoration: none; transition: all 0.2s; {{ $category === $catKey ? 'background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid #38bdf8;' : 'background: var(--bg-card, #1e293b); color: var(--text-muted, #94a3b8); border: 1px solid var(--border-color, #334155);' }}">
                            {{ $catLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- ROW 2: FORMAT PILLS (TEST, ODI, T20) -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                @foreach(['TEST', 'ODI', 'T20'] as $fmt)
                    <a href="{{ route('rankings', ['category' => $category, 'format' => $fmt, 'gender' => $gender]) }}"
                       style="padding: 7px 22px; border-radius: 6px; font-size: 0.85rem; font-weight: 900; letter-spacing: 0.04em; text-decoration: none; transition: all 0.2s; {{ $format === $fmt ? 'background: #0284c7; color: #ffffff; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);' : 'background: var(--bg-card, #1e293b); color: var(--text-muted, #94a3b8); border: 1px solid var(--border-color, #334155);' }}">
                        {{ $fmt }}
                    </a>
                @endforeach
            </div>

        </div>

        <!-- FILTER BAR (Image 2 style: Date + Apply) -->
        <div style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, #334155); border-radius: 10px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <select style="background: var(--bg-main, #0f172a); border: 1px solid var(--border-color, #334155); color: var(--text-main, #ffffff); font-size: 0.85rem; font-weight: 700; padding: 7px 14px; border-radius: 6px; outline: none; cursor: pointer; min-width: 180px;">
                <option selected>{{ strtoupper(date('d F Y')) }} (LATEST)</option>
                <option>{{ strtoupper(date('d F Y', strtotime('-7 days'))) }}</option>
                <option>{{ strtoupper(date('d F Y', strtotime('-14 days'))) }}</option>
                <option>{{ strtoupper(date('d F Y', strtotime('-30 days'))) }}</option>
            </select>
            <button type="button" style="background: #0284c7; color: white; border: none; padding: 7px 20px; border-radius: 9999px; font-size: 0.8rem; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;">
                APPLY &rsaquo;
            </button>
        </div>

        <!-- FULL RANKINGS TABLE (Image 2 exact style) -->
        <div style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, #334155); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            
            <!-- Accent Top Line -->
            <div style="height: 2px; background: #38bdf8;"></div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color, #334155); color: var(--text-dim, #64748b); font-weight: 800; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em;">
                            <th style="padding: 14px 18px; width: 85px;">POS</th>
                            <th style="padding: 14px 16px;">{{ $category === 'teams' ? 'TEAM' : 'PLAYER' }}</th>
                            @if($category !== 'teams')
                                <th style="padding: 14px 16px; width: 110px;">TEAM</th>
                            @endif
                            <th style="padding: 14px 16px; text-align: center; width: 110px;">MATCHES</th>
                            <th style="padding: 14px 16px; text-align: center; width: 120px;">PTS</th>
                            <th style="padding: 14px 22px; text-align: right; width: 120px;">RATING</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $item)
                            <tr style="border-bottom: 1px solid var(--border-color, #334155); transition: background 0.15s;" onmouseover="this.style.background='var(--bg-card-hover, rgba(255,255,255,0.03))';" onmouseout="this.style.background='none';">
                                
                                <!-- POS (Rank #) -->
                                <td style="padding: 14px 18px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 900; font-size: 1.05rem; color: {{ ($loop->iteration <= 3) ? '#38bdf8' : 'var(--text-main, #ffffff)' }};">
                                            {{ sprintf('%02d', $item->rank_num ?? $item->current_rank) }}
                                        </span>
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ ($loop->iteration <= 3) ? '#38bdf8' : '#64748b' }};"></span>
                                    </div>
                                </td>

                                <!-- TEAM / PLAYER -->
                                <td style="padding: 14px 16px; vertical-align: middle;">
                                    @if($category === 'teams')
                                        <!-- Team Row -->
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="{{ $item->logo_url }}" alt="{{ $item->team_name }}" style="width: 32px; height: 22px; object-fit: contain; border-radius: 3px; border: 1px solid rgba(255,255,255,0.1); background: #ffffff; padding: 1px;" onerror="this.onerror=null; this.src='https://flagcdn.com/w80/in.png';">
                                            <span style="font-weight: 900; font-size: 0.98rem; color: var(--text-main, #ffffff); letter-spacing: 0.02em;">
                                                {{ strtoupper($item->team_name) }}
                                            </span>
                                        </div>
                                    @else
                                        <!-- Player Row -->
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="position: relative; width: 38px; height: 38px; flex-shrink: 0;">
                                                <img src="{{ $item->photo_url }}" alt="{{ $item->player_name }}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #38bdf8; background: var(--bg-main, #0f172a);" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('{{ addslashes($item->player_name) }}') + '&background=0b1120&color=38bdf8&bold=true&size=128&rounded=true';">
                                                <img src="{{ $item->country_flag }}" alt="{{ $item->country }}" style="position: absolute; bottom: -2px; right: -2px; width: 16px; height: 11px; object-fit: cover; border-radius: 2px; border: 1px solid #000000;" onerror="this.style.display='none';">
                                            </div>
                                            <div>
                                                <div style="font-weight: 900; font-size: 0.98rem; color: var(--text-main, #ffffff);">
                                                    {{ $item->player_name }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-dim, #64748b);">
                                                    Best: #{{ $item->best_rank ?? '--' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- TEAM (For Player Row) -->
                                @if($category !== 'teams')
                                    <td style="padding: 14px 16px; vertical-align: middle;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <img src="{{ $item->country_flag }}" alt="{{ $item->country }}" style="width: 20px; height: 14px; object-fit: cover; border-radius: 2px;" onerror="this.style.display='none';">
                                            <span style="font-weight: 800; font-size: 0.85rem; color: var(--text-muted, #94a3b8); letter-spacing: 0.04em;">
                                                {{ strtoupper($item->country ?: 'IND') }}
                                            </span>
                                        </div>
                                    </td>
                                @endif

                                <!-- MATCHES -->
                                <td style="padding: 14px 16px; vertical-align: middle; text-align: center; font-weight: 700; color: var(--text-muted, #94a3b8);">
                                    {{ $item->matches_played ?? $item->matches ?? '--' }}
                                </td>

                                <!-- PTS -->
                                <td style="padding: 14px 16px; vertical-align: middle; text-align: center; font-weight: 700; color: var(--text-muted, #94a3b8);">
                                    {{ number_format($item->points ?? 0) }}
                                </td>

                                <!-- RATING (Bold Image 2 style) -->
                                <td style="padding: 14px 22px; vertical-align: middle; text-align: right;">
                                    <span style="font-size: 1.15rem; font-weight: 900; color: var(--text-main, #ffffff); letter-spacing: -0.01em;">
                                        {{ $item->rating ?? 0 }}
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 48px 20px; color: var(--text-dim, #64748b); font-weight: 600;">
                                    No rankings recorded for this category and format.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
@endsection
