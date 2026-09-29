@extends('layouts.app')

@section('pageTitle', 'Cricket Statistics, ICC Team Standings, Batting & Bowling Rankings | CricketKaScore')
@section('meta_description', 'Explore comprehensive cricket statistics, ICC team standings, points tables, batting leaderboards for most runs, bowling wicket-takers, net run rate (NRR), and player records on CricketKaScore.')
@section('meta_keywords', 'cricket statistics, team standings, ICC team rankings, batting rankings, bowling rankings, most runs cricket, most wickets cricket, cricket points table, player stats, net run rate, CricketKaScore rankings')
@section('canonical_url', route('stats'))
@section('og_type', 'website')
@section('og_title', 'Cricket Statistics, ICC Team Standings, Batting & Bowling Rankings | CricketKaScore')
@section('og_description', 'Explore comprehensive cricket statistics, ICC team standings, points tables, batting leaderboards, and bowling records on CricketKaScore.')
@section('og_url', route('stats'))

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'CollectionPage',
    'name' => 'Cricket Statistics, ICC Team Standings, Batting & Bowling Rankings',
    'url' => route('stats'),
    'description' => 'Official cricket statistics, team standings, batting leaderboards, bowling records, and player analytics.',
    'breadcrumb' => [
        chr(64) . 'type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/')
            ],
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 2,
                'name' => 'Statistics & Rankings',
                'item' => route('stats')
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container py-6 sm:py-10">
    <!-- Header with Primary H1 & On-Page SEO Description -->
    <div class="section-header mb-6 flex flex-col gap-3 pb-4 border-b w-full" style="border-color: var(--border-color); align-items: flex-start !important; text-align: left !important;">
        <div style="text-align: left !important;">
            <h1 class="text-xl sm:text-2xl font-black uppercase tracking-tight m-0" style="color: var(--text-main); text-align: left !important;">CRICKET STATISTICS, ICC RANKINGS &amp; LEADERBOARDS</h1>
            <p class="text-xs sm:text-sm mt-2 leading-relaxed" style="color: var(--text-muted); max-width: 840px; text-align: left !important;">
                Explore comprehensive cricket statistics, ICC team standings, points tables, batting leaderboards for most runs, bowling wicket-takers, net run rate (NRR), and player records on CricketKaScore.
            </p>
        </div>
    </div>

    <!-- Section 1: Team Rankings Table (H2) -->
    <section class="mb-10">
        <h2 class="text-base sm:text-lg font-bold mb-3.5 uppercase flex items-center gap-2" style="color: var(--text-main);">
            <span>🏆</span> Global Team Standings, Points Table &amp; NRR
        </h2>
        <div class="table-responsive-wrapper">
            <table class="cricket-table min-w-[540px]">
                <thead>
                    <tr>
                        <th class="rank-col">RANK</th>
                        <th class="team-col">TEAM</th>
                        <th>MAT</th>
                        <th>WON</th>
                        <th>NRR</th>
                        <th class="pts-col">POINTS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamRankings as $tr)
                        <tr>
                            <td class="rank-col">{{ $tr->rank_num }}</td>
                            <td class="team-col">
                                <strong>{{ $tr->team_name }}</strong>
                            </td>
                            <td>{{ $tr->matches_played }}</td>
                            <td>{{ $tr->won }}</td>
                            <td>{{ $tr->nrr }}</td>
                            <td class="pts-col">{{ $tr->points }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-sm" style="color: var(--text-muted);">No team rankings recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 2: Player Rankings Dual Columns (H2) -->
    <section class="mb-10">
        <h2 class="text-base sm:text-lg font-bold mb-3.5 uppercase flex items-center gap-2" style="color: var(--text-main);">
            <span>🏏</span> Player Leaderboards, Batting &amp; Bowling Rankings
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Subcolumn 1: Batting (H3) -->
            <div class="ranking-box rounded-2xl p-4 sm:p-5" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <h3 class="ranking-box-header text-xs sm:text-sm font-extrabold uppercase tracking-wider mb-4 pb-2.5 flex items-center justify-between m-0" style="color: var(--text-main); border-bottom: 1px solid var(--border-color);">
                    <span>BATTING &mdash; MOST RUNS, STRIKE RATES &amp; AVERAGES</span>
                    <span class="text-emerald-400 text-xs font-bold">RUNS</span>
                </h3>
                <div class="flex flex-col gap-2">
                    @forelse($battingRankings as $br)
                        <div class="ranking-item flex justify-between items-center py-2 text-xs sm:text-sm" style="border-bottom: 1px solid var(--border-color);">
                            <div class="ranking-item-left flex items-center gap-2.5">
                                <span class="rank-num font-black w-5" style="color: var(--text-muted);">{{ $br->rank_num }}</span>
                                <div class="player-badge text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">{{ $br->badge_text }}</div>
                                <span class="player-name font-semibold" style="color: var(--text-main);">{{ $br->player_name }}</span>
                            </div>
                            <span class="stat-val font-black text-emerald-400">{{ number_format($br->stat_value) }}</span>
                        </div>
                    @empty
                        <p class="text-xs py-4 text-center" style="color: var(--text-muted);">No batting statistics recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Subcolumn 2: Bowling (H3) -->
            <div class="ranking-box rounded-2xl p-4 sm:p-5" style="background: var(--bg-card); border: 1px solid var(--border-color);">
                <h3 class="ranking-box-header text-xs sm:text-sm font-extrabold uppercase tracking-wider mb-4 pb-2.5 flex items-center justify-between m-0" style="color: var(--text-main); border-bottom: 1px solid var(--border-color);">
                    <span>BOWLING &mdash; MOST WICKETS, ECONOMY &amp; 5-WICKET HAULS</span>
                    <span class="text-sky-400 text-xs font-bold">WKTS</span>
                </h3>
                <div class="flex flex-col gap-2">
                    @forelse($bowlingRankings as $bow)
                        <div class="ranking-item flex justify-between items-center py-2 text-xs sm:text-sm" style="border-bottom: 1px solid var(--border-color);">
                            <div class="ranking-item-left flex items-center gap-2.5">
                                <span class="rank-num font-black w-5" style="color: var(--text-muted);">{{ $bow->rank_num }}</span>
                                <div class="player-badge text-xs font-bold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded">{{ $bow->badge_text }}</div>
                                <span class="player-name font-semibold" style="color: var(--text-main);">{{ $bow->player_name }}</span>
                            </div>
                            <span class="stat-val font-black text-sky-400">{{ number_format($bow->stat_value) }}</span>
                        </div>
                    @empty
                        <p class="text-xs py-4 text-center" style="color: var(--text-muted);">No bowling statistics recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: SEO Overview & Cricket Analytics Section (H2) -->
    <section class="mt-12 p-6 sm:p-8 rounded-2xl border" style="background: var(--bg-card); border-color: var(--border-color);">
        <h2 class="text-base sm:text-lg font-bold mb-3 flex items-center gap-2 m-0" style="color: var(--text-main);">
            <span>📊</span> Comprehensive Cricket Records, Player Analytics &amp; Tournament Insights
        </h2>
        <p class="text-xs sm:text-sm leading-relaxed mb-4 mt-2" style="color: var(--text-muted);">
            CricketKaScore delivers in-depth cricket records, tournament points tables, Net Run Rate (NRR) calculations, and player milestone trackers across international, domestic, and franchise leagues. All statistics update dynamically from real-time ball-by-ball scorecards to give fans, analysts, and fantasy cricket players accurate historical performance data.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t" style="border-color: var(--border-color);">
            <div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1 m-0">ICC Team Standings, Net Run Rate (NRR) &amp; Points System</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">Official league standings calculated with matches won, lost, tied, and decimal net run rate.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider mb-1 m-0">Batting Statistics, Century Milestones &amp; Partnership Records</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">Top run-scorers, batting averages, boundary counts, strike rates, and highest individual scores.</p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-1 m-0">Bowling Economy, Strike Rates &amp; Match-Winning Spells</h3>
                <p class="text-xs mb-0 mt-1" style="color: var(--text-muted);">Leading wicket-takers, bowling economy rates, maidens, five-wicket hauls, and best match figures.</p>
            </div>
        </div>
    </section>
</main>
@endsection
