@extends('layouts.admin')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    
    

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; text-transform: uppercase;">
                {{ $tournament->name }}
            </h1>
            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem;">
                <span style="color: #64748b; font-weight: 500;">{{ $tournament->city ?? 'mumbai' }}</span>
                <span style="border: 1px solid #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 20px; font-weight: 700; text-transform: uppercase;">
                    {{ $tournament->format }}
                </span>
                
                @if($tournament->status == 'completed')
                    <span style="background: #0f172a; color: white; padding: 2px 10px; border-radius: 20px; font-weight: 700; text-transform: lowercase;">completed</span>
                @elseif($tournament->status == 'ongoing' || $tournament->status == 'live')
                    <span style="background: #ef4444; color: white; padding: 2px 10px; border-radius: 20px; font-weight: 700; text-transform: lowercase;">live</span>
                @else
                    <span style="background: #f1f5f9; color: #64748b; padding: 2px 10px; border-radius: 20px; font-weight: 700; text-transform: lowercase;">{{ $tournament->status }}</span>
                @endif
            </div>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.tournament.preview', $tournament->id) }}" style="background: #fff; border: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; padding: 10px 18px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                Public Page
            </a>
            
            @if($tournament->status == 'draft')
            <form method="POST" action="{{ route('admin.publish-tournament', $tournament->id) }}" style="margin:0;">
                @csrf
                <button type="submit" style="background: #0ea5e9; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15);">
                    Publish
                </button>
            </form>
            @elseif($tournament->status == 'published')
            <form method="POST" action="{{ route('admin.mark-ongoing', $tournament->id) }}" style="margin:0;">
                @csrf
                <button type="submit" style="background: #f97316; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.15);">
                    Mark Ongoing
                </button>
            </form>
            @elseif($tournament->status == 'ongoing')
            <form method="POST" action="{{ route('admin.mark-completed', $tournament->id) }}" style="margin:0;">
                @csrf
                <button type="submit" style="background: #16a34a; color: white; font-weight: 700; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.15);">
                    Mark Completed
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Tabs Container -->
    <div style="background: #e2e8f0; padding: 6px; border-radius: 12px; display: inline-flex; gap: 8px; margin-bottom: 24px;">
        <button class="tab-btn active" onclick="switchTab('teams', event)" style="background: white; border: none; color: #0f172a; font-weight: 700; font-size: 0.9rem; padding: 10px 20px; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Teams ({{ count($teams) }})
        </button>
        <button class="tab-btn" onclick="switchTab('players', event)" style="background: transparent; border: none; color: #64748b; font-weight: 600; font-size: 0.9rem; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Players ({{ count($players) }})
        </button>
        <button class="tab-btn" onclick="switchTab('matches', event)" style="background: transparent; border: none; color: #64748b; font-weight: 600; font-size: 0.9rem; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Matches ({{ count($matches) }})
        </button>
        <button class="tab-btn" onclick="switchTab('predictions', event)" style="background: transparent; border: none; color: #64748b; font-weight: 600; font-size: 0.9rem; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            ⚡ Predictions
        </button>
        <button class="tab-btn" onclick="switchTab('fantasy', event)" style="background: transparent; border: none; color: #64748b; font-weight: 600; font-size: 0.9rem; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            🎯 Fantasy Tips
        </button>
    </div>
    
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-btn:hover:not(.active) { color: #0f172a; }
        
        .list-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        
        .input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .input-group input, .input-group select {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            color: #0f172a;
        }
        .input-group input:focus, .input-group select:focus {
            border-color: #0ea5e9;
        }
        .input-group button {
            background: #0f172a;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 20px;
            font-size: 1.2rem;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.1);
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
        
        .item-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .item-card-stats {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>

    <!-- TEAMS TAB -->
    <div id="tab-teams" class="tab-content active">
        <div class="list-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a;">Teams</h3>

            <form method="POST" action="{{ route('admin.add-team', $tournament->id) }}" class="input-group">
                @csrf
                <input type="text" name="name" placeholder="Team name" required>
                <button type="submit">+</button>
            </form>

            <div class="grid-container">
                @foreach($teams as $team)
                @php
                    $teamMatches = $matches->filter(fn($m) => $m->team1_id == $team->id || $m->team2_id == $team->id);
                    $p = 0; $w = 0; $pts = 0;
                    foreach($teamMatches as $m) {
                        if($m->status === 'completed') {
                            $p++;
                            if(($m->team1_id == $team->id && $m->team1_score > $m->team2_score) ||
                               ($m->team2_id == $team->id && $m->team2_score > $m->team1_score)) {
                                $w++; $pts += 2;
                            }
                        }
                    }
                @endphp
                <div class="item-card">
                    <div>
                        <div class="item-card-title">{{ $team->name }}</div>
                        <div class="item-card-stats">P {{ $p }} &bull; W {{ $w }} &bull; Pts {{ $pts }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.delete-team', $team->id) }}" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this team?')">
                        @csrf
                        <button type="submit" style="background:transparent; border:none; color:#ef4444; cursor:pointer;" title="Delete Team">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- PLAYERS TAB -->
    <div id="tab-players" class="tab-content">
        <div class="list-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a;">Manage Players</h3>
            
            <form method="POST" action="{{ route('admin.add-player', $tournament->id) }}" class="input-group">
                @csrf
                <select name="team_id" required>
                    <option value="">Select Team...</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="name" placeholder="Player name" required>
                <select name="role" required>
                    <option value="Batsman">Batsman</option>
                    <option value="Bowler">Bowler</option>
                    <option value="All-Rounder">All-Rounder</option>
                    <option value="Wicket Keeper">Wicket Keeper</option>
                </select>
                <button type="submit">+</button>
            </form>
            
            <div class="grid-container" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); align-items: start;">
                @foreach($teams as $team)
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; text-transform: uppercase;">
                        {{ $team->name }}
                    </h4>
                    
                    @if($team->players->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($team->players as $player)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f8fafc; border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <span style="font-weight: 700; color: #334155;">{{ $player->name }}</span>
                                        <span style="background: white; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; color: #64748b;">{{ $player->role }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('admin.delete-player', $player->id) }}" style="margin:0;" onsubmit="return confirm('Delete player?')">
                                        @csrf
                                        <button type="submit" style="background:transparent; border:none; color:#ef4444; cursor:pointer; padding: 4px;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.9rem; color: #94a3b8; font-style: italic;">No players yet</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MATCHES TAB -->
    <div id="tab-matches" class="tab-content">
        <div class="list-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a;">Schedule Match</h3>

            <form method="POST" action="{{ route('admin.add-match', $tournament->id) }}" style="display: flex; gap: 10px; margin-bottom: 28px; flex-wrap: wrap; align-items: center;">
                @csrf
                <select name="team1_id" required style="flex: 1; min-width: 130px; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.92rem; color: #0f172a; background: white; outline: none;">
                    <option value="">Team A</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <select name="team2_id" required style="flex: 1; min-width: 130px; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.92rem; color: #0f172a; background: white; outline: none;">
                    <option value="">Team B</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <input type="datetime-local" name="scheduled_at" style="flex: 1; min-width: 180px; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none;">
                <input type="text" name="venue" placeholder="Venue" style="flex: 1; min-width: 130px; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.92rem; color: #0f172a; outline: none;">
                <button type="submit" style="background: #0f172a; color: white; border: none; border-radius: 8px; padding: 11px 22px; font-size: 0.92rem; font-weight: 800; cursor: pointer; white-space: nowrap; display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 1.1rem;">+</span> Add Match
                </button>
            </form>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($matches as $index => $match)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px;">
                    <div>
                        <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                            Match {{ $index + 1 }}: {{ $match->team1?->name ?? '' }} vs {{ $match->team2?->name ?? '' }}
                        </div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;">
                            {{ $match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('n/j/Y, g:i A') : 'TBD' }}
                            @if($match->custom_note) &bull; {{ $match->custom_note }} @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($match->status === 'live')
                            <span style="background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">live</span>
                        @elseif($match->status === 'completed')
                            <span style="background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">completed</span>
                        @else
                            <span style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">scheduled</span>
                        @endif

                        {{-- Score button: go to toss if not started, else directly to scorer --}}
                        @if($match->status === 'live')
                            <a href="{{ route('admin.scorer', $match->id) }}" style="background: #0f172a; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> Score
                            </a>
                        @else
                            <a href="{{ route('admin.toss', $match->id) }}" style="background: #0f172a; color: white; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> Score
                            </a>
                        @endif

                        <a href="{{ route('admin.match.detail', $match->id) }}" style="background: white; border: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 8px; text-decoration: none;">View</a>

                        <form method="POST" action="{{ route('admin.delete-match', $match->id) }}" style="margin:0;" onsubmit="return confirm('Delete this match?')">
                            @csrf
                            <button type="submit" style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 6px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 32px; color: #94a3b8; font-weight: 600;">No matches scheduled yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- PREDICTIONS TAB -->
    <div id="tab-predictions" class="tab-content">
        <div class="list-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a;">Match Predictions</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px;">Publish match predictions to appear on the homepage.</p>
            
            <form method="POST" action="{{ route('admin.add-prediction', $tournament->id) }}" style="display: flex; flex-direction: column; gap: 16px; max-width: 500px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Prediction Title</label>
                    <input type="text" name="title" placeholder="e.g., Team A to win against Team B" required style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 0.95rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Summary / Key Points</label>
                    <textarea name="summary" rows="3" placeholder="Brief details about the prediction..." required style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 0.95rem; resize: vertical;"></textarea>
                </div>
                <button type="submit" style="background: #0f172a; color: white; font-weight: 700; font-size: 0.95rem; padding: 12px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    Publish Prediction
                </button>
            </form>
        </div>
    </div>

    <!-- FANTASY TIPS TAB -->
    <div id="tab-fantasy" class="tab-content">
        <div class="list-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 20px 0; color: #0f172a;">Fantasy Tips</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 24px;">Publish fantasy tips to appear on the homepage.</p>
            
            <form method="POST" action="{{ route('admin.add-fantasy-tip', $tournament->id) }}" style="display: flex; flex-direction: column; gap: 16px; max-width: 500px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Tip Title</label>
                    <input type="text" name="title" placeholder="e.g., Must-have players for today" required style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 0.95rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Summary / Players</label>
                    <textarea name="summary" rows="3" placeholder="Brief details about the fantasy tip..." required style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 0.95rem; resize: vertical;"></textarea>
                </div>
                <button type="submit" style="background: #0ea5e9; color: white; font-weight: 700; font-size: 0.95rem; padding: 12px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    Publish Fantasy Tip
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    function switchTab(tabId, event) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
            el.style.background = 'transparent';
            el.style.boxShadow = 'none';
            el.style.color = '#64748b';
        });
        
        event.currentTarget.classList.add('active');
        event.currentTarget.style.background = 'white';
        event.currentTarget.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        event.currentTarget.style.color = '#0f172a';
    }
</script>
@endsection
