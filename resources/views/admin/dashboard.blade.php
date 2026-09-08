@extends('layouts.admin')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Dashboard Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.02em;">SUPER ADMIN PANEL</h1>
            <p style="font-size: 0.95rem; color: #64748b; margin: 4px 0 0 0;">Overview of tournaments, matches, and news content statistics.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.match') }}" style="background: #0ea5e9; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 0.92rem; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15);">
                + Create Match
            </a>
            <a href="{{ route('admin.series') }}" style="background: #3b82f6; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 0.92rem; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);">
                + Add Series
            </a>
        </div>
    </div>

    <!-- Pending Requests (Approvals & Deletions) -->
    @if($pendingApprovals->isNotEmpty() || $pendingDeletions->isNotEmpty())
        @php
            $pendingIds = $pendingApprovals->pluck('id')->merge($pendingDeletions->pluck('id'))->unique()->sort()->implode(',');
        @endphp
        <script>
            if (localStorage.getItem('admin_seen_notifications') === "{{ $pendingIds }}") {
                document.write('<style>#dashboard-notifications-banner { display: none !important; }</style>');
            }
        </script>
        <div id="dashboard-notifications-banner" style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 24px; margin-bottom: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: opacity 1s ease, max-height 1s ease, padding 1s ease, margin 1s ease, border 1s ease; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #b45309; margin: 0; display: flex; align-items: center; gap: 8px;">
                    🔔 Pending Requests (Tournament Approvals & Deletions)
                </h3>
                <button type="button" onclick="dismissDashboardNotifications()" style="background: transparent; border: none; color: #b45309; font-size: 1.5rem; font-weight: bold; cursor: pointer; line-height: 1; padding: 0 4px;">&times;</button>
            </div>
            
            @if($pendingApprovals->isNotEmpty())
                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #d97706; margin: 0 0 10px 0;">Tournament Approval Requests</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($pendingApprovals as $t)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #fef3c7; padding: 12px 16px; border-radius: 8px;">
                                <div>
                                    <span style="font-weight: 800; color: #1e293b;">{{ $t->name }}</span> 
                                    <span style="color: #64748b; font-size: 0.85rem;">({{ $t->format }}, {{ $t->city ?? 'Local' }}) &bull; Created by: <strong>{{ $t->user->email ?? 'Local User' }}</strong></span>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <form method="POST" action="{{ route('admin.approve-tournament', $t->id) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to reject/delete this tournament?');">
                                        @csrf
                                        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                            Reject & Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pendingDeletions->isNotEmpty())
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #b91c1c; margin: 0 0 10px 0;">Tournament Deletion Requests</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($pendingDeletions as $t)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid #fee2e2; padding: 12px 16px; border-radius: 8px;">
                                <div>
                                    <span style="font-weight: 800; color: #1e293b;">{{ $t->name }}</span> 
                                    <span style="color: #64748b; font-size: 0.85rem;">({{ $t->format }}, {{ $t->city ?? 'Local' }}) &bull; Requested by: <strong>{{ $t->user->email ?? 'Local User' }}</strong></span>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to permanently delete this tournament?');">
                                        @csrf
                                        <button type="submit" style="background: #b91c1c; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                            Approve Deletion
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reject-deletion', $t->id) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="background: #64748b; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Stats Panel -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">TOTAL TOURNAMENTS</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #0f172a;">{{ $tournCount }}</div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">TOTAL VIEWS</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #0f172a;">{{ $totalViews }}</div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">TOTAL MATCHES</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #0f172a;">{{ $matchCount }}</div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">LIVE MATCHES</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #ef4444; display: flex; align-items: center; gap: 8px;">
                <span style="display:inline-block; width:12px; height:12px; background:#ef4444; border-radius:50%;"></span>
                {{ $liveCount }}
            </div>
        </div>
    </div>

    <!-- Active Tournaments List -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Recent Series / Tournaments</h3>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Total: {{ $adminTournaments->count() }} Tournaments</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <th style="padding:12px; text-align:left; color:#64748b; font-weight:700; font-size: 0.85rem;">TOURNAMENT</th>
                        <th style="padding:12px; text-align:left; color:#64748b; font-weight:700; font-size: 0.85rem;">FORMAT</th>
                        <th style="padding:12px; text-align:left; color:#64748b; font-weight:700; font-size: 0.85rem;">TYPE</th>
                        <th style="padding:12px; text-align:left; color:#64748b; font-weight:700; font-size: 0.85rem;">STATUS</th>
                        <th style="padding:12px; text-align:center; color:#64748b; font-weight:700; font-size: 0.85rem;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminTournaments as $t)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding:12px;">
                                <div style="font-weight:700; color:#0f172a; font-size: 0.95rem;">{{ $t->name }}</div>
                                <div style="font-size: 0.75rem; color:#64748b; font-weight:600; margin-top: 2px;">
                                    {{ $t->city ? '📍 ' . $t->city . ' • ' : '' }}By: {{ $t->user->name ?? 'Super Admin' }}
                                </div>
                            </td>
                            <td style="padding:12px; color:#475569; font-size: 0.9rem;">
                                <span style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-weight:700; color:#1e293b;">{{ $t->format }}</span>
                            </td>
                            <td style="padding:12px; color:#475569; font-size: 0.85rem; font-weight:700;">
                                <span style="background: {{ $t->category === 'global' ? '#ede9fe' : '#e0f2fe' }}; color: {{ $t->category === 'global' ? '#6b21a8' : '#0369a1' }}; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; text-transform: uppercase;">
                                    {{ $t->category ?? ($t->series_type ?? 'LOCAL') }}
                                </span>
                            </td>
                            <td style="padding:12px;">
                                @if(!$t->is_approved)
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 800;">Pending</span>
                                @elseif($t->status === 'completed')
                                    <span style="background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 800;">Completed</span>
                                @elseif($t->status === 'ongoing' || $t->status === 'live')
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 800;">Live</span>
                                @else
                                    <span style="background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700;">{{ ucfirst($t->status) }}</span>
                                @endif
                            </td>
                            <td style="padding:12px; text-align:center; font-size: 0.88rem;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="{{ route('admin.manage-tournament', $t->id) }}" style="background: #0ea5e9; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Manage</a>
                                    <a href="{{ route('admin.tournament.preview', $t->id) }}" style="background: #f1f5f9; border: 1px solid #e2e8f0; color: #334155; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Preview</a>
                                    <form method="POST" action="{{ route('admin.delete-tournament', $t->id) }}" onsubmit="return confirm('Are you sure you want to permanently delete tournament \'{{ addslashes($t->name) }}\'? This will delete all matches and teams inside it.');" style="display:inline; margin:0;">
                                        @csrf
                                        <button type="submit" style="background: transparent; color: #ef4444; border: 1px solid #fca5a5; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:20px; text-align:center; color:#64748b; font-size: 0.9rem;">No tournaments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Matches List -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 20px 0;">Recent Match Center</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($adminMatches as $index => $match)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span>{{ $match->team1?->name ?? 'Team 1' }} vs {{ $match->team2?->name ?? 'Team 2' }}</span>
                            @if($match->status === 'completed' || $match->status === 'live' || $match->team1_score > 0)
                                <span style="font-size: 0.85rem; color: #0284c7; font-weight: 700; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">
                                    {{ $match->team1?->short_name ?? 'T1' }}: {{ $match->team1_score }}/{{ $match->team1_wickets }} | {{ $match->team2?->short_name ?? 'T2' }}: {{ $match->team2_score }}/{{ $match->team2_wickets }}
                                </span>
                            @endif
                        </div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;">
                            {{ $match->level_type ?? 'GLOBAL MATCH' }} &bull; {{ $match->match_type }}
                            @if($match->status === 'completed')
                                &bull; <strong style="color: #16a34a; text-transform: none;">🏆 {{ $match->winning_title }}</strong>
                            @elseif($match->custom_note)
                                &bull; <span>{{ $match->custom_note }}</span>
                            @elseif($match->match_date)
                                &bull; <span>{{ date('M d, Y - h:i A', strtotime($match->match_date)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($match->status === 'live')
                            <span style="background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">● live</span>
                        @elseif($match->status === 'completed')
                            <span style="background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">✓ completed</span>
                        @else
                            <span style="background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">upcoming</span>
                        @endif

                        @if($match->status === 'live')
                            <a href="{{ route('admin.scorer', $match->id) }}" style="background: #0ea5e9; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 5px;">Live Scorer</a>
                        @elseif($match->status === 'completed')
                            <a href="{{ route('admin.scorer', $match->id) }}" style="background: #10b981; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 5px;">Scorecard</a>
                        @else
                            <a href="{{ route('admin.toss', $match->id) }}" style="background: #0ea5e9; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 5px;">Start Match</a>
                        @endif

                        <a href="{{ route('admin.match.detail', $match->id) }}" style="background: white; border: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none;">View Details</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; color: #94a3b8; font-weight: 600;">No matches registered yet.</div>
            @endforelse
        </div>
    </div>

</div>

<script>
    function dismissDashboardNotifications() {
        const banner = document.getElementById('dashboard-notifications-banner');
        if (banner) {
            // Save current batch IDs to localStorage so it doesn't show again on refresh
            const currentIds = "{{ $pendingIds ?? '' }}";
            if (currentIds) {
                localStorage.setItem('admin_seen_notifications', currentIds);
            }
            
            banner.style.opacity = '0';
            banner.style.maxHeight = banner.scrollHeight + 'px';
            setTimeout(() => {
                banner.style.maxHeight = '0';
                banner.style.padding = '0';
                banner.style.margin = '0';
                banner.style.border = 'none';
                setTimeout(() => {
                    banner.remove();
                }, 1000);
            }, 50);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('dashboard-notifications-banner');
        if (banner) {
            const currentIds = "{{ $pendingIds ?? '' }}";
            const seenIds = localStorage.getItem('admin_seen_notifications');
            
            if (seenIds === currentIds) {
                banner.remove();
            } else {
                setTimeout(() => {
                    dismissDashboardNotifications();
                }, 30000);
            }
        }
    });
</script>
@endsection
