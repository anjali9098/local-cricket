@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; margin: 0 auto; font-family: var(--font-body, 'Inter', sans-serif);">
    
    <!-- Header -->
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 2.4rem; color: #22c55e;">📖</span>
            <div>
                <h1 style="font-size: 1.9rem; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.02em;">
                    Cricket Glossary &amp; Terminology
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin: 4px 0 0 0;">
                    Complete dictionary of cricket terms, deliveries, rules, positions, and slang.
                </p>
            </div>
        </div>

        <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-dim);">
            Showing {{ $glossaryTerms->count() }} Terms
        </div>
    </div>

    <!-- Search & Alphabet Letter Filter -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 20px; margin-bottom: 28px; display: flex; flex-direction: column; gap: 14px;">
        
        <!-- Search row -->
        <form method="GET" action="{{ route('glossary.all') }}" style="display: flex; gap: 10px; align-items: center; max-width: 500px;">
            <input type="text" name="search" value="{{ request('search', '') }}" placeholder="Search term, googly, yorker, LBW..." style="flex: 1; padding: 8px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; outline: none;">
            <button type="submit" style="padding: 8px 18px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                Search
            </button>
            @if(request('search') || request('letter'))
                <a href="{{ route('glossary.all') }}" style="text-decoration: none; font-size: 0.82rem; font-weight: 700; color: #f87171; background: rgba(239, 68, 68, 0.1); padding: 7px 12px; border-radius: 6px;">
                    ✕ Reset
                </a>
            @endif
        </form>

        <!-- Alphabet Bar -->
        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('glossary.all') }}" style="text-decoration: none; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.78rem; {{ empty(request('letter')) ? 'background: #22c55e; color: white;' : 'background: var(--bg-card-secondary); color: var(--text-dim);' }}">
                ALL
            </a>
            @foreach(range('A', 'Z') as $char)
                @php $isActive = strtolower(request('letter')) === strtolower($char); @endphp
                <a href="{{ route('glossary.all', ['letter' => strtolower($char)]) }}" style="text-decoration: none; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: 800; font-size: 0.8rem; {{ $isActive ? 'background: #22c55e; color: white;' : 'background: var(--bg-card-secondary); color: var(--text-main);' }}">
                    {{ $char }}
                </a>
            @endforeach
        </div>

    </div>

    @if($glossaryTerms->isNotEmpty())
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 18px;">
            @foreach($glossaryTerms as $term)
                <a href="{{ route('glossary.show', $term->id) }}" style="text-decoration: none; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: flex-start; transition: all 0.2s;" onmouseover="this.style.borderColor='rgba(34, 197, 94, 0.5)'; this.style.background='var(--bg-card-hover)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-card)';">
                    
                    <!-- Poster Image or Letter Badge -->
                    @if(!empty($term->poster_image))
                        <img src="{{ $term->poster_image }}" alt="{{ $term->term }}" style="width: 54px; height: 54px; border-radius: 8px; object-fit: cover; flex-shrink: 0; border: 1.5px solid rgba(34, 197, 94, 0.4);" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 44px; height: 44px; border-radius: 8px; background: rgba(34, 197, 94, 0.15); color: #22c55e; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; flex-shrink: 0;">
                            {{ strtoupper($term->letter ?: substr($term->term, 0, 1)) }}
                        </div>
                    @else
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(34, 197, 94, 0.15); color: #22c55e; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; flex-shrink: 0;">
                            {{ strtoupper($term->letter ?: substr($term->term, 0, 1)) }}
                        </div>
                    @endif

                    <!-- Details -->
                    <div style="flex: 1;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">
                            {{ $term->term }}
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 0 0 8px 0; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $term->definition }}
                        </p>
                        @if($term->keywords)
                            <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: 600;">
                                🏷️ {{ $term->keywords }}
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 60px; text-align: center;">
            <span style="font-size: 3rem;">📚</span>
            <h3 style="color: var(--text-main); margin: 16px 0 8px 0;">No matching glossary terms</h3>
            <p style="color: var(--text-muted); margin: 0;">Try searching for another cricket keyword or clear filters.</p>
        </div>
    @endif
</main>
@endsection
