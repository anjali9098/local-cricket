@extends('layouts.app')

@php
    $isPreviewMode = !empty($isPreview);
    $cleanTitle = trim($prediction->h1_heading ?: $prediction->title);
    $categoryLabel = $prediction->tag ?: ($isPreviewMode ? 'Match Preview' : 'Match Prediction');
    
    if ($isPreviewMode) {
        $pageTitle = $cleanTitle . ' — Match Preview, Squads & Pitch Report | CricketKaScore';
        $defaultDesc = 'Complete match preview for ' . $cleanTitle . ' featuring pitch reports, head-to-head records, playing XI analysis, and key players on CricketKaScore.';
        $defaultKeywords = $cleanTitle . ', cricket match preview, pitch report, playing 11 prediction, head to head records, CricketKaScore preview';
        $canonicalUrl = route('preview.show', $prediction->id);
        $breadcrumbCategoryName = 'Match Previews';
        $breadcrumbCategoryUrl = route('previews');
    } else {
        $pageTitle = $cleanTitle . ' — Today Match Prediction & Win Probability | CricketKaScore';
        $defaultDesc = 'Today cricket match prediction for ' . $cleanTitle . ' with win probability, pitch condition, toss forecast, and match analysis on CricketKaScore.';
        $defaultKeywords = $cleanTitle . ', today match prediction, cricket prediction, win probability, pitch report, toss analysis, winning chances, CricketKaScore prediction';
        $canonicalUrl = route('prediction.show', $prediction->id);
        $breadcrumbCategoryName = 'Match Predictions';
        $breadcrumbCategoryUrl = route('predictions');
    }

    $metaDesc = trim($prediction->meta_description ?: ($prediction->summary ? \Illuminate\Support\Str::limit(strip_tags($prediction->summary), 160) : $defaultDesc));
    $metaKeywords = $prediction->keywords ?: $defaultKeywords;

    // Image resolution: priority is poster_image, then image_url, then local fallback
    $fallbackImage = asset('images/articles/1788433528_India.jpg');
    $posterImg = $prediction->poster_image ?: ($prediction->image_url ?: null);
    if (empty($posterImg)) {
        $posterImg = asset('uploads/predictions/repaired_1789022057_UGSu6Bh2.jpg');
    }
    $ogImage = $posterImg ?: $fallbackImage;
    $publishedTime = $prediction->created_at ? \Carbon\Carbon::parse($prediction->created_at)->toIso8601String() : now()->toIso8601String();
    $modifiedTime = $prediction->updated_at ? \Carbon\Carbon::parse($prediction->updated_at)->toIso8601String() : $publishedTime;
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
    'headline' => $cleanTitle,
    'description' => $metaDesc,
    'image' => [$ogImage],
    'datePublished' => $publishedTime,
    'dateModified' => $modifiedTime,
    'mainEntityOfPage' => [
        chr(64) . 'type' => 'WebPage',
        chr(64) . 'id' => $canonicalUrl
    ],
    'author' => [
        chr(64) . 'type' => 'Organization',
        'name' => 'CricketKaScore Editorial Team',
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
                'name' => $breadcrumbCategoryName,
                'item' => $breadcrumbCategoryUrl
            ],
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 3,
                'name' => $cleanTitle,
                'item' => $canonicalUrl
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container" style="padding: 40px 24px 80px; max-width: 900px; margin: 0 auto;">

    <!-- Back Navigation -->
    <div style="margin-bottom: 24px;">
        <a href="{{ !empty($isPreview) ? route('previews') : route('predictions') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-text, #38bdf8); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
            &larr; Back to All {{ !empty($isPreview) ? 'Match Previews' : 'Match Predictions' }}
        </a>
    </div>

    <!-- Prediction Article Card -->
    <article style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg, 16px); padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        
        <!-- Category & Read Time -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <span class="card-tag" style="background: {{ !empty($isPreview) ? 'rgba(56, 189, 248, 0.15)' : 'rgba(249, 115, 22, 0.15)' }}; color: {{ !empty($isPreview) ? '#0284c7' : '#ea580c' }}; padding: 4px 12px; border-radius: 6px; font-weight: 800; font-size: 0.78rem; text-transform: uppercase;">
                {{ $prediction->tag ?: (!empty($isPreview) ? 'MATCH PREVIEW' : 'MATCH PREDICTION') }}
            </span>
            <div style="font-size: 0.82rem; color: var(--text-dim); font-weight: 600;">
                ⏱️ 3 MIN READ &bull; {{ $prediction->created_at ? \Carbon\Carbon::parse($prediction->created_at)->format('M d, Y') : 'Today' }}
            </div>
        </div>

        <!-- Title -->
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); line-height: 1.3; margin: 0 0 16px 0; letter-spacing: -0.02em;">
            {{ $cleanTitle }}
        </h1>

        <!-- Short Summary / Meta description Highlight Box -->
        @if(!empty($prediction->meta_description) || !empty($prediction->summary))
            <p style="font-size: 1.05rem; color: var(--text-muted); line-height: 1.6; margin: 0 0 24px 0; font-weight: 500; border-left: 3px solid {{ !empty($isPreview) ? '#38bdf8' : '#f97316' }}; padding-left: 16px;">
                {{ $prediction->meta_description ?: $prediction->summary }}
            </p>
        @endif

        <!-- Poster Banner Image -->
        <div style="width: 100%; max-height: 440px; border-radius: 12px; overflow: hidden; margin-bottom: 28px; background: var(--bg-card-secondary); box-shadow: 0 4px 16px rgba(0,0,0,0.08);">
            <img src="{{ $posterImg }}" alt="{{ $cleanTitle }}" style="width: 100%; height: 100%; max-height: 440px; object-fit: cover; display: block;" onerror="if (this.src !== '{{ $fallbackImage }}') { this.src = '{{ $fallbackImage }}'; } else { this.parentElement.style.display = 'none'; }">
        </div>

        <!-- Full Content Body -->
        <div style="color: var(--text-main); font-size: 1rem; line-height: 1.8; font-weight: 400;">
            {!! nl2br($prediction->full_content ?: $prediction->summary) !!}
        </div>

        <!-- Keywords Tags -->
        @if(!empty($prediction->keywords))
            <div style="margin-top: 36px; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim);">TAGS:</span>
                @foreach(explode(',', $prediction->keywords) as $tag)
                    @if(trim($tag))
                        <span style="background: var(--bg-card-secondary); color: var(--text-muted); padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                            #{{ trim($tag) }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

    </article>

    <!-- Recent Predictions / Previews Grid -->
    @if(isset($recentPredictions) && $recentPredictions->isNotEmpty())
        <div style="margin-top: 48px;">
            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px;">
                More {{ !empty($isPreview) ? 'Match Previews' : 'Match Predictions' }}
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($recentPredictions as $rp)
                    @php $route = !empty($isPreview) ? route('preview.show', $rp->id) : route('prediction.show', $rp->id); @endphp
                    <a href="{{ $route }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: {{ !empty($isPreview) ? '#0284c7' : '#ea580c' }}; text-transform: uppercase; margin-bottom: 4px;">
                                {{ $rp->tag ?: (!empty($isPreview) ? 'PREVIEW' : 'PREDICTION') }}
                            </div>
                            <div style="font-weight: 800; font-size: 0.92rem; color: var(--text-main); line-height: 1.4;">
                                {{ Str::limit($rp->title, 70) }}
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); border-top: 1px solid var(--border-color); padding-top: 8px;">
                            {{ $rp->created_at ? \Carbon\Carbon::parse($rp->created_at)->format('M d, Y') : 'Today' }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</main>
@endsection
