@extends('layouts.app')

@section('content')
<main class="home-container">

    <!-- ========================================================
         SECTION 1: LIVE & UPCOMING MATCHES CAROUSEL
         ======================================================== -->
    @if($allMatches->isNotEmpty())
    <section id="matches" class="matches-carousel-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span>LIVE & UPCOMING MATCHES</span>
                    @if($allMatches->where('status', 'live')->count() > 0)
                        <span class="badge-live">LIVE</span>
                    @endif
                </div>
                <a href="{{ route('matches') }}" class="view-all-link">FULL SCHEDULE &rarr;</a>
            </div>

            <div class="carousel-grid">
                @foreach($allMatches as $m)
                    <a href="{{ route('matches.detail', $m->id) }}" class="match-card" style="text-decoration:none;display:block;">
                        <div class="match-card-header">
                            <span>{{ $m->tournament->name ?? ($m->level_type ?? ($m->match_type . ' - ' . ($m->venue->name ?? 'MATCH'))) }}</span>
                            @if($m->effective_status === 'live')
                                <span class="badge-live" style="font-size:0.6rem;padding:1px 5px;">LIVE</span>
                            @elseif($m->effective_status === 'completed')
                                <span class="tag-badge" style="background:#059669;color:white;font-size:0.6rem;padding:1px 5px;">COMPLETED</span>
                            @else
                                <span class="tag-badge" style="background:#475569;color:white;font-size:0.6rem;padding:1px 5px;">UPCOMING</span>
                            @endif
                        </div>

                        <div class="match-card-teams">
                            <div class="team-row">
                                <div class="team-info">
                                    <div class="team-avatar" style="border-color: {{ $m->team1?->color_code ?? 'var(--primary)' }};">
                                        {{ $m->team1?->short_name ?? ($m->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team1->name), 0, 3)) : '') }}
                                    </div>
                                    <span class="team-name">{{ $m->team1?->name ?? '' }}</span>
                                </div>
                                <div class="team-score">
                                    @if($m->effective_status === 'completed' || $m->effective_status === 'live' || $m->team1_score > 0)
                                        {{ $m->team1_score }}/{{ $m->team1_wickets }} <span style="font-size:0.7rem;color:var(--text-dim);">({{ $m->team1_overs }} ov)</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <div class="team-row">
                                <div class="team-info">
                                    <div class="team-avatar" style="border-color: {{ $m->team2?->color_code ?? '#38bdf8' }};">
                                        {{ $m->team2?->short_name ?? ($m->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $m->team2->name), 0, 3)) : '') }}
                                    </div>
                                    <span class="team-name">{{ $m->team2?->name ?? '' }}</span>
                                </div>
                                <div class="team-score">
                                    @if($m->effective_status === 'completed' || $m->effective_status === 'live' || $m->team2_score > 0)
                                        {{ $m->team2_score }}/{{ $m->team2_wickets }} <span style="font-size:0.7rem;color:var(--text-dim);">({{ $m->team2_overs }} ov)</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="match-card-footer">
                            @if($m->effective_status === 'completed')
                                <span style="color: #4ade80; font-weight: 800;">🏆 {{ $m->winning_title }}</span>
                            @elseif($m->effective_status === 'live')
                                <span>{{ $m->custom_note && strlen($m->custom_note) < 60 && !str_contains($m->custom_note, '<p>') ? $m->custom_note : 'Match In Progress' }}</span>
                            @else
                                <span>{{ $m->match_date ? date('M d, h:i A', strtotime($m->match_date)) : 'Scheduled' }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ========================================================
         SECTION 2: SERIES HUB
         ======================================================== -->
    <section id="series" class="series-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-title-icon">🏆</span>
                    <span>SERIES</span>
                </div>
                <a href="{{ route('tournaments') }}" class="view-all-link">ALL SERIES &rarr;</a>
            </div>

            @php
                // Determine which tab is active
                $activeSeriesTab = 'ongoing';
                if ($liveSeries->isNotEmpty())          $activeSeriesTab = 'live';
                elseif ($ongoingSeries->isNotEmpty())   $activeSeriesTab = 'ongoing';
                elseif ($upcomingSeries->isNotEmpty())  $activeSeriesTab = 'upcoming';
                elseif ($completedSeries->isNotEmpty()) $activeSeriesTab = 'completed';
                elseif ($localSeries->isNotEmpty())     $activeSeriesTab = 'local';
            @endphp

            <div class="series-tabs">
                @if($liveSeries->isNotEmpty())
                <button type="button" class="series-tab {{ $activeSeriesTab === 'live' ? 'active' : '' }}" onclick="switchSeriesTab('live', this)">
                    🔴 Live ({{ $liveSeries->count() }})
                </button>
                @endif
                <button type="button" class="series-tab {{ $activeSeriesTab === 'upcoming' ? 'active' : '' }}" onclick="switchSeriesTab('upcoming', this)">
                    Upcoming ({{ $upcomingSeries->count() }})
                </button>
                <button type="button" class="series-tab {{ $activeSeriesTab === 'ongoing' ? 'active' : '' }}" onclick="switchSeriesTab('ongoing', this)">
                    Ongoing ({{ $ongoingSeries->count() }})
                </button>
                <button type="button" class="series-tab {{ $activeSeriesTab === 'completed' ? 'active' : '' }}" onclick="switchSeriesTab('completed', this)">
                    Completed ({{ $completedSeries->count() }})
                </button>
                <button type="button" class="series-tab {{ $activeSeriesTab === 'local' ? 'active' : '' }}" onclick="switchSeriesTab('local', this)">
                    Local ({{ $localSeries->count() }})
                </button>
            </div>

            @php
            // Helper to render a series card's match block
            if (!function_exists('seriesMatchBlock')) {
                function seriesMatchBlock($s) {
                    if ($s->matches->count() === 0) return '';
                    $lm = $s->matches->first();
                    $t1 = $lm->team1?->short_name ?? ($lm->team1?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $lm->team1->name), 0, 3)) : '');
                    $t2 = $lm->team2?->short_name ?? ($lm->team2?->name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $lm->team2->name), 0, 3)) : '');
                    $st = strtolower($lm->status ?? '');
                    $badgeStyle = $st === 'live' ? 'color:#ef4444;' : ($st === 'completed' ? 'color:#22c55e;' : 'color:var(--text-dim);');
                    return [
                        'team1' => $t1, 'team2' => $t2,
                        'score1' => $lm->team1_score.'/'.$lm->team1_wickets, 'overs1' => $lm->team1_overs,
                        'score2' => $lm->team2_score.'/'.$lm->team2_wickets, 'overs2' => $lm->team2_overs,
                        'status' => strtoupper($lm->status ?? ''), 'badgeStyle' => $badgeStyle,
                    ];
                }
            }
            @endphp

            {{-- LIVE TAB --}}
            @if($liveSeries->isNotEmpty())
            <div id="series-tab-live" class="series-grid series-tab-content" style="{{ $activeSeriesTab !== 'live' ? 'display:none;' : '' }}">
                @foreach($liveSeries->take(3) as $s)
                    @php $mb = seriesMatchBlock($s); @endphp
                    <div class="series-card" onclick="window.location.href='{{ route('tournament.public', $s->id) }}'" style="cursor: pointer;">
                        <div class="series-info">
                            <h4 class="series-title">{{ $s->name }}</h4>
                            <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                            @if($mb)
                            <div style="margin-top: 12px; background: var(--bg-card-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px;">
                                    <span style="color: var(--primary);">LATEST MATCH</span>
                                    <span style="{{ $mb['badgeStyle'] }}">{{ $mb['status'] }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team1'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score1'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs1'] }})</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team2'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score2'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs2'] }})</span></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="series-arrow">&rarr;</div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- UPCOMING TAB --}}
            <div id="series-tab-upcoming" class="series-grid series-tab-content" style="{{ $activeSeriesTab !== 'upcoming' ? 'display:none;' : '' }}">
                @forelse($upcomingSeries->take(3) as $s)
                    @php $mb = seriesMatchBlock($s); @endphp
                    <div class="series-card" onclick="window.location.href='{{ route('tournament.public', $s->id) }}'" style="cursor: pointer;">
                        <div class="series-info">
                            <h4 class="series-title">{{ $s->name }}</h4>
                            <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                            @if($mb)
                            <div style="margin-top: 12px; background: var(--bg-card-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px;">
                                    <span style="color: var(--primary);">LATEST MATCH</span>
                                    <span style="{{ $mb['badgeStyle'] }}">{{ $mb['status'] }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team1'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score1'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs1'] }})</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team2'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score2'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs2'] }})</span></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="series-arrow">&rarr;</div>
                    </div>
                @empty
                    <p style="color:var(--text-dim);padding:14px;grid-column:1/-1;">No upcoming tournaments listed.</p>
                @endforelse
            </div>

            {{-- ONGOING TAB --}}
            <div id="series-tab-ongoing" class="series-grid series-tab-content" style="{{ $activeSeriesTab !== 'ongoing' ? 'display:none;' : '' }}">
                @forelse($ongoingSeries->take(3) as $s)
                    @php $mb = seriesMatchBlock($s); @endphp
                    <div class="series-card" onclick="window.location.href='{{ route('tournament.public', $s->id) }}'" style="cursor: pointer;">
                        <div class="series-info">
                            <h4 class="series-title">{{ $s->name }}</h4>
                            <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                            @if($mb)
                            <div style="margin-top: 12px; background: var(--bg-card-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px;">
                                    <span style="color: var(--primary);">LATEST MATCH</span>
                                    <span style="{{ $mb['badgeStyle'] }}">{{ $mb['status'] }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team1'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score1'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs1'] }})</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team2'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score2'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs2'] }})</span></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="series-arrow">&rarr;</div>
                    </div>
                @empty
                    <p style="color:var(--text-dim);padding:14px;grid-column:1/-1;">No ongoing tournaments right now.</p>
                @endforelse
            </div>

            {{-- COMPLETED TAB --}}
            <div id="series-tab-completed" class="series-grid series-tab-content" style="{{ $activeSeriesTab !== 'completed' ? 'display:none;' : '' }}">
                @forelse($completedSeries->take(3) as $s)
                    @php $mb = seriesMatchBlock($s); @endphp
                    <div class="series-card" onclick="window.location.href='{{ route('tournament.public', $s->id) }}'" style="cursor: pointer;">
                        <div class="series-info">
                            <h4 class="series-title">{{ $s->name }}</h4>
                            <span class="series-location">{{ $s->city ?? 'Multiple' }}</span>
                            @if($mb)
                            <div style="margin-top: 12px; background: var(--bg-card-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px;">
                                    <span style="color: var(--primary);">LATEST MATCH</span>
                                    <span style="{{ $mb['badgeStyle'] }}">{{ $mb['status'] }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team1'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score1'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs1'] }})</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team2'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score2'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs2'] }})</span></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="series-arrow">&rarr;</div>
                    </div>
                @empty
                    <p style="color:var(--text-dim);padding:14px;grid-column:1/-1;">No completed tournaments yet.</p>
                @endforelse
            </div>

            {{-- LOCAL TAB --}}
            <div id="series-tab-local" class="series-grid series-tab-content" style="{{ $activeSeriesTab !== 'local' ? 'display:none;' : '' }}">
                @forelse($localSeries->take(3) as $s)
                    @php $mb = seriesMatchBlock($s); @endphp
                    <div class="series-card" onclick="window.location.href='{{ route('tournament.public', $s->id) }}'" style="cursor: pointer;">
                        <div class="series-info">
                            <h4 class="series-title">{{ $s->name }}</h4>
                            <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                            @if($mb)
                            <div style="margin-top: 12px; background: var(--bg-card-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px;">
                                    <span style="color: var(--primary);">LATEST MATCH</span>
                                    <span style="{{ $mb['badgeStyle'] }}">{{ $mb['status'] }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team1'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score1'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs1'] }})</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $mb['team2'] }}</span>
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $mb['score2'] }} <span style="font-size:0.7rem; color:var(--text-dim);">({{ $mb['overs2'] }})</span></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="series-arrow">&rarr;</div>
                    </div>
                @empty
                    <p style="color:var(--text-dim);padding:14px;grid-column:1/-1;">No local tournaments listed.</p>
                @endforelse
            </div>

        </div>
    </section>


    @if($predictions->isNotEmpty() || $fantasyTips->isNotEmpty())
    <section class="split-content-section">
        <div class="container">
            <div class="two-col-grid">
                <!-- Column 1: Match Predictions -->
                <div>
                    <div class="section-header">
                        <div class="section-title">
                            <span class="section-title-icon">🎯</span>
                            <span>MATCH PREDICTIONS</span>
                        </div>
                        <a href="{{ route('news', ['type' => 'prediction']) }}" class="view-all-link">ALL PREDICTIONS &rarr;</a>
                    </div>
                    @forelse($predictions->take(6) as $p)
                        <a href="{{ route('prediction.show', $p->id) }}" class="prediction-card" style="text-decoration: none; color: inherit; padding: 14px 16px; margin-bottom: 12px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border-color); border-left: 3px solid #f97316; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; cursor: pointer;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(249, 115, 22, 0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <div>
                                <span class="card-tag prediction" style="font-size: 0.68rem; padding: 2px 7px;">{{ $p->tag && $p->tag !== 'PREDICTION' ? $p->tag : 'MATCH PREDICTION' }}</span>
                                <h4 class="article-title" style="margin: 6px 0 8px 0; font-size: 0.95rem; line-height: 1.35; font-weight: 800; color: var(--text-main);">{{ $p->title }}</h4>
                                <p class="article-desc" style="font-size: 0.82rem; line-height: 1.5; color: var(--text-dim); margin-bottom: 0;">
                                    {{ Str::limit($p->summary, 180) }}
                                    <span style="color: #38bdf8; font-weight: 700; font-size: 0.8rem; margin-left: 4px;">Read More &rarr;</span>
                                </p>
                            </div>
                            <div style="border-top: 1px solid var(--border-color); padding-top: 8px; margin-top: 10px; font-size: 0.72rem; color: var(--text-dim); display: flex; justify-content: space-between;">
                                <span>CricketKaScore Desk</span>
                                <span>{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('M d') : 'Today' }}</span>
                            </div>
                        </a>
                    @empty
                        <p style="color:var(--text-dim);font-size:0.85rem;">No match predictions published yet.</p>
                    @endforelse
                </div>

                <!-- Column 2: Fantasy Tips -->
                <div>
                    <div class="section-header">
                        <div class="section-title">
                            <span class="section-title-icon">⭐</span>
                            <span>FANTASY TIPS</span>
                        </div>
                        <a href="{{ route('news', ['type' => 'fantasy']) }}" class="view-all-link">ALL FANTASY TIPS &rarr;</a>
                    </div>
                    @forelse($fantasyTips->take(6) as $f)
                        <a href="{{ route('fantasy.show', $f->id) }}" class="fantasy-card" style="text-decoration: none; color: inherit; padding: 14px 16px; margin-bottom: 12px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border-color); border-left: 3px solid #22c55e; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; cursor: pointer;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(34, 197, 94, 0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <div>
                                <span class="card-tag fantasy" style="font-size: 0.68rem; padding: 2px 7px;">{{ $f->tag ?? 'FANTASY' }}</span>
                                <h4 class="article-title" style="margin: 6px 0 8px 0; font-size: 0.95rem; line-height: 1.35; font-weight: 800; color: var(--text-main);">{{ $f->title }}</h4>
                                <p class="article-desc" style="font-size: 0.82rem; line-height: 1.5; color: var(--text-dim); margin-bottom: 0;">
                                    {{ Str::limit($f->summary, 180) }}
                                    <span style="color: #22c55e; font-weight: 700; font-size: 0.8rem; margin-left: 4px;">Read More &rarr;</span>
                                </p>
                            </div>
                            <div style="border-top: 1px solid var(--border-color); padding-top: 8px; margin-top: 10px; font-size: 0.72rem; color: var(--text-dim); display: flex; justify-content: space-between;">
                                <span>Fantasy Expert</span>
                                <span>{{ $f->created_at ? \Carbon\Carbon::parse($f->created_at)->format('M d') : 'Today' }}</span>
                            </div>
                        </a>
                    @empty
                        <p style="color:var(--text-dim);font-size:0.85rem;">No fantasy tips published yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- MATCH PREVIEWS SECTION (Directly below Prediction/Fantasy and above Articles) -->
    @if(isset($matchPreviews) && $matchPreviews->isNotEmpty())
    <section class="match-previews-section" style="padding: 32px 0 20px;">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-title-icon">⚡</span>
                    <span>MATCH PREVIEWS</span>
                </div>
                <a href="{{ route('news', ['type' => 'preview']) }}" class="view-all-link">ALL MATCH PREVIEWS &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @foreach($matchPreviews->take(6) as $preview)
                    @php
                        $previewImg = $preview->poster_image ?: $preview->image_url;
                        if (empty($previewImg)) {
                            $previewImg = 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80';
                        }
                    @endphp
                    <a href="{{ route('preview.show', $preview->id) }}" class="prediction-card" style="text-decoration: none; color: inherit; padding: 14px 16px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border-color); border-left: 3px solid #38bdf8; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; cursor: pointer;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(56, 189, 248, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <div>
                            <div style="width: 100%; height: 140px; border-radius: 8px; overflow: hidden; margin-bottom: 12px; background: var(--bg-card-secondary);">
                                <img src="{{ $previewImg }}" alt="{{ $preview->title }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.04)';" onmouseout="this.style.transform='scale(1)';" loading="lazy">
                            </div>
                            <span class="card-tag prediction" style="font-size: 0.68rem; padding: 2px 7px;">MATCH PREVIEW</span>
                            <h4 class="article-title" style="margin: 8px 0; font-size: 0.98rem; font-weight: 800; line-height: 1.35; color: var(--text-main);">{{ $preview->title }}</h4>
                            <p class="article-desc" style="font-size: 0.83rem; line-height: 1.5; color: var(--text-dim); margin-bottom: 0;">
                                {{ Str::limit($preview->summary, 150) }}
                                <span style="color: #38bdf8; font-weight: 700; font-size: 0.82rem; margin-left: 5px;">Read More &rarr;</span>
                            </p>
                        </div>
                        <div style="border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: 12px; font-size: 0.75rem; color: var(--text-dim); display: flex; justify-content: space-between;">
                            <span>CricketKaScore Desk</span>
                            <span>{{ $preview->created_at ? \Carbon\Carbon::parse($preview->created_at)->format('M d') : 'Today' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($articles->isNotEmpty())
    <section class="articles-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span>LATEST ARTICLES</span>
                </div>
                <a href="{{ route('news', ['type' => 'article']) }}" class="view-all-link">ALL ARTICLES &rarr;</a>
            </div>

            <div class="articles-grid">
                @foreach($articles->take(6) as $art)
                    <a href="{{ route('article.show', $art->id) }}" class="article-item" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                        @if(!empty($art->image_url))
                            <div class="article-img-box">
                                <img src="{{ $art->image_url }}" alt="{{ $art->title }}" loading="lazy" onerror="this.parentElement.style.display='none';">
                            </div>
                        @endif
                        <div class="article-body">
                            <span class="tag-badge primary">{{ $art->category }}</span>
                            <h4 class="article-title">{{ $art->title }}</h4>
                            <p class="article-desc">{{ $art->summary }}</p>
                            <div class="article-meta">
                                <span>📅 {{ $art->published_date ?? 'TODAY' }}</span>
                                <span>⏱️ {{ $art->read_time ?? '3 MIN READ' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($newsList->isNotEmpty())
    <section id="news" class="news-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span>LATEST NEWS</span>
                </div>
                <a href="{{ route('news') }}" class="view-all-link">ALL NEWS &rarr;</a>
            </div>

            <div class="news-grid-2col">
                @foreach($newsList->take(6) as $n)
                    <a href="{{ route('news.show', $n->id) }}" class="news-card-horizontal" style="text-decoration: none; color: inherit; display: flex; justify-content: space-between; align-items: center; gap: 14px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div style="flex: 1;">
                            <span class="card-tag prediction">{{ $n->category }}</span>
                            <h4 class="article-title" style="margin-top:6px; color: var(--text-main);">{{ $n->title }}</h4>
                        </div>
                        @if(!empty($n->image_url))
                            <img src="{{ $n->image_url }}" alt="News" class="news-thumb" loading="lazy" style="width: 72px; height: 52px; object-fit: cover; border-radius: 6px; flex-shrink: 0;" onerror="this.style.display='none';">
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ========================================================
         SECTION 6: MOST POPULAR TEAMS
         ======================================================== -->
    @if($popularTeams->isNotEmpty())
        <section class="popular-teams-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-title-icon">🛡️</span>
                        <span>MOST POPULAR TEAMS</span>
                    </div>
                    <a href="{{ route('teams') }}" class="view-all-link">ALL POPULAR TEAMS &rarr;</a>
                </div>

                <div class="popular-teams-grid">
                    @foreach($popularTeams->take(6) as $t)
                        <div class="popular-team-card">
                            <div class="popular-team-badge" style="border-color: {{ $t->color_code ?? 'var(--primary)' }};">
                                {{ $t->short_name }}
                            </div>
                            <div>
                                <div class="popular-team-name">{{ $t->name }}</div>
                                <div class="popular-team-city">{{ $t->city ?? $t->country ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ========================================================
         SECTION 6.6: VENUES
         ======================================================== -->
    @if($venues->isNotEmpty())
    <section style="padding: 32px 0 40px; background: var(--bg-secondary, var(--bg-main));">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-title-icon">🏟️</span>
                    <span>CRICKET VENUES</span>
                </div>
                <a href="{{ route('venues') }}" class="view-all-link">ALL VENUES &rarr;</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px;">
                @foreach($venues as $venue)
                    <a href="{{ route('venues.show', $venue->id) }}" style="text-decoration: none; display: block; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; cursor: pointer;"
                        onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 10px 24px rgba(0,0,0,0.35)';this.style.borderColor='rgba(34, 197, 94, 0.4)';"
                        onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';this.style.borderColor='var(--border)';">
                        @if(!empty($venue->image_url))
                            <img src="{{ $venue->image_url }}" alt="{{ $venue->name }}" loading="lazy"
                                style="width: 100%; height: 100px; object-fit: cover;" onerror="this.parentElement.innerHTML='<div style=\'width: 100%; height: 100px; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; font-size: 2rem;\'>🏟️</div>';">
                        @else
                            <div style="width: 100%; height: 100px; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; font-size: 2rem;">🏟️</div>
                        @endif
                        <div style="padding: 12px 14px;">
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.88rem;">{{ $venue->name }}</div>
                            <div style="font-size: 0.76rem; color: var(--text-dim); margin-top: 3px;">
                                @if($venue->city) 📍 {{ $venue->city }}{{ $venue->country ? ', ' . $venue->country : '' }} @endif
                                @if($venue->capacity) <br>Capacity: {{ is_numeric($venue->capacity) ? number_format($venue->capacity) : $venue->capacity }} @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($webStories->isNotEmpty())
        <!-- ========================================================
             SECTION 6.1: WEB STORIES
             ======================================================== -->
        <section class="web-stories-section" style="padding: 40px 0 24px; background: var(--bg-main);">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; margin-bottom: 24px;">
                    <div class="section-title" style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: var(--text-main);">WEB STORIES</span>
                    </div>
                    <a href="{{ route('webstories.all') }}" style="color: #22c55e; font-size: 0.85rem; font-weight: 800; text-decoration: none; letter-spacing: 0.05em;">ALL STORIES</a>
                </div>

                <div style="position: relative; display: flex; align-items: center;">
                    <!-- Left Arrow (Desktop Only) -->
                    <button class="hidden md:flex" onclick="scrollStories(-1)" style="position: absolute; left: -16px; z-index: 10; width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.2rem; box-shadow: var(--shadow-sm);" onmouseover="this.style.background='var(--bg-card-hover)';" onmouseout="this.style.background='var(--bg-card)';">
                        &lsaquo;
                    </button>

                    <!-- Stories Scroll Container -->
                    <div id="storiesContainer" style="display: flex; gap: 14px; overflow-x: auto; scroll-behavior: smooth; padding: 4px 0; width: 100%; -ms-overflow-style: none; scrollbar-width: none;">
                        @foreach($webStories as $story)
                            <a href="{{ route('webstories.show', $story->id) }}" style="text-decoration: none; flex: 0 0 170px; height: 250px; border-radius: 12px; position: relative; overflow: hidden; border: none; cursor: pointer; display: block; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='translateY(0)';">
                                <!-- Play Badge -->
                                <div style="position: absolute; top: 12px; right: 12px; z-index: 3; width: 22px; height: 22px; border-radius: 50%; background: rgba(15, 23, 42, 0.6); border: 1.5px solid #22c55e; display: flex; align-items: center; justify-content: center;">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="#22c55e" stroke="#22c55e" stroke-width="2">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </div>
                                
                                <!-- BG Image -->
                                <img src="{{ $story->image_url ?: 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=400&h=600&q=80' }}" alt="{{ $story->title }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
                                
                                <!-- Gradient Overlay -->
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(11,15,23,0.95) 100%); z-index: 2;"></div>
                                
                                <!-- Title -->
                                <div style="position: absolute; bottom: 14px; left: 14px; right: 14px; z-index: 3;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #ffffff; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $story->title }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Right Arrow (Desktop Only) -->
                    <button class="hidden md:flex" onclick="scrollStories(1)" style="position: absolute; right: -16px; z-index: 10; width: 36px; height: 36px; border-radius: 50%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.2rem; box-shadow: var(--shadow-sm);" onmouseover="this.style.background='var(--bg-card-hover)';" onmouseout="this.style.background='var(--bg-card)';">
                        &rsaquo;
                    </button>
                </div>
            </div>
        </section>

        <!-- Hide scrollbar utility for storiesContainer -->
        <style>
            #storiesContainer::-webkit-scrollbar {
                display: none;
            }
        </style>

        <script>
            function scrollStories(direction) {
                const container = document.getElementById('storiesContainer');
                if (container) {
                    container.scrollBy({ left: direction * 200, behavior: 'smooth' });
                }
            }
        </script>
    @endif

    <!-- ========================================================
         UNIFIED DASHBOARD SECTION (MATCHING SCREENSHOT 2)
         PLAYER RANKINGS, MOST POPULAR PLAYERS, POINTS TABLE & BIRTHDAYS
         ======================================================== -->
    <section id="rankings-dashboard" class="dashboard-rankings-section">
        <div class="container">
            <div class="dashboard-rankings-grid">
                
                <!-- LEFT COLUMN: PLAYER RANKINGS & MOST POPULAR PLAYERS -->
                <div style="display: flex; flex-direction: column; gap: 28px;">
                    
                    <!-- 1. PLAYER RANKINGS PANEL -->
                    <div>
                        <div class="section-header" style="margin-bottom: 12px;">
                            <div class="section-title">
                                <span class="section-title-icon">📊</span>
                                <span>PLAYER RANKINGS</span>
                            </div>
                            <a href="{{ route('stats') }}" class="view-all-link">FULL RANKINGS &rarr;</a>
                        </div>

                        <div class="dashboard-panel-card">
                            <div class="rankings-subcolumns-grid">
                                <!-- Subcol 1: BATTING — MOST RUNS -->
                                <div>
                                    <div class="rankings-subcol-title">BATTING &mdash; MOST RUNS</div>
                                    <div style="display: flex; flex-direction: column;">
                                        @forelse($battingRankings->take(10) as $bat)
                                            <a href="{{ route('stats', ['type' => 'batting']) }}" class="rankings-list-item">
                                                <div class="rankings-rank-num">{{ $bat->rank_num }}</div>
                                                <div class="rankings-avatar-badge" style="{{ $bat->rank_num == 1 ? 'border-color:#a855f7; color:#c084fc;' : ($bat->rank_num == 2 ? 'border-color:#f59e0b; color:#fbbf24;' : ($bat->rank_num == 3 ? 'border-color:#22c55e; color:#4ade80;' : ($bat->rank_num == 4 ? 'border-color:#38bdf8; color:#38bdf8;' : ''))) }}">
                                                    {{ $bat->badge_text ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $bat->player_name), 0, 3)) }}
                                                </div>
                                                <div class="rankings-player-name">{{ $bat->player_name }}</div>
                                                <div class="rankings-stat-val">{{ number_format($bat->stat_value) }}</div>
                                            </a>
                                        @empty
                                            <p style="color:var(--text-dim);font-size:0.85rem;padding:12px 0;">No batting rankings recorded.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Subcol 2: BOWLING — MOST WICKETS -->
                                <div>
                                    <div class="rankings-subcol-title">BOWLING &mdash; MOST WICKETS</div>
                                    <div style="display: flex; flex-direction: column;">
                                        @forelse($bowlingRankings->take(10) as $bowl)
                                            <a href="{{ route('stats', ['type' => 'bowling']) }}" class="rankings-list-item">
                                                <div class="rankings-rank-num">{{ $bowl->rank_num }}</div>
                                                <div class="rankings-avatar-badge" style="{{ $bowl->rank_num == 1 ? 'border-color:#38bdf8; color:#38bdf8;' : ($bowl->rank_num == 2 ? 'border-color:#eab308; color:#fde047;' : ($bowl->rank_num == 3 ? 'border-color:#ec4899; color:#f472b6;' : ($bowl->rank_num == 4 ? 'border-color:#a855f7; color:#c084fc;' : ''))) }}">
                                                    {{ $bowl->badge_text ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $bowl->player_name), 0, 3)) }}
                                                </div>
                                                <div class="rankings-player-name">{{ $bowl->player_name }}</div>
                                                <div class="rankings-stat-val">{{ number_format($bowl->stat_value) }}</div>
                                            </a>
                                        @empty
                                            <p style="color:var(--text-dim);font-size:0.85rem;padding:12px 0;">No bowling rankings recorded.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. MOST POPULAR PLAYERS PANEL -->
                    <div>
                        <div class="section-header" style="margin-bottom: 12px;">
                            <div class="section-title">
                                <span class="section-title-icon">🔥</span>
                                <span>MOST POPULAR PLAYERS</span>
                            </div>
                            <a href="{{ route('compare') }}" class="view-all-link">COMPARE PLAYERS &rarr;</a>
                        </div>

                        <div class="popular-players-bar-grid">
                            @foreach($popularPlayers->take(3) as $pop)
                                <a href="{{ route('player.profile', $pop->id) }}" class="popular-players-bar-card">
                                    <div style="position: relative; width: 48px; height: 48px; margin: 0 auto 10px;">
                                        @if(!empty($pop->profile_image))
                                            <img src="{{ $pop->profile_image }}" alt="{{ $pop->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        @endif
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--bg-card-secondary); border: 1.5px solid var(--border-color); color: var(--primary-text); display: {{ !empty($pop->profile_image) ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                            {{ $pop->initials ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $pop->name), 0, 3)) }}
                                        </div>
                                    </div>
                                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px;">{{ $pop->name }}</div>
                                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                                        {{ $pop->role ?: 'ALL-ROUNDER' }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: POINTS TABLE & PLAYER BIRTHDAYS -->
                <div style="display: flex; flex-direction: column; gap: 28px;">
                    
                    <!-- 1. POINTS TABLE PANEL -->
                    <div>
                        <div class="section-header" style="margin-bottom: 12px;">
                            <div class="section-title">
                                <span>POINTS TABLE</span>
                            </div>
                        </div>

                        <div class="dashboard-panel-card">
                            <div class="points-widget-header">{{ $pointsTable->first()->tournament_name ?? 'ICC World Cup 2026' }}</div>
                            <table class="points-widget-table">
                                <thead>
                                    <tr>
                                        <th>TEAM</th>
                                        <th>P</th>
                                        <th>W</th>
                                        <th>PTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pointsTable as $pt)
                                        <tr>
                                            <td>{{ $pt->team_code }}</td>
                                            <td>{{ $pt->played }}</td>
                                            <td>{{ $pt->won }}</td>
                                            <td style="font-weight: 900; color: var(--text-main);">{{ $pt->points }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="text-align:center;color:var(--text-dim);padding:14px;">No standings recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. PLAYER BIRTHDAYS PANEL -->
                    <div>
                        <div class="section-header" style="margin-bottom: 12px;">
                            <div class="section-title">
                                <span class="section-title-icon">🎂</span>
                                <span>PLAYER BIRTHDAYS</span>
                            </div>
                            <a href="{{ route('player.birthdays') }}" class="view-all-link">CALENDAR &rarr;</a>
                        </div>

                        <div class="dashboard-panel-card" style="padding: 12px 18px;">
                            <div style="display: flex; flex-direction: column;">
                                @forelse($playerBirthdays->take(4) as $pb)
                                    <a href="{{ route('player.profile', $pb->id) }}" class="birthday-list-item" style="text-decoration: none;">
                                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                            <div style="position: relative; width: 34px; height: 34px; flex-shrink: 0;">
                                                @if(!empty($pb->profile_image))
                                                    <img src="{{ $pb->profile_image }}" alt="{{ $pb->name }}" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-color);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                @endif
                                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--primary-text); display: {{ !empty($pb->profile_image) ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-weight: 800; font-size: 0.7rem;">
                                                    {{ $pb->initials ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $pb->name), 0, 2)) }}
                                                </div>
                                            </div>
                                            <div style="min-width: 0;">
                                                <div style="font-weight: 800; font-size: 0.86rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $pb->name }}
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-dim); margin-top: 1px;">
                                                    {{ $pb->birthday_date_text }} &bull; turns {{ $pb->turning_age }}
                                                </div>
                                            </div>
                                        </div>

                                        <div style="flex-shrink: 0;">
                                            @if($pb->is_today_birthday)
                                                <span class="birthday-tag-today">TODAY</span>
                                            @elseif($pb->days_until_birthday === 1)
                                                <span class="birthday-tag-days" style="color: #f59e0b; border-color: rgba(245,158,11,0.3);">1D</span>
                                            @else
                                                <span class="birthday-tag-days">{{ $pb->days_until_birthday }}D</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <p style="color:var(--text-dim);font-size:0.85rem;padding:12px 0;">No upcoming birthdays.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    @if($glossaryTerms->isNotEmpty())
        <!-- ========================================================
             SECTION 6.2: GLOSSARY OF CRICKET TERMS
             ======================================================== -->
        <section class="glossary-section" style="padding: 32px 0 48px; background: var(--bg-main);">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; margin-bottom: 24px;">
                    <div class="section-title" style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: #22c55e; font-size: 1.2rem;">📖</span>
                        <span style="color: var(--text-main);">GLOSSARY OF CRICKET TERMS</span>
                    </div>
                    <a href="{{ route('glossary.all') }}" style="color: #22c55e; font-size: 0.85rem; font-weight: 800; text-decoration: none; letter-spacing: 0.05em;">FULL GLOSSARY</a>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 14px;">
                    @foreach($glossaryTerms as $term)
                        <a href="{{ route('glossary.show', $term->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(34, 197, 94, 0.4)'; this.style.background='var(--bg-card-hover)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-card)';">
                            <!-- Poster Image or Letter Badge -->
                            @if(!empty($term->poster_image))
                                <img src="{{ $term->poster_image }}" alt="{{ $term->term }}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border-color, #334155);" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 36px; height: 36px; border-radius: 6px; background: rgba(34, 197, 94, 0.15); color: #22c55e; align-items: center; justify-content: center; font-weight: 900; font-size: 0.95rem; flex-shrink: 0;">
                                    {{ strtoupper($term->letter ?: substr($term->term, 0, 1)) }}
                                </div>
                            @else
                                <div style="width: 36px; height: 36px; border-radius: 6px; background: rgba(34, 197, 94, 0.15); color: #22c55e; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.95rem; flex-shrink: 0;">
                                    {{ strtoupper($term->letter ?: substr($term->term, 0, 1)) }}
                                </div>
                            @endif

                            <!-- Details -->
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">
                                    {{ $term->term }}
                                </h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.45; margin: 0;">
                                    {{ $term->definition }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ========================================================
         SECTION 8: GRASSROOTS CTA BANNER
         ======================================================== -->
    <section class="grassroots-banner-section">
        <div class="container">
            <div class="grassroots-banner">
                <div class="grassroots-content">
                    <span class="grassroots-tag">GRASSROOTS</span>
                    <h2 class="grassroots-title">RUN YOUR CLUB CRICKET LIKE THE PROS</h2>
                    <p class="grassroots-desc">Register your team, create a series, add players, and score every ball &mdash; career stats build themselves on Laravel.</p>
                </div>
                <div class="grassroots-actions">
                    <a href="{{ route('local.dashboard') }}" class="btn-grassroots-primary">Add team / series / player</a>
                    <a href="{{ route('local.dashboard') }}" class="btn-grassroots-secondary">Open scorer</a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
function switchSeriesTab(tabName, btn) {
    document.querySelectorAll('.series-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.series-tab-content').forEach(c => c.style.display = 'none');
    const target = document.getElementById('series-tab-' + tabName);
    if (target) target.style.display = 'grid';

    if (btn && typeof btn.scrollIntoView === 'function') {
        btn.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const activeTab = document.querySelector('.series-tab.active');
    if (activeTab && typeof activeTab.scrollIntoView === 'function') {
        activeTab.scrollIntoView({ behavior: 'auto', inline: 'start', block: 'nearest' });
    }
});

function toggleSummary(btn) {
    const parent = btn.parentElement;
    const shortText = parent.querySelector('.summary-short');
    const fullText = parent.querySelector('.summary-full');
    
    if (fullText.style.display === 'none') {
        fullText.style.display = 'inline';
        shortText.style.display = 'none';
        btn.innerText = 'Read Less';
    } else {
        fullText.style.display = 'none';
        shortText.style.display = 'inline';
        btn.innerText = 'Read More';
    }
}
</script>
@endsection
