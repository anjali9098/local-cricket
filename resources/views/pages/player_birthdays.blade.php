@extends('layouts.app')

@section('content')
<main class="container" style="padding: 40px 24px 80px; margin: 0 auto; font-family: var(--font-body, 'Inter', sans-serif);">
    
    <!-- Header -->
    <div style="margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 1.8rem;">🎂</span>
                <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; margin: 0;">
                    PLAYER BIRTHDAYS
                </h1>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">
                Today's celebrations, upcoming birthdays, and calendar of international &amp; franchise cricket stars.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('players') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 10px; font-weight: 700; font-size: 0.88rem; text-decoration: none;">
                🏏 All Players
            </a>
          
        </div>
    </div>

    <!-- Today's Birthday Special Highlight (if any) -->
    @if(isset($todayBirthdays) && $todayBirthdays->isNotEmpty())
        <div style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.15), rgba(56, 189, 248, 0.08)); border: 1.5px solid var(--primary); border-radius: 18px; padding: 24px; margin-bottom: 32px; box-shadow: 0 10px 30px rgba(2, 132, 199, 0.15); position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px;">
                <span style="font-size: 1.4rem;">🎉</span>
                <h2 style="font-size: 1.2rem; font-weight: 900; color: var(--primary-text); margin: 0; text-transform: uppercase; letter-spacing: 0.03em;">
                    TODAY'S BIRTHDAY CELEBRATIONS!
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                @foreach($todayBirthdays as $pb)
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; display: flex; align-items: center; gap: 16px;">
                        @if(!empty($pb->profile_image))
                            <img src="{{ $pb->profile_image }}" alt="{{ $pb->name }}" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                            <div style="width: 56px; height: 56px; border-radius: 50%; background: #1e293b; color: var(--primary-text); font-weight: 900; font-size: 1.2rem; display: none; align-items: center; justify-content: center; border: 2px solid var(--primary);">
                                {{ strtoupper(substr($pb->name, 0, 2)) }}
                            </div>
                        @else
                            <div style="width: 56px; height: 56px; border-radius: 50%; background: #1e293b; color: var(--primary-text); font-weight: 900; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; border: 2px solid var(--primary);">
                                {{ strtoupper(substr($pb->name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 800; background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">🎂 Turning {{ $pb->current_age }} Today</span>
                            <a href="{{ route('player.profile', $pb->id) }}" style="font-size: 1.05rem; font-weight: 900; color: var(--text-main); text-decoration: none; display: block; margin: 4px 0 2px 0;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                                {{ $pb->name }}
                            </a>
                            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">
                                {{ $pb->team ? $pb->team->name : ($pb->role ?? 'Cricketer') }} &bull; {{ $pb->country ?? 'India' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
            <span style="font-size: 1.1rem;">🔍</span>
            <input type="text" id="birthdaySearchInput" oninput="filterBirthdayCards()" placeholder="Search players by name, team, country or month..." style="width: 100%; background: var(--bg-card-secondary); border: 1px solid var(--border-color); padding: 10px 14px; border-radius: 8px; font-size: 0.9rem; color: var(--text-main); outline: none;">
        </div>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim);" id="birthdayCountDisplay">
            Showing {{ count($playerBirthdays) }} Players
        </div>
    </div>

    <!-- All Player Birthdays Grid -->
    <div id="birthdayCardsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        @forelse($playerBirthdays as $pb)
            <div class="birthday-card-item" data-search="{{ strtolower($pb->name . ' ' . ($pb->team->name ?? '') . ' ' . ($pb->country ?? '') . ' ' . $pb->birthday_date_text . ' ' . $pb->formatted_dob) }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
                
                @if($pb->is_today_birthday)
                    <div style="position: absolute; top: 0; right: 0; background: linear-gradient(135deg, #0284c7, #38bdf8); color: white; font-size: 0.68rem; font-weight: 900; padding: 3px 12px; border-bottom-left-radius: 8px;">
                        🎉 TODAY!
                    </div>
                @else
                    <div style="position: absolute; top: 0; right: 0; background: var(--bg-card-secondary); border-left: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); color: var(--text-dim); font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-bottom-left-radius: 8px;">
                        In {{ $pb->days_until_birthday }} days
                    </div>
                @endif

                <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 14px;">
                    <!-- Photo -->
                    <div style="flex-shrink: 0;">
                        @if(!empty($pb->profile_image))
                            <img src="{{ $pb->profile_image }}" alt="{{ $pb->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #1e293b; color: var(--primary-text); font-weight: 900; font-size: 1.05rem; display: none; align-items: center; justify-content: center; border: 2px solid var(--primary);">
                                {{ strtoupper(substr($pb->name, 0, 2)) }}
                            </div>
                        @else
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #1e293b; color: var(--primary-text); font-weight: 900; font-size: 1.05rem; display: flex; align-items: center; justify-content: center; border: 2px solid var(--primary);">
                                {{ strtoupper(substr($pb->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div style="flex: 1; min-width: 0;">
                        <a href="{{ route('player.profile', $pb->id) }}" style="font-size: 0.95rem; font-weight: 900; color: var(--text-main); text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                            {{ $pb->name }}
                        </a>
                        <div style="font-size: 0.76rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
                            {{ $pb->team ? $pb->team->name : ($pb->role ?? 'Cricketer') }}
                        </div>
                    </div>
                </div>

                <!-- Footer Specs -->
                <div style="background: var(--bg-card-secondary); border-radius: 10px; padding: 10px 14px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.68rem; text-transform: uppercase; color: var(--text-dim); font-weight: 700; display: block;">Birth Date</span>
                        <strong style="color: var(--primary-text); font-size: 0.88rem;">🎂 {{ $pb->birthday_date_text }}</strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.68rem; text-transform: uppercase; color: var(--text-dim); font-weight: 700; display: block;">Age Status</span>
                        <strong style="color: var(--text-main); font-size: 0.84rem;">
                            {{ $pb->is_today_birthday ? 'Turning ' . $pb->current_age : 'Turns ' . $pb->turning_age }}
                        </strong>
                    </div>
                </div>

            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: var(--text-muted); font-weight: 600;">
                No player records found.
            </div>
        @endforelse
    </div>

</main>

<script>
function filterBirthdayCards() {
    const query = document.getElementById('birthdaySearchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.birthday-card-item');
    let visibleCount = 0;

    cards.forEach(card => {
        const searchData = card.getAttribute('data-search') || '';
        if (!query || searchData.includes(query)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const display = document.getElementById('birthdayCountDisplay');
    if (display) {
        display.innerText = `Showing ${visibleCount} Player${visibleCount === 1 ? '' : 's'}`;
    }
}
</script>
@endsection
