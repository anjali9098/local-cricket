@php
    $storyTitle = trim($story->title);
    $storyAuthor = !empty($story->author) ? $story->author : (!empty($story->author_name) ? $story->author_name : 'CricketKaScore');
    $storyCategory = $story->category ?: ($story->tag ?: 'Cricket');
    
    // First slide description or meta description fallback
    $firstSlideDesc = !empty($slides[0]['description']) ? strip_tags($slides[0]['description']) : '';
    $metaDesc = !empty($story->meta_description) ? $story->meta_description : (!empty($firstSlideDesc) ? \Illuminate\Support\Str::limit($firstSlideDesc, 155) : "Watch {$storyTitle} visual web story with highlights, player stats, photos and facts on CricketKaScore.");
    $metaKeywords = !empty($story->keywords) ? $story->keywords : "{$storyTitle}, {$storyCategory} web story, cricket visual story, cricket highlights, {$storyAuthor}, CricketKaScore";
    
    $canonicalUrl = $story->url;
    
    // Find best representative cover image
    $publisherLogo = asset('images/logo.png');
    $candidateImg = !empty($story->image_url) ? $story->image_url : (!empty($slides[0]['image']) ? $slides[0]['image'] : '');
    $posterImage = $publisherLogo;
    if (!empty($candidateImg) && !str_starts_with($candidateImg, 'data:')) {
        $posterImage = str_starts_with($candidateImg, 'http') ? $candidateImg : asset($candidateImg);
    }
    $ogImage = $posterImage;
    
    $publishedDate = $story->created_at ? $story->created_at->toIso8601String() : now()->toIso8601String();
    $modifiedDate = $story->updated_at ? $story->updated_at->toIso8601String() : $publishedDate;
    $storyDate = $story->created_at ? \Carbon\Carbon::parse($story->created_at)->format('d M Y') : date('d M Y');
    
    $pageTitle = $storyTitle . ' — Visual Web Story | CricketKaScore';

    $storySchema = [
        chr(64) . 'context' => 'https://schema.org',
        chr(64) . 'type' => 'NewsArticle',
        'mainEntityOfPage' => [
            chr(64) . 'type' => 'WebPage',
            chr(64) . 'id' => $canonicalUrl
        ],
        'headline' => $storyTitle,
        'description' => $metaDesc,
        'image' => [$posterImage],
        'datePublished' => $publishedDate,
        'dateModified' => $modifiedDate,
        'author' => [
            chr(64) . 'type' => 'Person',
            'name' => $storyAuthor
        ],
        'publisher' => [
            chr(64) . 'type' => 'SportsOrganization',
            'name' => 'CricketKaScore',
            'url' => url('/'),
            'logo' => [
                chr(64) . 'type' => 'ImageObject',
                'url' => $publisherLogo
            ]
        ]
    ];

    $breadcrumbSchema = [
        chr(64) . 'context' => 'https://schema.org',
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
                'name' => 'Web Stories',
                'item' => route('webstories.all')
            ],
            [
                chr(64) . 'type' => 'ListItem',
                'position' => 3,
                'name' => $storyTitle,
                'item' => $canonicalUrl
            ]
        ]
    ];
