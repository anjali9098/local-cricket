@extends('layouts.app')

@php
    $storyTitle = trim($story->title);
    $storyAuthor = !empty($story->author) ? $story->author : (!empty($story->author_name) ? $story->author_name : 'CricketKaScore Editorial Team');
    $storyCategory = $story->category ?: ($story->tag ?: 'Cricket');
    
    // First slide description or meta description fallback
    $firstSlideDesc = !empty($slides[0]['description']) ? strip_tags($slides[0]['description']) : '';
    $metaDesc = !empty($story->meta_description) ? $story->meta_description : (!empty($firstSlideDesc) ? \Illuminate\Support\Str::limit($firstSlideDesc, 155) : "Watch {$storyTitle} visual web story with highlights, player stats, photos and facts on CricketKaScore.");
    $metaKeywords = !empty($story->keywords) ? $story->keywords : "{$storyTitle}, {$storyCategory} web story, cricket visual story, cricket highlights, {$storyAuthor}, CricketKaScore";
    
    $canonicalUrl = $story->url;
    
    // Find best representative cover image
    $ogImage = asset('images/logo.png');
    $candidateImg = !empty($story->image_url) ? $story->image_url : (!empty($slides[0]['image']) ? $slides[0]['image'] : '');
    if (!empty($candidateImg) && !str_starts_with($candidateImg, 'data:')) {
        $ogImage = str_starts_with($candidateImg, 'http') ? $candidateImg : asset($candidateImg);
    }
    
    $publishedDate = $story->created_at ? $story->created_at->toIso8601String() : now()->toIso8601String();
    $modifiedDate = $story->updated_at ? $story->updated_at->toIso8601String() : $publishedDate;
    
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
        'image' => [$ogImage],
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
                'url' => asset('images/logo.png')
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
{!! json_encode($storySchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<style>
    :root {
        --story-bg-page: #090d16;
        --story-bg-card: #000000;
        --story-text-main: #ffffff;
        --story-text-muted: rgba(255, 255, 255, 0.8);
        --story-progress-bg: rgba(255, 255, 255, 0.3);
        --story-progress-fill: #22c55e;
        --story-btn-bg: rgba(15, 23, 42, 0.7);
        --story-btn-color: #ffffff;
        --story-shadow: 0 20px 45px rgba(0,0,0,0.6);
        --story-text-shadow: 0 2px 6px rgba(0,0,0,0.8);
        --story-gradient: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 40%, rgba(9,13,22,0.96) 100%);
    }

    body.light-theme {
        --story-bg-page: #0f172a; /* Keep dark background around the story card for cinematic feel */
        --story-bg-card: #000000;
    }

    .story-slide-item {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        z-index: 1;
    }
    .story-slide-item.active {
        opacity: 1;
        visibility: visible;
        z-index: 2;
    }

    .story-cta-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #22c55e;
        color: #ffffff !important;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 10px 22px;
        border-radius: 9999px;
        text-decoration: none !important;
        box-shadow: 0 4px 14px rgba(34, 197, 94, 0.45);
        pointer-events: auto;
        cursor: pointer;
        transition: transform 0.15s, background 0.15s, box-shadow 0.15s;
        margin-top: 10px;
        width: fit-content;
    }
    .story-cta-button:hover {
        background: #16a34a;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.6);
    }
</style>

