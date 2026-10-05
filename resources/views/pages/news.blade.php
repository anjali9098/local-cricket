@extends('layouts.app')

@php
    $activeTypeVal = $activeType ?? 'news';
    $catVal = request('cat', '');

    // Map active type to dedicated route name
    $baseRoute = match($activeTypeVal) {
        'prediction', 'predictions' => 'predictions',
        'fantasy', 'fantasy_tips'   => 'fantasy',
        'preview', 'previews'       => 'previews',
        'article', 'articles'       => 'articles',
        default                     => 'news',
    };

    if ($activeTypeVal === 'article' || $activeTypeVal === 'articles') {
        $seoTitle     = 'Cricket Articles — In-Depth Analysis, Features & Columns | CricketKaScore';
        $seoDesc      = 'Read in-depth cricket articles, editorial columns, feature stories, player profiles, and expert cricket analysis on CricketKaScore.';
        $seoKeywords  = 'cricket articles, cricket analysis, cricket editorial, cricket features, cricket columns, player profiles, cricket opinion, CricketKaScore articles';
        $h1Text       = 'CRICKET ARTICLES';
    } elseif ($activeTypeVal === 'prediction' || $activeTypeVal === 'predictions') {
        $seoTitle     = 'Match Predictions — Today Cricket Forecast, Pitch Reports & Probability | CricketKaScore';
        $seoDesc      = 'Expert cricket match predictions, today match forecasts, pitch reports, toss analysis, and win probability for all cricket matches on CricketKaScore.';
        $seoKeywords  = 'cricket match prediction, today match prediction, cricket forecast, pitch report, toss prediction, win probability, dream11 prediction, CricketKaScore predictions';
        $h1Text       = 'MATCH PREDICTIONS';
    } elseif ($activeTypeVal === 'fantasy' || $activeTypeVal === 'fantasy_tips') {
        $seoTitle     = 'Fantasy Cricket Tips — Dream11 Picks, Captaincy & Best XI | CricketKaScore';
        $seoDesc      = 'Get expert fantasy cricket tips, Dream11 team suggestions, best captaincy picks, player form guide, and winning fantasy XI combinations on CricketKaScore.';
        $seoKeywords  = 'fantasy cricket tips, dream11 tips, dream11 team today, best captain pick, fantasy XI, fantasy cricket strategy, player form, CricketKaScore fantasy';
        $h1Text       = 'FANTASY CRICKET TIPS';
    } elseif ($activeTypeVal === 'preview' || $activeTypeVal === 'previews') {
        $seoTitle     = 'Match Previews — Upcoming Fixtures, Team News & Head-to-Head Records | CricketKaScore';
        $seoDesc      = 'Upcoming cricket match previews featuring fixture breakdowns, team news, head-to-head records, venue conditions, and key player battles on CricketKaScore.';
        $seoKeywords  = 'cricket match preview, upcoming match preview, team news, head to head cricket, venue conditions, key players, cricket fixture, CricketKaScore previews';
        $h1Text       = 'MATCH PREVIEWS';
    } else {
        $seoTitle     = 'Cricket News — Breaking Headlines, Match Reports & Latest Updates | CricketKaScore';
        $seoDesc      = 'Stay updated with the latest cricket news, breaking headlines, match reports, player transfers, series announcements, and cricket updates from around the world on CricketKaScore.';
        $seoKeywords  = 'cricket news, latest cricket news, cricket breaking news, match report, cricket updates, cricket headlines, IPL news, international cricket news, CricketKaScore news';
        $h1Text       = 'CRICKET NEWS';
    }

    if ($catVal === 'INTERNATIONAL') {
        $seoTitle = 'International Cricket ' . ucfirst($activeTypeVal) . ' — Latest Updates | CricketKaScore';
        $seoDesc  = 'International cricket ' . strtolower($activeTypeVal) . ', match results, series updates, player news and analysis from Test, ODI, and T20 International cricket on CricketKaScore.';
    } elseif ($catVal === 'IPL') {
        $seoTitle = 'IPL ' . ucfirst($activeTypeVal) . ' — Indian Premier League Updates & Analysis | CricketKaScore';
        $seoDesc  = 'IPL ' . strtolower($activeTypeVal) . ', Indian Premier League match results, team updates, player transfers, auction news, and analysis on CricketKaScore.';
    } elseif ($catVal === 'DOMESTIC') {
        $seoTitle = 'Domestic Cricket ' . ucfirst($activeTypeVal) . ' — Ranji Trophy, Vijay Hazare & More | CricketKaScore';
        $seoDesc  = 'Domestic cricket ' . strtolower($activeTypeVal) . ' covering Ranji Trophy, Vijay Hazare Trophy, Syed Mushtaq Ali, and other domestic cricket tournaments on CricketKaScore.';
    } elseif ($catVal === 'LOCAL') {
        $seoTitle = 'Local Cricket ' . ucfirst($activeTypeVal) . ' — Grassroots Tournaments & Club Cricket | CricketKaScore';
        $seoDesc  = 'Local cricket ' . strtolower($activeTypeVal) . ', grassroots tournament results, club cricket scores, and community cricket updates on CricketKaScore.';
    }

    $canonicalUrl = $catVal ? route($baseRoute, ['cat' => $catVal]) : route($baseRoute);
