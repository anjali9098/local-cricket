@extends('layouts.app')

@section('content')
<main class="container py-6 sm:py-10">
    <div class="section-header mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight">TOURNAMENTS & SERIES HUB</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">All ongoing, upcoming, and completed cricket series</p>
        </div>
    </div>

    <!-- Ongoing Series -->
    <div class="mb-8">
        <h3 class="text-sm sm:text-base font-extrabold text-emerald-400 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span>🟢</span> ONGOING TOURNAMENTS
        </h3>
        <div class="series-grid">
            @forelse($ongoingSeries as $s)
                <a href="{{ route('tournament.public', $s->id) }}" class="series-card no-underline block">
                    <div class="series-info">
                        <h4 class="series-title">{{ $s->name }}</h4>
                        <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                    </div>
                    <div class="series-arrow">&rarr;</div>
                </a>
            @empty
                <p class="text-gray-400 text-xs py-2">No ongoing tournaments right now.</p>
            @endforelse
        </div>
    </div>

    <!-- Upcoming Series -->
    <div class="mb-8">
        <h3 class="text-sm sm:text-base font-extrabold text-sky-400 uppercase tracking-wide mb-3 flex items-center gap-2">
            <span>🔵</span> UPCOMING TOURNAMENTS
        </h3>
        <div class="series-grid">
            @forelse($upcomingSeries as $s)
                <a href="{{ route('tournament.public', $s->id) }}" class="series-card no-underline block">
                    <div class="series-info">
                        <h4 class="series-title">{{ $s->name }}</h4>
                        <span class="series-location">{{ $s->city ?? 'Multiple' }} &bull; {{ $s->year ?? '2026' }}</span>
                    </div>
                    <div class="series-arrow">&rarr;</div>
                </a>
            @empty
                <p class="text-gray-400 text-xs py-2">No upcoming tournaments added yet.</p>
            @endforelse
        </div>
    </div>
</main>
@endsection
