@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; max-width: 900px; margin: 0 auto;">

    <!-- Back Navigation -->
    <div style="margin-bottom: 24px;">
        <a href="{{ route('news', ['type' => 'fantasy']) }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-text, #38bdf8); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
            &larr; Back to All Fantasy Tips
        </a>
    </div>

    <!-- Fantasy Tip Article Card -->
    <article style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg, 16px); padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        
        <!-- Category & Read Time -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <span class="card-tag fantasy" style="background: rgba(34, 197, 94, 0.15); color: #15803d; padding: 4px 12px; border-radius: 6px; font-weight: 800; font-size: 0.78rem; text-transform: uppercase;">
                {{ $tip->tag ?: 'FANTASY CRICKET TIP' }}
            </span>
            <div style="font-size: 0.82rem; color: var(--text-dim); font-weight: 600;">
                ⏱️ 3 MIN READ &bull; {{ $tip->created_at ? \Carbon\Carbon::parse($tip->created_at)->format('M d, Y') : 'Today' }}
            </div>
        </div>

        <!-- Title -->
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); line-height: 1.3; margin: 0 0 16px 0; letter-spacing: -0.02em;">
            {{ $tip->h1_heading ?: $tip->title }}
        </h1>

        <!-- Short Summary / Meta description Highlight Box -->
        @if(!empty($tip->meta_description) || !empty($tip->summary))
            <p style="font-size: 1.05rem; color: var(--text-muted); line-height: 1.6; margin: 0 0 24px 0; font-weight: 500; border-left: 3px solid #22c55e; padding-left: 16px;">
                {{ $tip->meta_description ?: $tip->summary }}
            </p>
        @endif

        <!-- Poster Banner Image (if available) -->
        @if(!empty($tip->poster_image) || !empty($tip->image_url))
            <div style="width: 100%; max-height: 440px; border-radius: 12px; overflow: hidden; margin-bottom: 28px; background: var(--bg-card-secondary);">
                <img src="{{ $tip->poster_image ?: $tip->image_url }}" alt="{{ $tip->title }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.style.display='none';">
            </div>
        @endif

        <!-- Full Content Body -->
        <div style="color: var(--text-main); font-size: 1rem; line-height: 1.8; font-weight: 400;">
            {!! nl2br($tip->full_content ?: $tip->summary) !!}
        </div>

        <!-- Keywords Tags -->
        @if(!empty($tip->keywords))
            <div style="margin-top: 36px; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim);">TAGS:</span>
                @foreach(explode(',', $tip->keywords) as $tag)
                    @if(trim($tag))
                        <span style="background: var(--bg-card-secondary); color: var(--text-muted); padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                            #{{ trim($tag) }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

    </article>

    <!-- Recent Fantasy Tips Grid -->
    @if(isset($recentTips) && $recentTips->isNotEmpty())
        <div style="margin-top: 48px;">
            <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px;">
                More Fantasy Tips &amp; Strategies
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($recentTips as $rt)
                    <a href="{{ route('fantasy.show', $rt->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #16a34a; text-transform: uppercase; margin-bottom: 4px;">
                                {{ $rt->tag ?: 'FANTASY TIP' }}
                            </div>
                            <div style="font-weight: 800; font-size: 0.92rem; color: var(--text-main); line-height: 1.4;">
                                {{ Str::limit($rt->title, 70) }}
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); border-top: 1px solid var(--border-color); padding-top: 8px;">
                            {{ $rt->created_at ? \Carbon\Carbon::parse($rt->created_at)->format('M d, Y') : 'Today' }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</main>
@endsection
