@extends('layouts.app')

@php
    $newsTitle = trim($news->h1_heading ?: $news->title);
    $pageTitle = $newsTitle . ' — CricketKaScore';
    $metaDesc = trim($news->meta_description ?: ($news->summary ? \Illuminate\Support\Str::limit(strip_tags($news->summary), 160) : 'Read latest cricket news updates and breaking stories on CricketKaScore.'));
    $metaKeywords = $news->keywords ?: ($newsTitle . ', cricket news, latest cricket news, CricketKaScore');
    $canonicalUrl = $news->url;
    $ogImage = $news->image_url ?: asset('images/logo.png');
    $authorName = !empty($news->author) ? $news->author : (!empty($news->user?->name) ? $news->user->name : 'Anjali Malviya');
    $publishedDate = $news->created_at ? \Carbon\Carbon::parse($news->created_at)->format('l, M d, Y') : ($news->published_date ?: 'Today');
    $updatedDate = $news->updated_at ? \Carbon\Carbon::parse($news->updated_at)->format('M d, Y h:i A') : $publishedDate;
    $publishedIso = $news->created_at ? \Carbon\Carbon::parse($news->created_at)->toIso8601String() : now()->toIso8601String();
    $modifiedIso = $news->updated_at ? \Carbon\Carbon::parse($news->updated_at)->toIso8601String() : $publishedIso;
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'article')
@section('og_title', $pageTitle)
@section('og_description', $metaDesc)
@section('og_url', $canonicalUrl)
@section('og_image', $ogImage)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode([
    chr(64) . 'context' => 'https://schema.org',
    chr(64) . 'type' => 'NewsArticle',
    'headline' => $newsTitle,
    'description' => $metaDesc,
    'image' => [$ogImage],
    'datePublished' => $publishedIso,
    'dateModified' => $modifiedIso,
    'mainEntityOfPage' => [
        chr(64) . 'type' => 'WebPage',
        chr(64) . 'id' => $canonicalUrl
    ],
    'author' => [
        chr(64) . 'type' => 'Person',
        'name' => $authorName,
        'url' => url('/')
    ],
    'publisher' => [
        chr(64) . 'type' => 'SportsOrganization',
        'name' => 'CricketKaScore',
        'url' => url('/'),
        'logo' => [
            chr(64) . 'type' => 'ImageObject',
            'url' => asset('images/logo.png')
        ]
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
                'name' => 'News',
                'item' => route('news')
            ],
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 3,
                'name' => $newsTitle,
                'item' => $canonicalUrl
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container article-page-container">

    <!-- Breadcrumbs Navigation -->
    <nav class="editorial-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}">Home</a>
        <span class="breadcrumb-sep">&raquo;</span>
        <a href="{{ route('news') }}">News</a>
        <span class="breadcrumb-sep">&raquo;</span>
        <span class="breadcrumb-active">{{ Str::limit($newsTitle, 65) }}</span>
    </nav>

    <!-- 70% / 30% Responsive Grid Layout -->
    <div class="article-layout-grid">
        
        <!-- Main Column (70%) -->
        <div class="article-main-col">
            <article class="article-card">
                
                <!-- Category Tag -->
                <div class="article-meta-row">
                    <span class="card-tag">
                        {{ $news->category ?: 'CRICKET NEWS' }}
                    </span>
                    <div class="article-meta-time">
                        ⏱️ {{ $news->read_time ?: '3 MIN READ' }}
                    </div>
                </div>

                <!-- Main Title -->
                <h1 class="article-title">
                    {{ $newsTitle }}
                </h1>

                <!-- Author & Date Row -->
                <div class="article-author-meta-row">
                    <div class="author-info-block">
                        <div class="author-avatar-badge">{{ strtoupper(substr($authorName, 0, 1)) }}</div>
                        <div class="author-text-meta">
                            <div class="author-byline">
                                <strong>By {{ $authorName }}</strong> <span class="meta-dot">&bull;</span> <span class="pub-date">{{ $publishedDate }}</span>
                            </div>
                            <div class="update-byline">
                                Last Updated on {{ $updatedDate }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster Banner Image (100% full width, NEVER cropped, natural aspect ratio) -->
                @if(!empty($news->image_url))
                    <div class="article-poster-container">
                        <img src="{{ $news->image_url }}" alt="{{ $newsTitle }}" class="article-full-poster" onerror="this.parentElement.style.display='none';">
                        <div class="image-source-caption">Image Source: {{ $news->image_source ?: 'X' }}</div>
                    </div>
                @endif

                <!-- Short Summary / Lead Paragraph -->
                @if(!empty($news->meta_description) || !empty($news->summary))
                    <div class="article-summary-lead">
                        {{ $news->meta_description ?: $news->summary }}
                    </div>
                @endif

                <!-- Full Content Body (Rich formatted HTML & Markdown) -->
                <div class="rich-article-body">
                    {!! \Illuminate\Support\Str::markdown($news->content ?: $news->summary) !!}
                </div>

                <!-- Keywords / Tags -->
                @if(!empty($news->keywords))
                    <div class="article-tags-wrap">
                        <span class="article-tags-label">TAGS:</span>
                        @foreach(explode(',', $news->keywords) as $tag)
                            @if(trim($tag))
                                <span class="article-tag-chip">
                                    #{{ trim($tag) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

            </article>
        </div>

        <!-- Sidebar Column (30%) - ONLY Related News -->
        <aside class="article-sidebar-col">
            
            <!-- SECTION 1: Related News -->
            @if(isset($recentNews) && $recentNews->isNotEmpty())
                <div class="sidebar-section-block">
                    <div class="sidebar-cyan-banner">
                        Latest Cricket News
                    </div>
                    <div class="sidebar-cards-stream">
                        @foreach($recentNews->take(6) as $rn)
                            <a href="{{ $rn->url }}" class="sidebar-stream-card" title="{{ $rn->title }}">
                                @if(!empty($rn->image_url))
                                    <div class="stream-card-thumb">
                                        <img src="{{ $rn->image_url }}" alt="{{ $rn->title }}" loading="lazy" onerror="this.parentElement.style.display='none';">
                                    </div>
                                @endif
                                <div class="stream-card-content">
                                    <h4 class="stream-card-title">{{ $rn->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>

</main>

<style>
/* Page Container & Breadcrumbs */
.article-page-container {
    padding: 24px 20px 80px;
    max-width: 1260px;
    margin: 0 auto;
    font-family: var(--font-body, 'Inter', -apple-system, BlinkMacSystemFont, sans-serif);
}

.editorial-breadcrumbs {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 0.86rem;
    color: var(--text-dim, #64748b);
    margin-bottom: 20px;
    font-weight: 600;
}
.editorial-breadcrumbs a {
    color: #0284c7;
    text-decoration: none;
    transition: color 0.15s;
}
.editorial-breadcrumbs a:hover {
    color: #0369a1;
    text-decoration: underline;
}
.breadcrumb-sep {
    color: #94a3b8;
    font-size: 0.9rem;
}
.breadcrumb-active {
    color: var(--text-muted, #475569);
    font-weight: 500;
}

/* 70% / 30% Layout Grid */
.article-layout-grid {
    display: grid;
    grid-template-columns: 7fr 3fr;
    gap: 28px;
    align-items: start;
    width: 100%;
}

@media (max-width: 991px) {
    .article-layout-grid {
        grid-template-columns: 1fr;
        gap: 32px;
    }
}

.article-main-col {
    min-width: 0;
    width: 100%;
}

.article-sidebar-col {
    position: sticky;
    top: 86px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

@media (max-width: 991px) {
    .article-sidebar-col {
        position: static !important;
    }
}

/* Article Card */
.article-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 32px 32px 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

@media (max-width: 640px) {
    .article-card {
        padding: 20px 16px;
    }
}

.article-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}

.card-tag {
    background: #e0f2fe;
    color: #0369a1;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 0.76rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.article-meta-time {
    font-size: 0.82rem;
    color: var(--text-dim, #94a3b8);
    font-weight: 600;
}

/* Title */
.article-title {
    font-size: 2.15rem;
    font-weight: 900;
    color: var(--text-main, #0f172a);
    line-height: 1.28;
    margin: 0 0 16px 0;
    letter-spacing: -0.02em;
}

@media (max-width: 640px) {
    .article-title {
        font-size: 1.6rem;
    }
}

/* Author & Meta Row with Social Badges (Screenshot 1 Match) */
.article-author-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 18px;
    margin-bottom: 22px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}

.author-info-block {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar-badge {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.author-text-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.author-byline {
    font-size: 0.86rem;
    color: var(--text-main, #0f172a);
}
.author-byline strong {
    color: #0284c7;
    font-weight: 750;
}
.meta-dot {
    color: #94a3b8;
    margin: 0 4px;
}
.pub-date {
    color: var(--text-dim, #64748b);
    font-weight: 500;
}

.update-byline {
    font-size: 0.74rem;
    color: var(--text-dim, #94a3b8);
    font-weight: 500;
}

.article-social-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.social-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.badge-google-news {
    background: #ffffff;
    border: 1px solid #dadce0;
    color: #3c4043;
    box-shadow: 0 1px 3px rgba(60,64,67,0.1);
}
.badge-google-news:hover {
    background: #f8fafc;
    border-color: #4285f4;
    transform: translateY(-1px);
}

.badge-telegram {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    color: #0088cc;
}
.badge-telegram:hover {
    background: #0088cc;
    color: #ffffff;
    border-color: #0088cc;
    transform: translateY(-1px);
}
.badge-telegram:hover svg path {
    fill: #ffffff;
}

/* Poster Image (100% full width, NEVER cropped, Screenshot 1 & 2 Match) */
.article-poster-container {
    width: 100%;
    margin-bottom: 24px;
}

.article-full-poster {
    width: 100%;
    max-width: 100%;
    height: auto;
    object-fit: contain;
    border-radius: 12px;
    display: block;
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    background: var(--bg-card-secondary, #f8fafc);
}

.image-source-caption {
    font-size: 0.75rem;
    color: var(--text-dim, #94a3b8);
    text-align: center;
    margin-top: 6px;
    font-weight: 500;
}

/* Summary Lead */
.article-summary-lead {
    font-size: 1.05rem;
    color: var(--text-muted, #475569);
    line-height: 1.65;
    margin: 0 0 24px 0;
    font-weight: 500;
    border-left: 4px solid #0284c7;
    background: var(--bg-card-secondary, #f8fafc);
    padding: 14px 18px;
    border-radius: 0 8px 8px 0;
}

/* Rich Article Body (ChatGPT / Markdown / HTML Styling) */
.rich-article-body {
    color: var(--text-main, #0f172a);
    font-size: 1.05rem;
    line-height: 1.85;
    word-break: break-word;
}

/* CRITICAL: ALL IMAGES INSIDE DESCRIPTION COVER 100% WIDTH, NEVER CROPPED (Screenshot 2 Match) */
.rich-article-body img {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    object-fit: contain !important;
    display: block !important;
    margin: 24px auto 6px auto !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 18px rgba(0,0,0,0.06) !important;
    background: var(--bg-card-secondary, #f8fafc);
}

.rich-article-body figcaption,
.rich-article-body .image-source,
.rich-article-body p:has(img) + p {
    text-align: center;
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 4px;
    margin-bottom: 22px;
    font-weight: 500;
}

.rich-article-body h1 {
    font-size: 1.85rem !important;
    font-weight: 900 !important;
    color: var(--text-main, #0f172a) !important;
    margin: 32px 0 16px 0 !important;
    line-height: 1.3 !important;
    padding-bottom: 8px !important;
    border-bottom: 2px solid var(--border-color, #e2e8f0) !important;
}

.rich-article-body h2 {
    font-size: 1.55rem !important;
    font-weight: 800 !important;
    color: var(--text-main, #0f172a) !important;
    margin: 34px 0 14px 0 !important;
    line-height: 1.35 !important;
    position: relative !important;
    padding-left: 14px !important;
    border-left: 4px solid #0284c7 !important;
}

.rich-article-body h3 {
    font-size: 1.3rem !important;
    font-weight: 750 !important;
    color: var(--text-main, #0f172a) !important;
    margin: 26px 0 12px 0 !important;
    line-height: 1.4 !important;
}

.rich-article-body h4 {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: var(--text-main, #0f172a) !important;
    margin: 20px 0 8px 0 !important;
}

.rich-article-body p {
    margin: 0 0 18px 0 !important;
    line-height: 1.85 !important;
    font-size: 1.02rem !important;
    color: var(--text-main, #1e293b) !important;
}

.rich-article-body ul, .rich-article-body ol {
    margin: 0 0 20px 0 !important;
    padding-left: 26px !important;
}

.rich-article-body ul {
    list-style-type: disc !important;
}

.rich-article-body ol {
    list-style-type: decimal !important;
}

.rich-article-body li {
    margin-bottom: 8px !important;
    line-height: 1.75 !important;
    color: var(--text-main, #1e293b) !important;
}

.rich-article-body strong, .rich-article-body b {
    font-weight: 750 !important;
    color: var(--text-main, #0f172a) !important;
}

.rich-article-body blockquote {
    margin: 24px 0 !important;
    padding: 16px 20px !important;
    background: #f0f7ff !important;
    border-left: 4px solid #0284c7 !important;
    border-radius: 8px !important;
    font-style: normal !important;
    color: var(--text-main, #1e293b) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03) !important;
}

/* Tags Wrap */
.article-tags-wrap {
    margin-top: 36px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.article-tags-label {
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--text-dim, #94a3b8);
}

.article-tag-chip {
    background: var(--bg-card-secondary, #f1f5f9);
    color: var(--text-muted, #475569);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
}

/* 30% Sidebar Components (Screenshot 1 & 2 Match) */
.sidebar-section-block {
    width: 100%;
}

/* Cyan Header Banner (Screenshot 1 Match) */
.sidebar-cyan-banner {
    background: #67e8f9;
    color: #082f49;
    font-weight: 800;
    font-size: 0.88rem;
    letter-spacing: 0.02em;
    text-align: center;
    padding: 9px 14px;
    border-radius: 4px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(6, 182, 212, 0.2);
    text-transform: capitalize;
}

/* Cards Stream List */
.sidebar-cards-stream {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Single Stream Card (Thumbnail Image on Top, Headline Below) */
.sidebar-stream-card {
    display: block;
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    overflow: hidden;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.sidebar-stream-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    border-color: #38bdf8;
}

.stream-card-thumb {
    width: 100%;
    height: 140px;
    background: var(--bg-card-secondary, #f8fafc);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stream-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.sidebar-stream-card:hover .stream-card-thumb img {
    transform: scale(1.03);
}

.stream-card-content {
    padding: 10px 12px 12px;
}

.stream-card-title {
    font-size: 0.84rem;
    font-weight: 750;
    color: var(--text-main, #0f172a);
    line-height: 1.38;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}

.sidebar-stream-card:hover .stream-card-title {
    color: #0284c7;
}

/* Quick Hub Links */
.sidebar-quick-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: var(--bg-card-secondary, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-main, #0f172a);
    font-size: 0.84rem;
    font-weight: 600;
    transition: all 0.15s;
}

.sidebar-quick-link:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0369a1;
    transform: translateX(3px);
}
</style>
@endsection
