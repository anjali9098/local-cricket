@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 40px 24px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Header -->
    <div style="text-align: center; margin-bottom: 32px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: #10b981; text-transform: uppercase; display: block; margin-bottom: 6px;">
            CRICKET STADIUM DIRECTORY
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 6px 0;">
            Cricket Venues &amp; Stadiums
        </h1>
        <p style="font-size: 0.95rem; color: var(--text-muted); margin: 0 auto; max-width: 600px;">
            Explore international and domestic cricket stadiums, seating capacities, locations, and match histories.
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 20px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main);">
            Showing {{ $allVenues->count() }} Stadiums
        </div>

        <form method="GET" action="{{ route('venues') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Search Input -->
            <input type="text" name="search" value="{{ request('search', '') }}" placeholder="Search stadium or city..." style="padding: 8px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; outline: none; width: 200px;">

            <!-- Country Filter -->
            <select name="country" onchange="this.form.submit()" style="padding: 8px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card-secondary); color: var(--text-main); font-size: 0.88rem; font-weight: 700; outline: none; cursor: pointer;">
                <option value="">All Countries</option>
                @foreach($countries as $cntry)
                    <option value="{{ $cntry }}" {{ request('country') == $cntry ? 'selected' : '' }}>
                        {{ $cntry }}
                    </option>
                @endforeach
            </select>

            <button type="submit" style="padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                Filter
            </button>

            @if(request('country') || request('search'))
                <a href="{{ route('venues') }}" style="text-decoration: none; font-size: 0.82rem; font-weight: 700; color: #f87171; background: rgba(239, 68, 68, 0.1); padding: 7px 12px; border-radius: 6px;">
                    ✕ Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Venues Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 22px;">
        @forelse($allVenues as $venue)
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.05);"
                onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 28px rgba(0,0,0,0.2)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';">
                
                <div>
                    <!-- Image with click to detail -->
                    <a href="{{ route('venues.show', $venue->id) }}" style="display: block; position: relative; height: 160px; overflow: hidden;">
                        @if(!empty($venue->image_url))
                            <img src="{{ $venue->image_url }}" alt="{{ $venue->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                        @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                🏟️
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 50%; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
                        @if($venue->country)
                            <span style="position: absolute; top: 10px; right: 10px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: white; font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">
                                📍 {{ $venue->country }}
                            </span>
                        @endif
                    </a>

                    <!-- Venue Info -->
                    <div style="padding: 16px 18px 12px;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0; line-height: 1.3;">
                            <a href="{{ route('venues.show', $venue->id) }}" style="text-decoration: none; color: inherit;">
                                {{ $venue->name }}
                            </a>
                        </h3>

                        <div style="font-size: 0.82rem; color: var(--text-dim); margin-bottom: 12px; display: flex; flex-direction: column; gap: 4px;">
                            @if($venue->city)
                                <div>📍 <strong>{{ $venue->city }}</strong>{{ $venue->country ? ', ' . $venue->country : '' }}</div>
                            @endif
                            @if($venue->capacity)
                                <div>👥 Capacity: <strong>{{ is_numeric($venue->capacity) ? number_format($venue->capacity) : $venue->capacity }}</strong></div>
                            @endif
                        </div>

                        @if(!empty($venue->description))
                            <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.45; margin: 0 0 14px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $venue->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Footer Links & Actions -->
                <div style="padding: 12px 18px; border-top: 1px solid var(--border-color); background: var(--bg-card-secondary); display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <!-- Maps Hyperlink -->
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($venue->name . ' ' . $venue->city . ' ' . $venue->country) }}" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; font-weight: 700; color: #10b981; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        View on Map
                    </a>

                    <!-- Detail Link -->
                    <a href="{{ route('venues.show', $venue->id) }}" style="text-decoration: none; font-size: 0.8rem; font-weight: 800; color: white; background: #0284c7; padding: 6px 14px; border-radius: 6px; transition: opacity 0.15s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
                        Stadium Details &rarr;
                    </a>
                </div>

            </div>
        @empty
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 48px; text-align: center; grid-column: 1/-1;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">🏟️</div>
                <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">No stadiums or venues found matching this filter.</p>
            </div>
        @endforelse
    </div>

</main>
@endsection
