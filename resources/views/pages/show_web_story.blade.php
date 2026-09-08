@extends('layouts.app')

@section('content')
<style>
    :root {
        --story-bg-page: #090d16;
        --story-bg-card: #000000;
        --story-text-main: #ffffff;
        --story-text-muted: rgba(255, 255, 255, 0.75);
        --story-progress-bg: rgba(255, 255, 255, 0.35);
        --story-progress-fill: #ffffff;
        --story-btn-bg: rgba(15, 23, 42, 0.6);
        --story-btn-color: #ffffff;
        --story-shadow: 0 10px 30px rgba(0,0,0,0.5);
        --story-text-shadow: 0 2px 4px rgba(0,0,0,0.6);
        --story-gradient: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.9) 100%);
    }

    body.light-theme {
        --story-bg-page: #f8fafc;
        --story-bg-card: #ffffff;
        --story-text-main: #0f172a;
        --story-text-muted: #64748b;
        --story-progress-bg: rgba(0, 0, 0, 0.12);
        --story-progress-fill: #22c55e;
        --story-btn-bg: rgba(241, 245, 249, 0.95);
        --story-btn-color: #0f172a;
        --story-shadow: 0 10px 30px rgba(0,0,0,0.06);
        --story-text-shadow: none;
        --story-gradient: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.95) 100%);
    }
</style>

