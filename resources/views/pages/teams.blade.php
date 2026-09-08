@extends('layouts.app')

@section('content')
<main class="container" style="margin: 0 auto; padding: 40px 24px 80px; font-family: var(--font-body, 'Inter', sans-serif);">

    <!-- Header -->
    <div style="text-align: center; margin-bottom: 32px;">
        <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.08em; color: #38bdf8; text-transform: uppercase; display: block; margin-bottom: 6px;">
            CRICKET DIRECTORY
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 6px 0;">
            All Cricket Teams
        </h1>
        <p style="font-size: 0.95rem; color: var(--text-muted); margin: 0 auto 20px; max-width: 600px;">
            Browse international, league, domestic, and local cricket teams.
        </p>

        <!-- Live Search Bar -->
        <div style="max-width: 400px; margin: 0 auto;">
            <input type="text" id="public-team-search" oninput="filterPublicTeams()" placeholder="🔍 Search teams by name or city..." style="width: 100%; padding: 10px 16px; border: 1px solid var(--border-color); border-radius: 20px; background: var(--bg-card); color: var(--text-main); font-size: 0.9rem; outline: none; box-sizing: border-box;">
        </div>
    </div>

    <!-- Teams Grid -->
    <div id="teams-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
        @forelse($allTeams as $team)
            <div class="team-grid-card" data-name="{{ strtolower($team->name . ' ' . ($team->city ?? '') . ' ' . ($team->country ?? '') . ' ' . $team->short_name) }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;"
                onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 54px; height: 54px; border-radius: 12px; background: var(--bg-card-secondary); border: 2px solid {{ $team->color_code ?? '#38bdf8' }}; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.15rem; color: var(--text-main); flex-shrink: 0;">
                        {{ $team->short_name }}
                    </div>
                    <div>
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 3px 0;">
                            {{ $team->name }}
                        </h3>
                        <span style="font-size: 0.78rem; color: var(--text-dim); font-weight: 600;">
                            {{ $team->city ?? $team->country ?? 'Cricket Team' }}
                        </span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                    <span style="color: var(--text-dim); font-weight: 600;">
                        Squad: <strong style="color: var(--text-main);">{{ $team->players_count ?? $team->players->count() ?? 0 }} Players</strong>
                    </span>
                    <a href="{{ route('players', ['team' => $team->id]) }}" style="text-decoration: none; color: #38bdf8; font-weight: 700; font-size: 0.8rem;">
                        View Squad &rarr;
                    </a>
                </div>

            </div>
        @empty
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 40px; text-align: center; grid-column: 1/-1;">
                <p style="color: var(--text-muted); font-size: 1rem; margin: 0;">No teams registered yet.</p>
            </div>
        @endforelse
    </div>

</main>

<script>
function filterPublicTeams() {
    const query = document.getElementById('public-team-search').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.team-grid-card');
    cards.forEach(card => {
        const name = card.dataset.name || card.innerText.toLowerCase();
        if (query === '' || name.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