@endphp
<!doctype html>
<html ⚡ lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <title>{{ $pageTitle }}</title>
    
    <link rel="canonical" href="{{ $canonicalUrl }}">
    
    <!-- Core SEO Meta Tags -->
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    <!-- Open Graph (Facebook / WhatsApp / Social Media) -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="CricketKaScore">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {!! json_encode($storySchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- AMP Core Engine & Story Extension -->
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-1.0.js"></script>

    <!-- Google Fonts (AMP Approved Font Provider) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">

    <!-- Mandatory AMP Boilerplate CSS -->
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>

    <!-- Custom AMP Story CSS (Single style tag strictly in head) -->
    <style amp-custom>
        amp-story {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #ffffff;
        }

        amp-story-page {
            background-color: #0b0f19;
        }

        /* Scrim layer gradient for crystal clear text readability over any background image */
        /* Scrim layer gradient for crystal clear text readability over any background image */
        .scrim-overlay {
            background: linear-gradient(
                180deg,
                rgba(0, 0, 0, 0.05) 0%,
                rgba(0, 0, 0, 0) 35%,
                rgba(11, 15, 23, 0.35) 55%,
                rgba(11, 15, 23, 0.88) 75%,
                rgba(11, 15, 23, 0.98) 100%
            );
            pointer-events: none;
        }

        /* Bottom text card content layer */
        amp-story-grid-layer.story-text-container,
        .story-text-container {
            align-content: end;
            justify-content: flex-end;
            padding: 24px 20px 56px 20px;
            box-sizing: border-box;
        }

        .story-text-wrap {
            width: 100%;
            align-self: end;
        }

        .slide-heading {
            font-size: 1.42rem;
            font-weight: 900;
            line-height: 1.25;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin: 0 0 10px 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);
        }

        .slide-description {
            font-size: 0.90rem;
            line-height: 1.48;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 500;
            margin: 0 0 14px 0;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.9);
        }

        .slide-meta-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.75);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.9);
        }

        .slide-meta-row strong {
            color: #38bdf8;
            font-weight: 700;
        }

        .slide-meta-dot {
            color: rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body>

<amp-story standalone
    title="{{ $storyTitle }}"
    publisher="CricketKaScore"
    publisher-logo-src="{{ $publisherLogo }}"
    poster-portrait-src="{{ $posterImage }}">

    @foreach($slides as $index => $slide)
        @php
            $candidateImg = !empty($slide['image']) ? $slide['image'] : ($story->image_url ?: asset('images/logo.png'));
            if (!empty($candidateImg) && (str_starts_with($candidateImg, 'data:image') || str_contains($candidateImg, ';base64,'))) {
                $candidateImg = asset('images/logo.png');
            }
            if (!str_starts_with($candidateImg, 'http')) {
                $candidateImg = asset($candidateImg);
            }
            $slideImg = $candidateImg;
            $slideHeading = !empty($slide['heading']) ? $slide['heading'] : $story->title;
            $slideDesc = !empty($slide['description']) ? $slide['description'] : ($story->meta_description ?? '');
            $ctaText = !empty($slide['cta_text']) ? $slide['cta_text'] : ($loop->last ? 'More Cricket Stories' : null);
            $ctaUrl = !empty($slide['cta_url']) ? $slide['cta_url'] : ($loop->last ? route('webstories.all') : null);
        @endphp

        <amp-story-page id="slide-{{ $index + 1 }}">
            
            <!-- Layer 1: Background Media Image -->
            <amp-story-grid-layer template="fill">
                <amp-img src="{{ $slideImg }}"
                         width="720"
                         height="1280"
                         layout="responsive"
                         alt="{{ $slideHeading }}">
                </amp-img>
            </amp-story-grid-layer>

            <!-- Layer 2: Gradient Scrim for Readability -->
            <amp-story-grid-layer template="fill" class="scrim-overlay">
            </amp-story-grid-layer>

            <!-- Layer 3: Narrative Content (Placed at bottom without category pill) -->
            <amp-story-grid-layer template="vertical" class="story-text-container">
                <div class="story-text-wrap">
                    @if($index === 0)
                        <h1 class="slide-heading">{{ $slideHeading }}</h1>
                    @else
                        <h2 class="slide-heading">{{ $slideHeading }}</h2>
                    @endif

                    @if(!empty($slideDesc))
                        <p class="slide-description">{{ $slideDesc }}</p>
                    @endif

                    <div class="slide-meta-row">
                        <span>By <strong>{{ $storyAuthor }}</strong></span>
                        <span class="slide-meta-dot">&bull;</span>
                        <span>{{ $storyDate }}</span>
                    </div>
                </div>
            </amp-story-grid-layer>

            <!-- Layer 4: Call-To-Action Outlink (Native AMP Story Outlink) -->
            @if(!empty($ctaUrl))
                <amp-story-page-outlink layout="nodisplay">
                    <a href="{{ $ctaUrl }}">
                        {{ !empty($ctaText) ? $ctaText : 'Read Full Story' }}
                    </a>
                </amp-story-page-outlink>
            @endif

        </amp-story-page>
    @endforeach

</amp-story>

</body>
</html>
