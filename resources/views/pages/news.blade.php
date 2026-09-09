@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; margin: 0 auto;">

    <!-- Newsroom Header -->
    <div style="margin-bottom: 24px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: var(--primary-text, #38bdf8); text-transform: uppercase; display: block; margin-bottom: 4px;">NEWSROOM</span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 4px 0;">
            @if(($activeType ?? 'news') === 'article')
                CRICKET ARTICLES
            @elseif(($activeType ?? 'news') === 'prediction')
                MATCH PREDICTIONS
            @elseif(($activeType ?? 'news') === 'fantasy')
                FANTASY CRICKET TIPS
            @elseif(($activeType ?? 'news') === 'preview')
                MATCH PREVIEWS
            @else
                CRICKET NEWS
            @endif
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">
            @if(($activeType ?? 'news') === 'article')
                In-depth cricket analysis, feature stories, and editorial columns.
            @elseif(($activeType ?? 'news') === 'prediction')
                Expert cricket match forecasts, pitch reports, and probability analysis.
            @elseif(($activeType ?? 'news') === 'fantasy')
                Captaincy picks, player form guide, and winning fantasy combinations.
            @elseif(($activeType ?? 'news') === 'preview')
                Upcoming fixture breakdowns, team news, and head-to-head records.
            @else
                Latest cricket news, breaking headlines, match reports, and updates.
            @endif
        </p>
    </div>

    <!-- Category Filter Tabs -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px;">
        <a href="{{ route('news', array_filter(['type' => ($activeType ?? 'news') !== 'news' ? $activeType : null])) }}" class="series-tab {{ empty(request('cat')) ? 'active' : '' }}">All Categories</a>
        <a href="{{ route('news', array_filter(['type' => ($activeType ?? 'news') !== 'news' ? $activeType : null, 'cat' => 'INTERNATIONAL'])) }}" class="series-tab {{ request('cat') === 'INTERNATIONAL' ? 'active' : '' }}">International</a>
        <a href="{{ route('news', array_filter(['type' => ($activeType ?? 'news') !== 'news' ? $activeType : null, 'cat' => 'IPL'])) }}" class="series-tab {{ request('cat') === 'IPL' ? 'active' : '' }}">IPL</a>
        <a href="{{ route('news', array_filter(['type' => ($activeType ?? 'news') !== 'news' ? $activeType : null, 'cat' => 'DOMESTIC'])) }}" class="series-tab {{ request('cat') === 'DOMESTIC' ? 'active' : '' }}">Domestic</a>
        <a href="{{ route('news', array_filter(['type' => ($activeType ?? 'news') !== 'news' ? $activeType : null, 'cat' => 'LOCAL'])) }}" class="series-tab {{ request('cat') === 'LOCAL' ? 'active' : '' }}">Local</a>
    </div>

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
                        $detailRoute = null;
                        if (($item->content_type ?? '') === 'article') {
                            $detailRoute = route('article.show', $item->id);
                        } elseif (($item->content_type ?? '') === 'news') {
                            $detailRoute = route('news.show', $item->id);
                        }
                    @endphp

                    @if(!empty($item->image_url))
                        @if($detailRoute)
                            <a href="{{ $detailRoute }}" style="text-decoration: none; display: block;">
                                <div style="width: 100%; height: 180px; border-radius: 10px; overflow: hidden; margin-bottom: 12px; background: var(--bg-card-secondary);">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'" loading="lazy">
                                </div>
                            </a>
                        @else
                            <div style="width: 100%; height: 180px; border-radius: 10px; overflow: hidden; margin-bottom: 12px; background: var(--bg-card-secondary);">
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
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
