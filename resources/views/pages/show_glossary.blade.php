@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; max-width: 860px; margin: 0 auto; font-family: var(--font-body, 'Inter', sans-serif);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <a href="{{ route('glossary.all') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #22c55e; text-decoration: none; font-weight: 800; font-size: 0.9rem;">
            &larr; Back to Full Glossary
        </a>
        <a href="{{ route('home') }}" style="color: var(--text-dim); text-decoration: none; font-weight: 700; font-size: 0.85rem;">
            Home
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 36px 32px; box-shadow: var(--shadow-lg); display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Top Badge & Label -->
        <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
            @if(!empty($term->poster_image))
                <img src="{{ $term->poster_image }}" alt="{{ $term->term }}" style="width: 72px; height: 72px; border-radius: 12px; object-fit: cover; border: 2px solid #22c55e; box-shadow: 0 4px 12px rgba(34,197,94,0.25);">
            @else
                <div style="width: 64px; height: 64px; border-radius: 12px; background: rgba(34, 197, 94, 0.15); color: #22c55e; display: flex; align-items: center; justify-content: center; font-weight: 950; font-size: 2rem; flex-shrink: 0;">
                    {{ strtoupper($term->letter ?: substr($term->term, 0, 1)) }}
                </div>
            @endif
            <div>
                <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #22c55e;">CRICKET GLOSSARY TERM</span>
                <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); margin: 4px 0 0 0; letter-spacing: -0.02em;">
                    {{ $term->term }}
                </h1>
            </div>
        </div>

        @if(!empty($term->poster_image))
            <!-- Full Illustration Poster Banner if available -->
            <div style="width: 100%; border-radius: 12px; overflow: hidden; max-height: 380px; border: 1px solid var(--border-color);">
                <img src="{{ $term->poster_image }}" alt="{{ $term->term }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </div>
        @endif

        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 0;">

        <!-- Definition Block -->
        <div>
            <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--text-dim); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                Definition &amp; Explanation
            </h3>
            <p style="font-size: 1.15rem; color: var(--text-main); line-height: 1.7; margin: 0;">
                {{ $term->definition }}
            </p>
        </div>

        @if($term->keywords)
            <div style="padding-top: 12px; border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-dim);">
                <strong>Keywords:</strong> {{ $term->keywords }}
            </div>
        @endif
    </div>

</main>
@endsection
