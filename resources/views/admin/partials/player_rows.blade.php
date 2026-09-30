@forelse($players as $item)
    <tr class="tbl-player-row" data-role="{{ strtolower($item->role ?? '') }}" data-team="{{ strtolower($item->team->name ?? '') }}" data-country="{{ strtolower($item->country ?? '') }}" data-name="{{ strtolower($item->name ?? '') }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='none';">
        
        <!-- # EDIT -->
        <td style="padding: 12px 10px; vertical-align: middle;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <a href="{{ route('admin.players', ['edit' => $item->id]) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 0.9rem;">
                     {{ $item->id }}
                </a>
                <a href="{{ route('admin.players', ['edit' => $item->id]) }}" title="Edit Player" style="color: #0284c7; text-decoration: none;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </a>
            </div>
            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                Jersey: <strong>#{{ $item->jersey_number ?: '-' }}</strong>
            </div>
        </td>

        <!-- ORDER -->
        <td style="padding: 12px 10px; vertical-align: middle;">
            <input type="number" value="{{ $item->display_order ?? 1 }}" min="1" style="width: 44px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; text-align: center; color: #1e293b; outline: none;">
        </td>

        <!-- POSTER -->
        <td style="padding: 12px 10px; vertical-align: middle;">
            @if(!empty($item->profile_image))
                <img src="{{ $item->profile_image }}" alt="Photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; display: block;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; border: 1px solid #cbd5e1; display: none; align-items: center; justify-content: center; font-size: 0.8rem; color: #0284c7; font-weight: 800;">
                    {{ $item->initials ?: substr($item->name, 0, 2) }}
                </div>
            @else
                <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #0284c7; font-weight: 800;">
                    {{ $item->initials ?: substr($item->name, 0, 2) }}
                </div>
            @endif
        </td>

        <!-- NAME -->
        <td style="padding: 12px 14px; vertical-align: middle;">
            <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 3px; display: flex; align-items: center; gap: 6px;">
                {{ $item->name }}
                @if($item->is_popular)
                    <span style="font-size: 0.68rem; background: #fef3c7; color: #b45309; font-weight: 800; padding: 2px 6px; border-radius: 4px;">★ POPULAR</span>
                @endif
            </div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                {{ $item->batting_style ?: 'Batting' }} &bull; {{ $item->bowling_style ?: 'Bowling' }}
            </div>
        </td>

        <!-- PAGE LINK / TEAM -->
        <td style="padding: 12px 14px; vertical-align: middle;">
            <div style="margin-bottom: 2px;">
                <a href="{{ route('players') }}" target="_blank" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 3px;">
                    {{ $item->slug ?: \Illuminate\Support\Str::slug($item->name) }}
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
            </div>
            <div style="font-size: 0.75rem; color: #475569; font-weight: 600;">
                Team: <strong>{{ $item->team ? $item->team->name : 'No Team' }}</strong>
            </div>
        </td>

        <!-- ROLE, DOB & COUNTRY -->
        <td style="padding: 12px 12px; vertical-align: middle; font-weight: 700; color: #1e293b;">
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                    {{ $item->role }}
                </span>
                @if($item->date_of_birth)
                    <span style="background: #fdf2f8; color: #db2777; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                        🎂 {{ date('d M Y', strtotime($item->date_of_birth)) }}
                    </span>
                @endif
            </div>
            <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                {{ $item->country ?: ($item->nationality ?: 'India') }}
            </div>
        </td>

        <!-- ADD/UPDATE -->
        <td style="padding: 12px 14px; vertical-align: middle; font-size: 0.75rem; color: #475569; line-height: 1.4;">
            <div>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
            <div>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s') }} - <strong>Admin</strong></div>
        </td>

        <!-- ACTION -->
        <td style="padding: 12px 12px; vertical-align: middle; text-align: right;">
            <form method="POST" action="{{ route('admin.players.delete', $item->id) }}" onsubmit="return confirm('Delete player \'{{ addslashes($item->name) }}\'?');" style="display:inline; margin:0;">
                @csrf
                <button type="submit" style="background: #fee2e2; color: #b91c1c; border: none; font-weight: 700; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                    Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align: center; padding: 36px 20px; color: #94a3b8; font-weight: 600; font-size: 0.95rem;">
            No matching players found.
        </td>
    </tr>
@endforelse
