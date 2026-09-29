@php
    $effectiveStatus = $m->effective_status;
    $isLive = ($effectiveStatus === 'live');
    $isCompleted = ($effectiveStatus === 'completed');
    $isUpcoming = in_array($effectiveStatus, ['upcoming', 'scheduled']);

    $t1Name = $m->team1?->name ?? 'Team 1';
    $t1Code = $m->team1?->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $t1Name), 0, 3));
    $t1Logo = $m->team1?->logo_url ?: $m->team1?->logo;

    $t2Name = $m->team2?->name ?? 'Team 2';
    $t2Code = $m->team2?->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $t2Name), 0, 3));
    $t2Logo = $m->team2?->logo_url ?: $m->team2?->logo;

    $seriesName = $m->tournament?->name ?? ($m->level_type ? ($m->level_type . ' ' . ($m->match_type ?? 'Cricket')) : 'Cricket Series');
    $venueName = $m->venue?->name ?? 'Stadium TBA';
    if (!empty($m->venue?->city)) {
        $venueName .= ', ' . $m->venue->city;
    }
@endphp

<a href="{{ route('matches.detail', $m->id) }}" class="custom-match-card">
    <!-- Top Metadata: Series Name, Venue, Format Badge -->
    <div class="card-top-meta">
        <div class="card-series-info">
            <div class="card-series-title">
                <span class="card-series-bullet">•</span>
                <span>{{ $seriesName }}</span>
            </div>
            <div class="card-venue-text">
                <span class="card-series-bullet">•</span>
                <span>Venue - {{ $venueName }}</span>
            </div>
        </div>
        <div class="card-format-badge">
            {{ strtoupper($m->match_type ?? 'T20') }}
        </div>
    </div>

    <!-- 3-Column Match Content Grid: Left Team | Center Info | Right Team -->
    <div class="card-content-grid">
        <!-- TEAM 1 (Left) -->
        <div class="team-col-left">
            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                <span class="team-code-title" title="{{ $t1Name }}">{{ $t1Code }}</span>
                @if($isLive || $isCompleted)
                    <div class="team-score-badge">
                        {{ $m->team1_score }}/{{ $m->team1_wickets }}
                        @if($m->team1_overs > 0)
                            <span class="score-overs">({{ $m->team1_overs }} ov)</span>
                        @endif
                    </div>
                @endif
            </div>

            @if($t1Logo)
                <img src="{{ $t1Logo }}" alt="{{ $t1Name }}" class="team-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="team-logo-fallback" style="display: none;">{{ $t1Code }}</div>
            @else
                <div class="team-logo-fallback">{{ $t1Code }}</div>
            @endif
        </div>

        <!-- CENTER INFO: Status, Timing, Result, Where to Watch -->
        <div class="center-info-col">
            @if($isLive)
                <div class="match-status-title live-red">
                    <span class="live-dot-pulse"></span> LIVE
                </div>
            @elseif($isCompleted)
                <div class="match-status-title completed-green">
                    Completed
                </div>
            @else
                <div class="match-status-title upcoming-blue">
                    Upcoming
                </div>
            @endif

            <!-- Match Timing -->
            <div class="match-timing-str">
                Match starts at - {{ !empty($m->match_date) ? \Carbon\Carbon::parse($m->match_date)->format('D d-M-Y h:i A') : 'TBA' }}
            </div>

            <!-- Custom Note or Winning Result -->
            @if($isCompleted && !empty($m->winning_title))
                <div class="match-result-summary">
                    {{ $m->winning_title }}
                </div>
            @elseif($isLive && !empty($m->custom_note) && !str_contains($m->custom_note, '<p>'))
                <div class="match-result-summary" style="color: #38bdf8;">
                    {{ $m->custom_note }}
                </div>
            @endif

            <!-- WHERE TO WATCH (Click opens official streaming broadcaster) -->
            @php
                $wtwText = $m->where_to_watch ?? '';
                $channels = array_filter(array_map('trim', explode(',', $wtwText)));
                
                $getChannelUrl = function($chName) {
                    $lower = strtolower(trim($chName));
                    if (str_contains($lower, 'sony') || str_contains($lower, 'liv')) {
                        return 'https://www.sonyliv.com';
                    }
                    if (str_contains($lower, 'hotstar') || str_contains($lower, 'star')) {
                        return 'https://www.hotstar.com';
                    }
                    if (str_contains($lower, 'jio') || str_contains($lower, 'sports18')) {
                        return 'https://www.jiocinema.com';
                    }
                    if (str_contains($lower, 'fancode')) {
                        return 'https://www.fancode.com';
                    }
                    if (str_contains($lower, 'sky')) {
                        return 'https://www.skysports.com';
                    }
                    if (str_contains($lower, 'willow')) {
                        return 'https://www.willow.tv';
                    }
                    if (str_contains($lower, 'supersport')) {
                        return 'https://supersport.com';
                    }
                    return 'https://www.google.com/search?q=' . urlencode($chName . ' live cricket stream');
                };

                $firstCh = !empty($channels) ? reset($channels) : 'Official Streaming';
                $firstUrl = !empty($channels) ? $getChannelUrl($firstCh) : 'https://www.hotstar.com';
            @endphp
            <div class="where-to-watch-tag" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $firstUrl }}', '_blank');" title="Click to watch on {{ $firstCh }} (opens streaming site)">
                <span>📺</span>
                <span class="wtw-label">Where to watch:</span>
                <span class="wtw-channels">
                    @foreach($channels as $idx => $ch)
                        @php $chUrl = $getChannelUrl($ch); @endphp
                        <span class="wtw-channel-link" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $chUrl }}', '_blank');" title="Watch live on {{ $ch }} (opens official site)">{{ $ch }}</span>@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
        </div>

        <!-- TEAM 2 (Right) -->
        <div class="team-col-right">
            @if($t2Logo)
                <img src="{{ $t2Logo }}" alt="{{ $t2Name }}" class="team-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="team-logo-fallback" style="display: none;">{{ $t2Code }}</div>
            @else
                <div class="team-logo-fallback">{{ $t2Code }}</div>
            @endif

            <div style="display: flex; flex-direction: column; align-items: flex-start;">
                <span class="team-code-title" title="{{ $t2Name }}">{{ $t2Code }}</span>
                @if($isLive || $isCompleted)
                    <div class="team-score-badge">
                        @if($m->team2_score > 0 || $m->team2_wickets > 0 || $m->team2_overs > 0)
                            {{ $m->team2_score }}/{{ $m->team2_wickets }}
                            @if($m->team2_overs > 0)
                                <span class="score-overs">({{ $m->team2_overs }} ov)</span>
                            @endif
                        @else
                            <span class="score-overs">Yet to bat</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</a>
