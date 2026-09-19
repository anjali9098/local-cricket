@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 20px 80px; max-width: 1240px; margin: 0 auto;">

    <!-- Back Navigation -->
    <div style="margin-bottom: 24px;">
        <a href="{{ route('news') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-text, #38bdf8); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
            &larr; Back to All News &amp; Articles
        </a>
    </div>

    <!-- 2-Column Responsive Layout -->
    <div class="news-layout-grid">
        
        <!-- Main Column (Left) -->
        <div class="news-main-col">
            <article class="news-card">
                
                <!-- Category & Read Time -->
                <div class="news-meta-row">
                    <span class="card-tag">
                        {{ $news->category ?: 'CRICKET NEWS' }}
                    </span>
                    <div class="news-meta-time">
                        ⏱️ {{ $news->read_time ?: '3 MIN READ' }} &bull; {{ $news->created_at ? \Carbon\Carbon::parse($news->created_at)->format('M d, Y') : ($news->published_date ?: 'Today') }}
                    </div>
                </div>

                <!-- Title -->
                <h1 class="news-title">
                    {{ $news->h1_heading ?: $news->title }}
                </h1>

                <!-- Short Summary / Meta description -->
                @if(!empty($news->meta_description) || !empty($news->summary))
                    <div class="news-summary-lead">
                        {{ $news->meta_description ?: $news->summary }}
                    </div>
                @endif

                <!-- Poster Banner Image (No cut off, smart ambient background) -->
                @if(!empty($news->image_url))
                    <div class="news-hero-banner-wrap">
                        <div class="news-hero-backdrop" style="background-image: url('{{ $news->image_url }}');"></div>
                        <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="news-hero-image" onerror="this.closest('.news-hero-banner-wrap').style.display='none';">
                    </div>
                @endif

                <!-- Full Content Body (Rich formatted HTML & Markdown with prominent H1, H2, H3, P, Lists) -->
                <div class="rich-article-body">
                    {!! \Illuminate\Support\Str::markdown($news->content ?: $news->summary) !!}
                </div>

                <!-- Keywords / Tags -->
                @if(!empty($news->keywords))
                    <div class="news-tags-wrap">
                        <span class="news-tags-label">TAGS:</span>
                        @foreach(explode(',', $news->keywords) as $tag)
                            @if(trim($tag))
                                <span class="news-tag-chip">
                                    #{{ trim($tag) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

            </article>
        </div>

        <!-- Sidebar Column (Right) - Fills the empty right side! -->
        <aside class="news-sidebar-col">
            <!-- More Latest News -->
            @if(isset($recentNews) && $recentNews->isNotEmpty())
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h3 class="sidebar-card-title">More Latest News</h3>
                        <a href="{{ route('news') }}" class="sidebar-view-all">View All &rarr;</a>
                    </div>
                    <div class="sidebar-articles-list">
                        @foreach($recentNews as $rn)
                            <a href="{{ route('news.show', $rn->id) }}" class="sidebar-article-item">
                                @if(!empty($rn->image_url))
                                    <div class="sidebar-article-thumb">
                                        <img src="{{ $rn->image_url }}" alt="{{ $rn->title }}" onerror="this.parentElement.style.display='none';">
                                    </div>
                                @endif
                                <div class="sidebar-article-info">
                                    <span class="sidebar-article-cat">{{ $rn->category ?: 'NEWS' }}</span>
                                    <h4 class="sidebar-article-heading">{{ Str::limit($rn->title, 60) }}</h4>
                                    <span class="sidebar-article-date">{{ $rn->created_at ? \Carbon\Carbon::parse($rn->created_at)->format('M d, Y') : ($rn->read_time ?: '3 min read') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Cricket Quick Hub -->
            <div class="sidebar-card" style="margin-top: 20px;">
                <div class="sidebar-card-header">
                    <h3 class="sidebar-card-title">Cricket Hub</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('live') }}" class="sidebar-quick-link">
                        <span>🔴 Live Scores &amp; Matches</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('news', ['type' => 'article']) }}" class="sidebar-quick-link">
                        <span>📝 Cricket Analysis &amp; Articles</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('compare') }}" class="sidebar-quick-link">
                        <span>⚔️ Player vs Player Compare</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('search') }}" class="sidebar-quick-link">
                        <span>🔍 Search Players &amp; Stats</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </aside>
    </div>

</main>

<style>
/* Layout Grid */
.news-layout-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    gap: 32px;
    align-items: start;
}

@media (max-width: 1024px) {
    .news-layout-grid {
        grid-template-columns: 1fr;
    }
    .news-sidebar-col {
        position: static !important;
        margin-top: 24px;
    }
}

.news-main-col {
    min-width: 0;
}

