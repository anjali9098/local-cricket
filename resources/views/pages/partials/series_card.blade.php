@php
    $teamsList = $series->teams ?? collect();
    $matchesCount = $series->matches ? $series->matches->count() : 0;
    $statusText = strtoupper($series->status ?: 'ONGOING');
    $statusBg = '#2563eb';
    if (in_array($statusText, ['ONGOING', 'LIVE', 'ACTIVE'])) {
        $statusBg = '#16a34a';
    } elseif (in_array($statusText, ['COMPLETED', 'FINISHED'])) {
        $statusBg = '#9333ea';
    }
@endphp

<div class="series-hub-card" data-search="{{ strtolower($series->name . ' ' . ($series->city ?? '') . ' ' . ($series->hosting_country ?? '') . ' ' . ($series->format ?? '')) }}" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between; gap: 16px; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border-color)';">
    
    <div>
        <!-- Format, Status & Location Pills -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="background: #2563eb; color: #ffffff; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em;">
                    {{ strtoupper($series->format ?: 'T20') }}
                </span>
                <span style="background: {{ $statusBg }}; color: #ffffff; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em;">
                    {{ $statusText }}
                </span>
            </div>

            @if(!empty($series->city) || !empty($series->hosting_country))
                <span style="font-size: 0.76rem; color: var(--text-dim); font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <span>📍</span> {{ $series->city ?: $series->hosting_country }}
                </span>
            @endif
        </div>

        <!-- Series Title Link -->
        <h3 style="font-size: 1.15rem; font-weight: 900; color: var(--text-main); line-height: 1.35; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: -0.01em;">
            <a href="{{ $series->url }}" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='inherit'">
                {{ $series->name }}
            </a>
        </h3>

        <!-- Dates & Counts -->
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px;">
            @if($series->start_date)
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span>📅</span>
                    <span>{{ \Carbon\Carbon::parse($series->start_date)->format('d M Y') }}
                    @if($series->end_date)
                        &mdash; {{ \Carbon\Carbon::parse($series->end_date)->format('d M Y') }}
                    @endif
                    </span>
                </div>
            @endif
            <div style="display: flex; align-items: center; gap: 12px; font-size: 0.78rem; color: var(--text-dim);">
                <span>⚔️ <strong>{{ $matchesCount }}</strong> Matches</span>
                <span>•</span>
                <span>👥 <strong>{{ $teamsList->count() }}</strong> Teams</span>
            </div>
        </div>

        <!-- Participating Teams Avatars Preview -->
        @if($teamsList->isNotEmpty())
        <div style="background: var(--bg-card-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 12px; margin-bottom: 14px;">
            <div style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); margin-bottom: 6px;">
                Teams
            </div>
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                @foreach($teamsList->take(6) as $tm)
                    <div title="{{ $tm->name }}" style="display: inline-flex; align-items: center; gap: 4px; background: var(--bg-card); border: 1px solid var(--border-color); padding: 3px 6px; border-radius: 6px; font-size: 0.72rem; font-weight: 800; color: var(--text-main);">
                        @if(!empty($tm->logo))
                            <img src="{{ $tm->logo }}" alt="{{ $tm->name }}" style="width: 16px; height: 16px; border-radius: 3px; object-fit: contain;">
                        @endif
                        <span>{{ $tm->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $tm->name), 0, 3)) }}</span>
                    </div>
                @endforeach
                @if($teamsList->count() > 6)
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); padding-left: 2px;">+{{ $teamsList->count() - 6 }} more</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Direct Navigation Actions -->
    <div>
        <a href="{{ $series->url }}" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #2563eb; color: #ffffff; font-size: 0.86rem; font-weight: 800; text-decoration: none; padding: 10px 16px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.04em; transition: background-color 0.2s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
            <span>Explore Series</span> &rarr;
        </a>

        <!-- Quick Sub-Tabs Deep Links -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-top: 8px;">
            <a href="{{ $series->url }}#tab-stats" style="text-align: center; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; padding: 4px 2px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; transition: color 0.15s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                Stats
            </a>
            <a href="{{ $series->url }}#tab-fixtures" style="text-align: center; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; padding: 4px 2px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; transition: color 0.15s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                Fixtures
            </a>
            <a href="{{ $series->url }}#tab-points" style="text-align: center; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; padding: 4px 2px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; transition: color 0.15s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                Points
            </a>
            <a href="{{ $series->url }}#tab-teams" style="text-align: center; background: var(--bg-card-secondary); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; padding: 4px 2px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; transition: color 0.15s;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-muted)'">
                Squads
            </a>
        </div>
    </div>

</div>