@endphp

@section('pageTitle', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords', $seoKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDesc)
@section('og_url', $canonicalUrl)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'graph' => [
        [
            chr(64) . 'type' => 'CollectionPage',
            chr(64) . 'id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => $seoTitle,
            'description' => $seoDesc,
            'isPartOf' => [
                chr(64) . 'id' => url('/') . '/#website'
            ],
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
                        'name' => $h1Text,
                        'item' => $canonicalUrl
                    ]
                ]
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container py-6 sm:py-8">

    <!-- News Header -->
    <div class="section-header mb-5 flex flex-col gap-3 pb-4 border-b w-full" style="border-color: var(--border-color); align-items: flex-start !important; text-align: left !important;">
        <div style="text-align: left !important;">
            <h1 class="text-xl sm:text-2xl font-black uppercase tracking-tight m-0" style="color: var(--text-main); text-align: left !important;">
                {{ $h1Text }}
            </h1>
            <p class="mt-2 leading-relaxed" style="color: var(--text-muted); font-size: 0.875rem; max-width: 840px; margin: 6px 0 0 0; text-align: left !important;">
                {{ $seoDesc }}
            </p>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px;">
        <a href="{{ route($baseRoute) }}" class="series-tab {{ empty(request('cat')) ? 'active' : '' }}">All Categories</a>
        <a href="{{ route($baseRoute, ['cat' => 'INTERNATIONAL']) }}" class="series-tab {{ request('cat') === 'INTERNATIONAL' ? 'active' : '' }}">International</a>
        <a href="{{ route($baseRoute, ['cat' => 'IPL']) }}" class="series-tab {{ request('cat') === 'IPL' ? 'active' : '' }}">IPL</a>
        <a href="{{ route($baseRoute, ['cat' => 'DOMESTIC']) }}" class="series-tab {{ request('cat') === 'DOMESTIC' ? 'active' : '' }}">Domestic</a>
        <a href="{{ route($baseRoute, ['cat' => 'LOCAL']) }}" class="series-tab {{ request('cat') === 'LOCAL' ? 'active' : '' }}">Local</a>
    </div>

    <!-- Semantic H2 for SEO - visually hidden but helps search engines -->
    @php
        $h2Text = 'Latest Cricket News, Breaking Headlines & Match Reports';
        if ($activeTypeVal === 'article') $h2Text = 'Latest Cricket Articles, Analysis & Editorial Features';
        elseif ($activeTypeVal === 'prediction') $h2Text = 'Latest Match Predictions, Pitch Reports & Cricket Forecasts';
        elseif ($activeTypeVal === 'fantasy') $h2Text = 'Latest Fantasy Cricket Tips, Dream11 Picks & Best XI Suggestions';
        elseif ($activeTypeVal === 'preview') $h2Text = 'Latest Match Previews, Team News & Head-to-Head Analysis';
    @endphp
    <h2 style="font-size: 0; height: 0; margin: 0; overflow: hidden; position: absolute;">{{ $h2Text }}</h2>


    <!-- 2-Column News & Articles Grid -->
    <div class="news-grid-2col" style="gap: 20px;">

        @forelse($allNewsItems as $item)
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg, 14px); padding: 22px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: transform 0.2s, box-shadow 0.2s;"
                onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                
                <div>
                    <!-- Card Top Badge Row -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span class="card-tag {{ ($item->badge_label ?? $item->tag ?? 'NEWS') === 'FANTASY TIP' || ($item->badge_label ?? '') === 'FANTASY' ? 'fantasy' : 'prediction' }}">
                            {{ $item->badge_label ?? $item->tag ?? $item->category ?? 'NEWS' }}
                        </span>
                        @if(!empty($item->category) && $item->category !== ($item->badge_label ?? ''))
                            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                                {{ $item->category }}
                            </span>
                        @endif
                    </div>

                    @php
                        $detailRoute = $item->url ?? null;
                    @endphp

                    @if(!empty($item->image_url))
                        @if($detailRoute)
                            <a href="{{ $detailRoute }}" style="text-decoration: none; display: block;">
                                <div style="position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: 10px; overflow: hidden; margin-bottom: 12px; background: #0b1120; display: flex; align-items: center; justify-content: center;">
                                    <div style="position: absolute; inset: -10px; background-image: url('{{ $item->image_url }}'); background-size: cover; background-position: center; filter: blur(14px) brightness(0.35); opacity: 0.8; transform: scale(1.1); pointer-events: none;"></div>
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="position: relative; z-index: 1; width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'" loading="lazy">
                                </div>
                            </a>
                        @else
                            <div style="position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: 10px; overflow: hidden; margin-bottom: 12px; background: #0b1120; display: flex; align-items: center; justify-content: center;">
                                <div style="position: absolute; inset: -10px; background-image: url('{{ $item->image_url }}'); background-size: cover; background-position: center; filter: blur(14px) brightness(0.35); opacity: 0.8; transform: scale(1.1); pointer-events: none;"></div>
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="position: relative; z-index: 1; width: 100%; height: 100%; object-fit: cover; display: block;" loading="lazy">
                            </div>
                        @endif
                    @endif
                    
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0 0 10px 0; line-height: 1.4;">
                        @if($detailRoute)
                            <a href="{{ $detailRoute }}" style="color: var(--text-main); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#0284c7'" onmouseout="this.style.color='var(--text-main)'">
                                {{ $item->title }}
                            </a>
                        @else
                            {{ $item->title }}
                        @endif
                    </h3>

                    <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6;">
                        @php $summaryText = $item->summary ?? strip_tags($item->content ?? ''); @endphp
                        @if(strlen($summaryText) > 220)
                            <span class="summary-short">{{ Str::limit($summaryText, 220) }}</span>
                            <span class="summary-full" style="display: none;">{{ $summaryText }}</span>
                            <button onclick="toggleSummary(this)" style="background: none; border: none; color: #38bdf8; font-weight: 700; padding: 0; margin-left: 5px; cursor: pointer; font-size: 0.85rem; outline: none;">Read More</button>
                        @else
                            {{ $summaryText }}
                        @endif
                    </div>
                </div>

                <!-- Card Footer -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; font-size: 0.75rem; color: var(--text-dim); display: flex; justify-content: space-between; align-items: center;">
                    <span>CricketKaScore &bull; {{ $item->published_date ?? (isset($item->created_at) && $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('M d, Y') : 'Today') }}</span>
                    @if(!empty($item->read_time))
                        <span>⏱️ {{ $item->read_time }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 40px; text-align: center; grid-column: 1/-1;">
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1.05rem;">No items found in this section.</p>
                <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 6px;">Super Admin can publish content directly from the Super Admin panel.</p>
            </div>
        @endforelse
    </div>

</main>

<script>
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