<div style="background: var(--story-bg-page); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 10px; box-sizing: border-box;">
    
    <!-- Mobile & Desktop Web Story Frame (9:16 Aspect Ratio) -->
    <div style="position: relative; width: 100%; max-width: 440px; height: 90vh; max-height: 820px; min-height: 560px; background: var(--story-bg-card); border-radius: 16px; overflow: hidden; box-shadow: var(--story-shadow); border: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column;">
        
        <!-- Segmented Progress Bar (Top) -->
        <div id="progressContainer" style="position: absolute; top: 12px; left: 14px; right: 14px; z-index: 15; display: flex; gap: 4px;">
            @foreach($slides as $index => $slide)
                <div style="flex: 1; height: 3px; background: var(--story-progress-bg); border-radius: 2px; overflow: hidden;">
                    <div id="progressBarFill-{{ $index }}" style="width: {{ $index === 0 ? '0%' : '0%' }}; height: 100%; background: var(--story-progress-fill); transition: width 0.05s linear;"></div>
                </div>
            @endforeach
        </div>

        <!-- Controls Header -->
        <div style="position: absolute; top: 24px; left: 14px; right: 14px; z-index: 15; display: flex; align-items: center; justify-content: space-between;">
            <!-- Back Button -->
            <a href="{{ route('webstories.all') }}" style="background: var(--story-btn-bg); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 34px; height: 34px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.3rem; line-height: 1; cursor: pointer; transition: all 0.2s;" title="All Stories">
                &lsaquo;
            </a>

            <!-- Slide Counter Badge -->
            <span id="slideCounterBadge" style="background: var(--story-btn-bg); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); color: #f8fafc; font-size: 0.75rem; font-weight: 800; padding: 4px 12px; border-radius: 12px; letter-spacing: 0.05em;">
                1 / {{ count($slides) }}
            </span>

            <!-- Right Controls (Play/Pause, Share) -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Play/Pause Toggle -->
                <button id="playPauseBtn" onclick="togglePlayPause()" style="background: var(--story-btn-bg); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 34px; height: 34px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; outline: none;" title="Play / Pause">
                    <svg id="pauseIcon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                    <svg id="playIcon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </button>

                <!-- Share Button -->
                <button onclick="shareStory()" style="background: var(--story-btn-bg); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 34px; height: 34px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; outline: none;" title="Share Story">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="18" cy="5" r="3"></circle>
                        <circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Slides Wrapper -->
        <div id="slidesWrapper" style="position: relative; width: 100%; height: 100%; flex: 1;">
            
            @foreach($slides as $index => $slide)
                @php
                    $slideImg = !empty($slide['image']) ? $slide['image'] : ($story->image_url ?: 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80');
                    $slideHeading = !empty($slide['heading']) ? $slide['heading'] : $story->title;
                    $slideDesc = !empty($slide['description']) ? $slide['description'] : ($story->meta_description ?? '');
                    $ctaText = !empty($slide['cta_text']) ? $slide['cta_text'] : null;
                    $ctaUrl = !empty($slide['cta_url']) ? $slide['cta_url'] : null;
                @endphp

                <div id="slideItem-{{ $index }}" class="story-slide-item {{ $index === 0 ? 'active' : '' }}">
                    
                    <!-- Background Visual Image -->
                    <img src="{{ $slideImg }}" alt="{{ $slideHeading }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" onerror="this.src='{{ $story->image_url }}';">

                    <!-- Gradient Shade (Ensures Headline & Description are crystal clear) -->
                    <div style="position: absolute; inset: 0; background: var(--story-gradient); z-index: 2; pointer-events: none;"></div>

                    <!-- Slide Content Info Overlay (Bottom) -->
                    <div style="position: absolute; bottom: 32px; left: 20px; right: 20px; z-index: 5; display: flex; flex-direction: column; gap: 8px;">
                        
                        <!-- Category Badge -->
                        @if(!empty($story->category) || !empty($story->tag))
                            <span style="background: rgba(34, 197, 94, 0.9); color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 4px; width: fit-content; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                {{ $story->category ?: $story->tag }}
                            </span>
                        @endif

                        <!-- Slide Heading -->
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: #ffffff; line-height: 1.3; margin: 0; text-shadow: var(--story-text-shadow);">
                            {{ $slideHeading }}
                        </h2>

                        <!-- Slide Description (120-150 Chars) -->
                        @if(!empty($slideDesc))
                            <p style="font-size: 0.88rem; color: rgba(255, 255, 255, 0.9); line-height: 1.45; margin: 0; text-shadow: var(--story-text-shadow);">
                                {{ $slideDesc }}
                            </p>
                        @endif

                        <!-- CTA Button (Screenshot 2: ctaText and ctaUrl) -->
                        @if(!empty($ctaText) && !empty($ctaUrl))
                            <div style="margin-top: 4px;">
                                <a href="{{ $ctaUrl }}" target="_blank" rel="noopener noreferrer" class="story-cta-button" onclick="event.stopPropagation();">
                                    <span>{{ $ctaText }}</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7"></line>
                                        <polyline points="7 7 17 7 17 17"></polyline>
                                    </svg>
                                </a>
                            </div>
                        @endif

                        <!-- Story Meta (Author & Date) -->
                        <div style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <span>By <strong>{{ $story->author ?? 'Admin' }}</strong></span>
                            <span>&bull;</span>
                            <span>{{ $story->created_at ? \Carbon\Carbon::parse($story->created_at)->format('d M Y') : 'Published' }}</span>
                        </div>

                    </div>
                </div>
            @endforeach

            <!-- Tap Navigation Overlay Zones (Left 35% / Right 65%) -->
            <div style="position: absolute; top: 0; bottom: 0; left: 0; width: 35%; z-index: 8; cursor: pointer;" onclick="navigateSlide(-1)"></div>
            <div style="position: absolute; top: 0; bottom: 0; right: 0; width: 65%; z-index: 8; cursor: pointer;" onclick="navigateSlide(1)"></div>

            <!-- Floating Arrow Navigation Buttons -->
            @if(count($slides) > 1)
                <button onclick="event.stopPropagation(); navigateSlide(-1)" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); z-index: 10; width: 34px; height: 34px; border-radius: 50%; background: var(--story-btn-bg); backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.4rem; line-height: 1; outline: none; transition: background 0.15s;" title="Previous Slide">&lsaquo;</button>
                <button onclick="event.stopPropagation(); navigateSlide(1)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 10; width: 34px; height: 34px; border-radius: 50%; background: var(--story-btn-bg); backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.4rem; line-height: 1; outline: none; transition: background 0.15s;" title="Next Slide">&rsaquo;</button>
            @endif

        </div>

    </div>
