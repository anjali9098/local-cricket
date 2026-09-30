@extends(isset($isLocal) && $isLocal ? 'layouts.local' : 'layouts.admin')

@php
    $seoName        = $tournament->name ?? 'Local Cricket Tournament';
    $seoCity        = $tournament->city ?? '';
    $seoState       = $tournament->state ?? '';
    $seoFormat      = $tournament->format ?? 'T20';
    $seoStatus      = ucfirst($tournament->status ?? 'ongoing');
    $seoVenue       = $tournament->venue ?? '';
    $seoDesc        = $tournament->description ?? '';
    $seoLocation    = trim(implode(', ', array_filter([$seoCity, $seoState])));
    $seoStartDate   = $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->toDateString() : null;
    $seoEndDate     = $tournament->end_date   ? \Carbon\Carbon::parse($tournament->end_date)->toDateString()   : null;
    $seoTeamCount   = $tournament->teams->count();
    $seoMatchCount  = $tournament->matches->count();

    $pageTitle  = $seoName . ($seoLocation ? ' — ' . $seoLocation : '') . ' | ' . $seoFormat . ' Cricket | CricketKaScore';
    $metaDesc   = 'View live scores, points table and match schedule for ' . $seoName
                . ($seoLocation ? ' in ' . $seoLocation : '')
                . ' — a ' . $seoFormat . ' local cricket tournament on CricketKaScore.'
                . ($seoDesc ? ' ' . Str::limit($seoDesc, 80) : '');
    $metaKw     = $seoName . ', local cricket tournament, ' . $seoFormat . ' cricket'
                . ($seoCity ? ', ' . $seoCity . ' cricket' : '')
                . ($seoState ? ', ' . $seoState . ' cricket tournament' : '')
                . ', grassroots cricket, gully cricket, CricketKaScore';
    $canonicalUrl = (isset($isLocal) && $isLocal) ? route('local.tournament.preview', $tournament->id) : route('admin.tournament.preview', $tournament->id);
    $dashboardUrl = (isset($isLocal) && $isLocal) ? route('local.dashboard') : route('admin.dashboard');
    $dashboardName = (isset($isLocal) && $isLocal) ? 'Local Dashboard' : 'Admin Dashboard';
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKw)
@section('canonical_url', $canonicalUrl)
@section('og_title', $seoName . ($seoLocation ? ' — ' . $seoLocation : '') . ' | ' . $seoFormat . ' Tournament')
@section('og_description', $metaDesc)
@section('og_url', $canonicalUrl)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'SportsEvent',
    'name'        => $seoName,
    'description' => $metaDesc,
    'url'         => $canonicalUrl,
    'sport'       => 'Cricket',
    'eventStatus' => $seoStatus === 'Completed'
                        ? 'https://schema.org/EventScheduled'
                        : 'https://schema.org/EventScheduled',
    'startDate'   => $seoStartDate,
    'endDate'     => $seoEndDate,
    'location'    => $seoVenue || $seoLocation ? array_filter([
        '@type'   => 'Place',
        'name'    => $seoVenue ?: ($seoLocation ?: null),
        'address' => $seoLocation ?: null,
    ]) : null,
    'organizer'   => [
        '@type' => 'Organization',
        'name'  => 'CricketKaScore',
        'url'   => url('/'),
    ],
    'breadcrumb'  => [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',          'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $dashboardName,   'item' => $dashboardUrl],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $seoName,        'item' => $canonicalUrl],
        ]),
    ],
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')
<!-- Header Banner -->
<div class="tournament-hero-banner" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 36px 20px; transition: background-color 0.3s ease, border-color 0.3s ease;">
    <div class="max-w-6xl mx-auto">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 0.82rem; font-weight: 700; flex-wrap: wrap;">
            <span style="background: #f97316; color: #ffffff; padding: 3px 12px; border-radius: 9999px; text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em;">{{ $tournament->format }}</span>
            <span style="border: 1px solid var(--border-color); background: var(--bg-card-secondary); color: var(--text-muted); padding: 3px 12px; border-radius: 9999px; text-transform: lowercase; font-size: 0.72rem; font-weight: 700;">{{ $tournament->status }}</span>
            <span style="color: var(--text-dim); display: inline-flex; align-items: center; gap: 4px;"><span>📍</span> {{ $tournament->city ?? 'Unknown' }}</span>
        </div>
        
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; letter-spacing: -0.02em; margin: 0 0 10px 0; line-height: 1.2;">
            {{ $tournament->name }}
        </h1>
        
        <div style="font-size: 0.88rem; color: var(--text-muted); font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span>📅</span> {{ \Carbon\Carbon::parse($tournament->start_date)->format('n/j/Y') }} &mdash; {{ \Carbon\Carbon::parse($tournament->end_date)->format('n/j/Y') }}
        </div>
        
        <div style="font-size: 0.85rem; color: var(--text-dim); max-width: 680px; font-weight: 500; line-height: 1.5;">
            {{ $tournament->description ?? 'Official tournament page.' }}
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 pb-28">
    
    <!-- Live Now Section -->
    <div class="mb-8">
        <h3 style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 8px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.04em;">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ef4444; animation: pulse 2s infinite;"></span>
            LIVE NOW
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $liveMatches = $tournament->matches->whereIn('status', ['ongoing', 'live']);
            @endphp
            
            @if($liveMatches->count() > 0)
                @foreach($liveMatches as $liveMatch)
                <a href="{{ route('matches.detail', $liveMatch->id) }}" style="display: block; background: var(--bg-card); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; padding: 18px; text-decoration: none; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="display: inline-block; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">&bull; LIVE</div>
                    <div style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); text-transform: uppercase; margin-bottom: 8px;">
                        {{ $liveMatch->team1->name ?? 'TBA' }} <span style="color: #ef4444;">VS</span> {{ $liveMatch->team2->name ?? 'TBA' }}
                    </div>
                    <div style="font-size: 0.82rem; color: #38bdf8; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                        <span>Click to view live scorer</span> &rarr;
                    </div>
                </a>
                @endforeach
            @else
                <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; color: var(--text-muted); font-weight: 600; font-size: 0.88rem;">
                    No live matches at the moment.
                </div>
            @endif
        </div>
    </div>

    <!-- Points Table -->
    <div>
        <h3 style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 8px;">
            <span>🏆</span> POINTS TABLE
        </h3>
        
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="overflow-x: auto;">
                <table class="cricket-table" style="min-width: 500px; width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-card-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-transform: uppercase;">Team</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">P</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">W</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">L</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">Pts</th>
                            <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 900; color: var(--text-muted); text-align: center; text-transform: uppercase;">NRR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointsTable as $row)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 800; color: var(--text-main); font-size: 0.9rem;">{{ $row['team']->name }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $row['p'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #2563eb; text-align: center; font-size: 0.88rem;">{{ $row['w'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: #ef4444; text-align: center; font-size: 0.88rem;">{{ $row['l'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 900; color: #2563eb; text-align: center; font-size: 0.92rem;">{{ $row['pts'] }}</td>
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--text-muted); text-align: center; font-size: 0.88rem;">{{ $row['nrr'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding: 32px 18px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">No teams registered yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
