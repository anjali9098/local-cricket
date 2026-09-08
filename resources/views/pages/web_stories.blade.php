@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; margin: 0 auto;">
    <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px; margin-bottom: 32px;">
        <h1 style="font-size: 1.8rem; font-weight: 900; color: #ffffff; margin: 0; letter-spacing: 0.02em;">ALL WEB STORIES</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin: 4px 0 0 0;">Browse through our visual web stories for top highlights and stats</p>
    </div>

    @if($webStories->isNotEmpty())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            @foreach($webStories as $story)
                <a href="{{ route('webstories.show', $story->id) }}" style="text-decoration: none; height: 300px; border-radius: 12px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; display: block; transition: transform 0.2s, border-color 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='#22c55e';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,255,255,0.1)';">
                    <!-- Play Badge -->
                    <div style="position: absolute; top: 12px; right: 12px; z-index: 3; width: 22px; height: 22px; border-radius: 50%; background: rgba(15, 23, 42, 0.6); border: 1.5px solid #22c55e; display: flex; align-items: center; justify-content: center;">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="#22c55e" stroke="#22c55e" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                    </div>
                    
                    <!-- BG Image -->
                    <img src="{{ $story->image_url ?: 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=400&h=600&q=80' }}" alt="{{ $story->title }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
                    
                    <!-- Gradient Overlay -->
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(11,15,23,0.95) 100%); z-index: 2;"></div>
                    
                    <!-- Title -->
                    <div style="position: absolute; bottom: 16px; left: 16px; right: 16px; z-index: 3;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #ffffff; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $story->title }}
                        </span>
                        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.7); margin-top: 6px; display: flex; align-items: center; justify-content: space-between;">
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