.news-sidebar-col {
    position: sticky;
    top: 90px;
}

/* News Card */
.news-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--radius-lg, 16px);
    padding: 36px 36px 44px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

@media (max-width: 640px) {
    .news-card {
        padding: 22px 18px;
    }
}

.news-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
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

.news-meta-time {
    font-size: 0.82rem;
    color: var(--text-dim, #94a3b8);
    font-weight: 600;
}

.news-title {
    font-size: 2.15rem;
    font-weight: 900;
    color: var(--text-main, #0f172a);
    line-height: 1.3;
    margin: 0 0 16px 0;
    letter-spacing: -0.02em;
}

@media (max-width: 640px) {
    .news-title {
        font-size: 1.6rem;
    }
}

.news-summary-lead {
    font-size: 1.05rem;
    color: var(--text-muted, #475569);
    line-height: 1.65;
    margin: 0 0 24px 0;
    font-weight: 500;
    border-left: 4px solid #0284c7;
    background: var(--bg-card-secondary, #f8fafc);
    padding: 12px 16px;
    border-radius: 0 8px 8px 0;
}

/* Smart Banner Container (No cut off, ambient backdrop) */
.news-hero-banner-wrap {
    position: relative;
    width: 100%;
    max-height: 480px;
    min-height: 240px;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 30px;
    background: #090d16;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.news-hero-backdrop {
    position: absolute;
    inset: -20px;
    background-size: cover;
    background-position: center;
    filter: blur(25px) brightness(0.45);
    opacity: 0.85;
    transform: scale(1.1);
    pointer-events: none;
}

.news-hero-image {
    position: relative;
    z-index: 2;
    max-width: 100%;
    max-height: 480px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35);
}

/* Rich Content Body (ChatGPT / Markdown / HTML Styling) */
.rich-article-body {
    color: var(--text-main, #0f172a);
    font-size: 1.05rem;
    line-height: 1.85;
    word-break: break-word;
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
    background: var(--bg-card-secondary, #f8fafc) !important;
    border-left: 4px solid #0284c7 !important;
    border-radius: 0 10px 10px 0 !important;
    font-style: italic !important;
    color: var(--text-muted, #475569) !important;
}

.rich-article-body hr {
    border: none !important;
    border-top: 1px solid var(--border-color, #e2e8f0) !important;
    margin: 28px 0 !important;
}

.rich-article-body a {
    color: #0284c7 !important;
    text-decoration: underline !important;
    font-weight: 600 !important;
}

.rich-article-body table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 24px 0 !important;
    font-size: 0.95rem !important;
}

.rich-article-body th, .rich-article-body td {
    padding: 10px 14px !important;
    border: 1px solid var(--border-color, #e2e8f0) !important;
    text-align: left !important;
}

.rich-article-body th {
    background: var(--bg-card-secondary, #f8fafc) !important;
    font-weight: 700 !important;
}

/* Tags Wrap */
.news-tags-wrap {
    margin-top: 36px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.news-tags-label {
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--text-dim, #94a3b8);
}

.news-tag-chip {
    background: var(--bg-card-secondary, #f1f5f9);
    color: var(--text-muted, #475569);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
}

/* Sidebar Styles */
.sidebar-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--radius-lg, 16px);
    padding: 22px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}

.sidebar-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}

.sidebar-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-main, #0f172a);
    margin: 0;
    letter-spacing: -0.01em;
}

.sidebar-view-all {
    font-size: 0.78rem;
    font-weight: 700;
    color: #0284c7;
    text-decoration: none;
}
.sidebar-view-all:hover {
    text-decoration: underline;
}

.sidebar-articles-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.sidebar-article-item {
    display: flex;
    gap: 12px;
    text-decoration: none;
    padding: 8px;
    border-radius: 10px;
    transition: background 0.15s, transform 0.15s;
}

.sidebar-article-item:hover {
    background: var(--bg-card-secondary, #f8fafc);
    transform: translateX(3px);
}

.sidebar-article-thumb {
    width: 68px;
    height: 68px;
    flex-shrink: 0;
    border-radius: 8px;
    overflow: hidden;
    background: #0f172a;
}

.sidebar-article-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-article-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 3px;
    min-width: 0;
}

.sidebar-article-cat {
    font-size: 0.68rem;
    font-weight: 800;
    color: #0284c7;
    text-transform: uppercase;
}

.sidebar-article-heading {
    font-size: 0.86rem;
    font-weight: 700;
    color: var(--text-main, #0f172a);
    line-height: 1.35;
    margin: 0;
}

.sidebar-article-date {
    font-size: 0.74rem;
    color: var(--text-dim, #94a3b8);
    font-weight: 500;
}

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