</div>

<script>
    const slidesCount = {{ count($slides) }};
    let currentActiveIndex = 0;
    let isPlaying = true;
    
    const slideDuration = 5000; // 5 seconds per slide
    let progressInterval = null;
    let currentProgress = 0; // 0 to 100%
    
    const intervalTick = 50; // tick every 50ms for smooth 60fps fill
    const progressStep = (intervalTick / slideDuration) * 100;

    document.addEventListener('DOMContentLoaded', () => {
        startProgressTimer();
        updateCounterBadge();
    });

    function startProgressTimer() {
        clearInterval(progressInterval);
        progressInterval = setInterval(() => {
            if (isPlaying) {
                currentProgress += progressStep;
                if (currentProgress >= 100) {
                    currentProgress = 0;
                    navigateSlide(1);
                } else {
                    updateProgressBar(currentActiveIndex, currentProgress);
                }
            }
        }, intervalTick);
    }

    function updateProgressBar(index, percent) {
        const bar = document.getElementById(`progressBarFill-${index}`);
        if (bar) {
            bar.style.width = `${percent}%`;
        }
    }

    function updateCounterBadge() {
        const badge = document.getElementById('slideCounterBadge');
        if (badge) {
            badge.innerText = `${currentActiveIndex + 1} / ${slidesCount}`;
        }
    }

    function navigateSlide(direction) {
        // Reset current active progress
        updateProgressBar(currentActiveIndex, 0);

        // Hide current slide
        const prevSlide = document.getElementById(`slideItem-${currentActiveIndex}`);
        if (prevSlide) prevSlide.classList.remove('active');

        if (direction === 1) {
            if (currentActiveIndex < slidesCount - 1) {
                // Complete bars up to current index
                for (let i = 0; i <= currentActiveIndex; i++) {
                    updateProgressBar(i, 100);
                }
                currentActiveIndex++;
            } else {
                // Finish story: loop back or return to all stories
                window.location.href = "{{ route('webstories.all') }}";
                return;
            }
        } else {
            if (currentActiveIndex > 0) {
                updateProgressBar(currentActiveIndex, 0);
                currentActiveIndex--;
                updateProgressBar(currentActiveIndex, 0);
            } else {
                currentActiveIndex = 0;
            }
        }

        // Activate new slide
        const nextSlide = document.getElementById(`slideItem-${currentActiveIndex}`);
        if (nextSlide) nextSlide.classList.add('active');

        // Update progress bars ahead and behind
        for (let i = 0; i < currentActiveIndex; i++) {
            updateProgressBar(i, 100);
        }
        for (let i = currentActiveIndex + 1; i < slidesCount; i++) {
            updateProgressBar(i, 0);
        }

        currentProgress = 0;
        updateProgressBar(currentActiveIndex, 0);
        updateCounterBadge();
    }

    function togglePlayPause() {
        isPlaying = !isPlaying;
        const playIcon = document.getElementById('playIcon');
        const pauseIcon = document.getElementById('pauseIcon');

        if (isPlaying) {
            if (playIcon) playIcon.style.display = 'none';
            if (pauseIcon) pauseIcon.style.display = 'block';
        } else {
            if (playIcon) playIcon.style.display = 'block';
            if (pauseIcon) pauseIcon.style.display = 'none';
        }
    }

    function shareStory() {
        if (navigator.share) {
            navigator.share({
                title: "{{ addslashes($story->title) }}",
                url: window.location.href
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Story link copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy link.');
            });
        }
    }

    // Keyboard navigation (Left and Right arrows)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === ' ') {
            e.preventDefault();
            navigateSlide(1);
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            navigateSlide(-1);
        }
    });

    // Pause on hold
    const wrapper = document.getElementById('slidesWrapper');
    if (wrapper) {
        wrapper.addEventListener('mousedown', () => { isPlaying = false; });
        wrapper.addEventListener('mouseup', () => { isPlaying = true; });
        wrapper.addEventListener('touchstart', () => { isPlaying = false; }, { passive: true });
        wrapper.addEventListener('touchend', () => { isPlaying = true; }, { passive: true });
    }
</script>
@endsection
