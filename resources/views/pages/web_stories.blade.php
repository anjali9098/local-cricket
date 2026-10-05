@extends('layouts.app')

@php
    $pageTitle = 'Visual Cricket Web Stories — Top Highlights, Player Stories & Moments | CricketKaScore';
    $metaDesc = 'Browse visual cricket web stories, top match highlights, player biographies, cricket stats, and unforgettable tournament moments in tap-friendly mobile format on CricketKaScore.';
    $metaKeywords = 'cricket web stories, cricket visual stories, web stories cricket, virat kohli web story, cricket highlights story, T20 world cup stories, CricketKaScore';
    $canonicalUrl = route('webstories.all');

    $collectionSchema = [
        chr(64) . 'context' => 'https://schema.org',
        chr(64) . 'type' => 'CollectionPage',
        'name' => 'Visual Cricket Web Stories',
        'description' => $metaDesc,
        'url' => $canonicalUrl,
        'publisher' => [
            chr(64) . 'type' => 'SportsOrganization',
            'name' => 'CricketKaScore',
            'url' => url('/'),
            'logo' => asset('images/logo.png')
        ]
    ];

    if ($webStories->isNotEmpty()) {
        $itemListElements = [];
        foreach ($webStories->take(12) as $index => $s) {
            $storyImg = asset('images/logo.png');
            $candidate = $s->image_url ?: ($s->slides[0]['image'] ?? '');
            if (!empty($candidate) && !str_starts_with($candidate, 'data:')) {
                $storyImg = str_starts_with($candidate, 'http') ? $candidate : asset($candidate);
            }
            $itemListElements[] = [
                chr(64) . 'type' => 'ListItem',
                'position' => $index + 1,
                'url' => $s->url,
                'name' => $s->title,
                'image' => $storyImg
            ];
        }
        $collectionSchema['mainEntity'] = [
            chr(64) . 'type' => 'ItemList',
            'itemListElement' => $itemListElements
        ];
    }
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'website')
@section('og_title', $pageTitle)
@section('og_description', $metaDesc)
@section('og_url', $canonicalUrl)

@section('additional_schema')
<script type="application/ld+json">
{!! json_encode($collectionSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<main class="container" style="padding: 40px 24px 80px; margin: 0 auto;">
    <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px; margin-bottom: 32px;">
        <h1 style="font-size: 1.8rem; font-weight: 900; color: #ffffff; margin: 0; letter-spacing: 0.02em;">ALL WEB STORIES</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 4px 0 0 0;">Browse through our visual web stories for top highlights and stats</p>
    </div>

    @if($webStories->isNotEmpty())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            @foreach($webStories as $story)
                <a href="{{ $story->url }}" style="text-decoration: none; height: 300px; border-radius: 12px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; display: block; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#22c55e';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.1)';">
                    <!-- Top Badges: Category & Slides Count -->
                    <div style="position: absolute; top: 12px; left: 12px; right: 12px; z-index: 3; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span style="background: rgba(34, 197, 94, 0.9); color: white; font-size: 0.68rem; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.04em;">
                            {{ $story->category ?: ($story->tag ?: 'STORY') }}
                        </span>
                        <span style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); color: #f8fafc; font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);">
                            🎬 {{ count($story->slides ?? []) }}
                        </span>
                    </div>
                    
                    <!-- BG Cover Image -->
                    <img src="{{ $story->image_url ?: ($story->slides[0]['image'] ?? '') }}" alt="{{ $story->title }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
                    
                    <!-- Gradient Overlay -->
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(11,15,23,0.95) 100%); z-index: 2;"></div>
                    
                    <!-- Title & Meta -->
                    <div style="position: absolute; bottom: 16px; left: 16px; right: 16px; z-index: 3;">
                        <span style="font-size: 0.88rem; font-weight: 800; color: #ffffff; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $story->title }}
                        </span>
                        <div style="font-size: 0.72rem; color: rgba(255,255,255,0.75); margin-top: 6px; display: flex; align-items: center; justify-content: space-between;">
                            <span>By {{ $story->author ?? 'Admin' }}</span>
                            @if($story->created_at)
                                <span style="font-weight: 600;">{{ \Carbon\Carbon::parse($story->created_at)->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 60px; text-align: center;">
            <span style="font-size: 3rem;">📖</span>
            <h3 style="color: var(--text-main); margin: 16px 0 8px 0;">No web stories yet</h3>
            <p style="color: var(--text-muted); margin: 0;">Check back later or add them as Super Admin!</p>
        </div>
    @endif
</main>
@endsection