<div style="background: var(--story-bg-page); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 0; transition: background 0.2s;">
    <div style="position: relative; width: 100%; max-width: 420px; height: 85vh; min-height: 550px; background: var(--story-bg-card); border-radius: 16px; overflow: hidden; box-shadow: var(--story-shadow); border: 1px solid var(--border-color); display: flex; flex-direction: column; transition: all 0.2s;">
        
        <!-- Segmented Progress Bar (Top) -->
        <div id="progressContainer" style="position: absolute; top: 12px; left: 12px; right: 12px; z-index: 10; display: flex; gap: 4px;">
            @foreach($slides as $index => $slide)
                <div style="flex: 1; height: 3px; background: var(--story-progress-bg); border-radius: 2px; overflow: hidden;">
                    <div id="progressBarFill-{{ $index }}" style="width: 0%; height: 100%; background: var(--story-progress-fill); transition: width 0.1s linear;"></div>
                </div>
            @endforeach
        </div>

        <!-- Controls Header -->
        <div style="position: absolute; top: 24px; left: 16px; right: 16px; z-index: 10; display: flex; align-items: center; justify-content: space-between;">
            <!-- Close / Back Button -->
            <a href="javascript:history.back()" style="background: var(--story-btn-bg); border: none; border-radius: 50%; width: 32px; height: 32px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 1.1rem; cursor: pointer; transition: all 0.2s;">
                &lsaquo;
            </a>

            <!-- Right Controls (Play/Pause, Share) -->
            <div style="display: flex; gap: 8px;">
                <!-- Play/Pause Toggle -->
                <button id="playPauseBtn" onclick="togglePlayPause()" style="background: var(--story-btn-bg); border: none; border-radius: 50%; width: 32px; height: 32px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; outline: none; transition: all 0.2s;">
                    <!-- Pause Icon (Default playing) -->
                    <svg id="pauseIcon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                    <!-- Play Icon -->
                    <svg id="playIcon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </button>

                <!-- Share Button -->
                <button onclick="shareStory()" style="background: var(--story-btn-bg); border: none; border-radius: 50%; width: 32px; height: 32px; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; outline: none; transition: all 0.2s;" title="Copy Story Link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Slides Wrapper -->
        <div id="slidesWrapper" style="position: relative; width: 100%; height: 100%; flex: 1;">
            @foreach($slides as $index => $slide)
                <div id="slideItem-{{ $index }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: {{ $index === 0 ? 'block' : 'none' }}; z-index: 1;">
                    <!-- Image -->
                    <img src="{{ $slide }}" alt="Slide {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            @endforeach

            <!-- Tap Navigation Overlay Zones -->
            <div style="position: absolute; top: 0; bottom: 0; left: 0; width: 35%; z-index: 5; cursor: pointer;" onclick="navigateSlide(-1)"></div>
            <div style="position: absolute; top: 0; bottom: 0; right: 0; width: 65%; z-index: 5; cursor: pointer;" onclick="navigateSlide(1)"></div>

            <!-- Arrow Navigation Buttons -->
            @if(count($slides) > 1)
                <button onclick="event.stopPropagation(); navigateSlide(-1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 12; width: 36px; height: 36px; border-radius: 50%; background: var(--story-btn-bg); border: none; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; outline: none; transition: background 0.2s;">&lsaquo;</button>
                <button onclick="event.stopPropagation(); navigateSlide(1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 12; width: 36px; height: 36px; border-radius: 50%; background: var(--story-btn-bg); border: none; color: var(--story-btn-color); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; outline: none; transition: background 0.2s;">&rsaquo;</button>
            @endif

            <!-- Gradient Bottom Overlay -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 45%; background: var(--story-gradient); z-index: 4; pointer-events: none;"></div>

            <!-- Text Content Info overlay (Bottom) -->
            <div style="position: absolute; bottom: 40px; left: 24px; right: 24px; z-index: 6; color: var(--story-text-main); pointer-events: none;">
                <h2 style="font-size: 1.5rem; font-weight: 800; line-height: 1.35; margin: 0 0 12px 0; text-shadow: var(--story-text-shadow);">
                    {{ $story->title }}
                </h2>
                <div style="font-size: 0.82rem; color: var(--story-text-muted); display: flex; align-items: center; gap: 6px;">
                    <span>By <strong style="color: var(--story-text-main);">{{ $story->author ?? 'Admin' }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ $story->created_at ? \Carbon\Carbon::parse($story->created_at)->format('d M Y') : 'Published' }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const slidesCount = {{ count($slides) }};
    let currentActiveIndex = 0;
    let isPlaying = true;
    
    const slideDuration = 5000; // 5 seconds per slide
    let progressInterval = null;
    let currentProgress = 0; // percentage: 0 to 100
    
    const intervalTick = 50; // tick every 50ms for smooth progress bar fill
    const progressStep = (intervalTick / slideDuration) * 100;

    // Start player
    startProgressTimer();

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

    function navigateSlide(direction) {
        // Reset current bar width
        updateProgressBar(currentActiveIndex, 0);

        // Hide current active slide
        document.getElementById(`slideItem-${currentActiveIndex}`).style.display = 'none';

        if (direction === 1) {
            // Forward
            if (currentActiveIndex < slidesCount - 1) {
                // Fill current progress segment completely to look nice
                for (let i = 0; i <= currentActiveIndex; i++) {
                    updateProgressBar(i, 100);
                }
                currentActiveIndex++;
            } else {
                // Loop or go back to main site
                window.history.back();
                return;
            }
        } else {
            // Backward
            if (currentActiveIndex > 0) {
                updateProgressBar(currentActiveIndex, 0);
                currentActiveIndex--;
                updateProgressBar(currentActiveIndex, 0);
            } else {
                currentActiveIndex = 0;
            }
        }

        // Show new active slide
        document.getElementById(`slideItem-${currentActiveIndex}`).style.display = 'block';

        // Clear progress segments ahead of current active slide
        for (let i = currentActiveIndex + 1; i < slidesCount; i++) {
            updateProgressBar(i, 0);
        }
        for (let i = 0; i < currentActiveIndex; i++) {
            updateProgressBar(i, 100);
        }

        currentProgress = 0;
        updateProgressBar(currentActiveIndex, 0);
    }

    function togglePlayPause() {
        isPlaying = !isPlaying;
        const playIcon = document.getElementById('playIcon');
        const pauseIcon = document.getElementById('pauseIcon');

        if (isPlaying) {
            playIcon.style.display = 'none';
            pauseIcon.style.display = 'block';
        } else {
            playIcon.style.display = 'block';
            pauseIcon.style.display = 'none';
        }
    }

    function shareStory() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Story link copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy link.');
        });
    }

    // Pause story on hold (mouse down / touch start)
    const slidesWrapper = document.getElementById('slidesWrapper');
    if (slidesWrapper) {
        slidesWrapper.addEventListener('mousedown', () => { isPlaying = false; });
        slidesWrapper.addEventListener('mouseup', () => { isPlaying = true; });
        slidesWrapper.addEventListener('touchstart', () => { isPlaying = false; });
        slidesWrapper.addEventListener('touchend', () => { isPlaying = true; });
    }
</script>
@endsection
