<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tournament;
use App\Models\CricketMatch;
use App\Models\FantasyTip;

class LocalController extends Controller
{
    private function authorizeTournament($tournamentId)
    {
        $tournament = Tournament::findOrFail($tournamentId);
        
        // Superadmin and admin have full management access
        if (Auth::check() && (Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin')) {
            return $tournament;
        }

        // Strict Ownership Enforcement: Users can ONLY manage and edit their own tournaments
        if (!Auth::check() || $tournament->user_id != Auth::id()) {
            abort(403, 'Unauthorized: You can only edit, manage, and create matches in your own tournaments.');
        }

        return $tournament;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please sign in or register to access your CrickArena Local Dashboard.');
        }

        $user = Auth::user();
        
        // 1. My Tournaments (Created by the logged-in user - Full Management Permissions)
        $myTournaments = Tournament::where('category', 'local')
            ->where('user_id', $user->id)
            ->with(['user', 'teams', 'matches'])
            ->orderBy('id', 'desc')
            ->get();

        // 2. Community & Other Tournaments (Created by other users - View Only Permissions)
        $otherTournaments = Tournament::where('category', 'local')
            ->where(function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                      ->orWhereNull('user_id');
            })
            ->with(['user', 'teams', 'matches'])
            ->orderBy('id', 'desc')
            ->get();

        // Counts for user's own local tournaments
        $upcomingCount = $myTournaments->whereIn('status', ['draft', 'published', 'scheduled'])->count();
        $ongoingCount = $myTournaments->whereIn('status', ['ongoing', 'live'])->count();
        $completedCount = $myTournaments->where('status', 'completed')->count();
        $totalTournaments = $myTournaments->count();
        $totalViews = $myTournaments->sum('views_count');

        return view('local.dashboard', compact(
            'myTournaments',
            'otherTournaments',
            'totalTournaments',
            'totalViews',
            'user',
            'upcomingCount',
            'ongoingCount',
            'completedCount'
        ));
    }

    public function manageTournament($id)
    {
        $tournament = $this->authorizeTournament($id);
        $teams = \App\Models\Team::where('tournament_id', $id)->get();
        $teamIds = $teams->pluck('id');
        $players = \App\Models\Player::whereIn('team_id', $teamIds)->get();
        $matches = CricketMatch::where('tournament_id', $id)->with(['team1', 'team2'])->orderBy('id', 'desc')->get();

        return view('local.manage-tournament', compact('tournament', 'teams', 'players', 'matches'));
    }

    public function createTournament(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $name = trim($request->input('name', ''));
        $city = trim($request->input('city', ''));
        $state = trim($request->input('state', ''));
        $venue = trim($request->input('venue', ''));
        $format = trim($request->input('format', 'T20'));
        $overs = (int)$request->input('overs', 20);
        $type = trim($request->input('type', 'Knockout'));
        $bannerUrl = trim($request->input('banner_url', ''));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $description = trim($request->input('description', ''));
        $userId = Auth::id();

        if (!empty($name)) {
            $shortName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
            Tournament::create([
                'user_id' => $userId,
                'name' => $name,
                'short_name' => $shortName,
                'format' => $format,
                'series_type' => 'LOCAL',
                'category' => 'local',
                'city' => $city,
                'state' => $state,
                'venue' => $venue,
                'overs' => $overs,
                'type' => $type,
                'banner_url' => $bannerUrl,
                'start_date' => $startDate ?: null,
                'end_date' => $endDate ?: null,
                'description' => $description,
                'year' => date('Y'),
                'status' => 'draft',
                'is_approved' => false,
                'delete_requested' => false,
                'views_count' => 0,
                'reward_tier' => 'ROOKIE'
            ]);

            return back()->with('success', 'Tournament created successfully in Draft! Sent to Super Admin for approval.');
        }

        return redirect()->route('local.dashboard')->with('error', 'Tournament name is required.');
    }

    public function addMatch(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        
        if ($tournament->teams()->count() < 2) {
            return back()->with('error', 'Please add at least 2 teams to this tournament before scheduling a match.');
        }

        $team1Id = $request->input('team1_id');
        $team2Id = $request->input('team2_id');
        
        if ($team1Id == $team2Id) {
            return back()->with('error', 'Teams must be different');
        }

        $status = $request->input('status', 'scheduled');
        if (!in_array($status, ['scheduled', 'live'])) {
            $status = 'scheduled';
        }

        $venue = trim($request->input('venue', ''));
        $customNote = $status === 'live' ? ($venue ? $venue . ' • In Progress' : 'Match in progress') : ($venue ?: 'Match Scheduled');
        
        $tournament->matches()->create([
            'team1_id' => $team1Id,
            'team2_id' => $team2Id,
            'match_type' => $tournament->format,
            'status' => $status,
            'custom_note' => $customNote,
            'match_date' => $request->input('scheduled_at') ?: now(),
            'level_type' => 'LOCAL'
        ]);

        if ($status === 'live' && $tournament->status !== 'ongoing') {
            $tournament->status = 'ongoing';
            $tournament->save();
        }
        
        return back()->with('success', $status === 'live' ? 'Match started live successfully!' : 'Match scheduled successfully!');
    }

    public function updateMatchStatus(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);

        $status = $request->input('status', 'live');
        if (in_array($status, ['live', 'scheduled', 'completed'])) {
            $match->status = $status;
            if ($status === 'live' && (empty($match->custom_note) || $match->custom_note === 'Match Scheduled')) {
                $match->custom_note = 'Match in progress';
            } elseif ($status === 'scheduled' && $match->custom_note === 'Match in progress') {
                $match->custom_note = 'Match Scheduled';
            }
            $match->save();

            // Also ensure tournament is marked ongoing if match goes live
            if ($status === 'live' && $match->tournament) {
                if ($match->tournament->status !== 'ongoing') {
                    $match->tournament->status = 'ongoing';
                    $match->tournament->save();
                }
            }
        }

        return back()->with('success', "Match status updated to " . ucfirst($status) . "!");
    }

    public function addNews(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        Auth::user()->news()->create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => 'LOCAL NEWS',
            'summary' => substr($request->content, 0, 100) . '...'
        ]);

        return back()->with('success', 'News published successfully!');
    }


    public function markOngoing($id)
    {
        $tournament = $this->authorizeTournament($id);
        $tournament->status = 'ongoing';
        $tournament->save();
        return back()->with('success', 'Tournament marked as ongoing!');
    }

    public function markCompleted($id)
    {
        $tournament = $this->authorizeTournament($id);
        $tournament->status = 'completed';
        $tournament->save();
        return back()->with('success', 'Tournament marked as completed!');
    }

    public function scorer($id)
    {
        $match = CricketMatch::with(['team1.players', 'team2.players', 'tournament', 'battingStats', 'bowlingStats'])->findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        $isLocal = true;
        
        // Fetch ALL balls of the match (ordered chronologically)
        $allBalls = \App\Models\BallByBall::where('match_id', $id)
            ->orderBy('id', 'asc')
            ->get();
            
        // Also provide last 24 balls for immediate quick bar
        $lastBalls = $allBalls->take(-24);
        
        return view('admin.scorer', compact('match', 'isLocal', 'lastBalls', 'allBalls'));
    }

    public function deleteTeam($id)
    {
        $team = \App\Models\Team::findOrFail($id);
        $this->authorizeTournament($team->tournament_id);

        // Delete matches associated with this team
        $matches = \App\Models\CricketMatch::where('team1_id', $id)->orWhere('team2_id', $id)->get();
        foreach ($matches as $m) {
            \App\Models\BallByBall::where('match_id', $m->id)->delete();
            \App\Models\PlayerBattingStat::where('match_id', $m->id)->delete();
            \App\Models\PlayerBowlingStat::where('match_id', $m->id)->delete();
            $m->delete();
        }

        // Delete players of this team
        \App\Models\Player::where('team_id', $id)->delete();

        $team->delete();
        return back()->with('success', 'Team and related matches deleted successfully.');
    }

    public function deletePlayer($id)
    {
        $player = \App\Models\Player::findOrFail($id);
        $team = \App\Models\Team::findOrFail($player->team_id);
        $this->authorizeTournament($team->tournament_id);
        $player->delete();
        return back()->with('success', 'Player deleted successfully.');
    }

    public function updateScore(Request $request)
    {
        $match = \App\Models\CricketMatch::findOrFail($request->match_id);
        $this->authorizeTournament($match->tournament_id);
        
        \App\Services\CricketScorerService::recordBall(
            $request->match_id,
            (int)$request->input('runs', 0),
            $request->input('extras'),
            $request->has('is_wicket'),
            $request->input('striker_name'),
            $request->input('non_striker_name'),
            $request->input('bowler_name'),
            $request->input('new_batsman_name')
        );

        return back()->with('success', 'Ball recorded successfully!');
    }

    public function undoScore(Request $request)
    {
        $match = \App\Models\CricketMatch::findOrFail($request->match_id);
        $this->authorizeTournament($match->tournament_id);
        
        $success = \App\Services\CricketScorerService::undoBall($request->match_id);
        if ($success) {
            return back()->with('success', 'Last ball undone successfully!');
        }
        return back()->with('error', 'No balls to undo!');
    }

    public function changeActivePlayers(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);

        $strikerName = trim($request->input('striker_name', ''));
        $nonStrikerName = trim($request->input('non_striker_name', ''));
        $bowlerName = trim($request->input('bowler_name', ''));

        if (!empty($strikerName)) {
            \App\Models\PlayerBattingStat::where('match_id', $id)->where('status_text', 'striker')->update(['status_text' => 'not out']);
            \App\Models\PlayerBattingStat::updateOrCreate(
                ['match_id' => $id, 'player_name' => $strikerName],
                ['status_text' => 'striker']
            );
        }

        if (!empty($nonStrikerName)) {
            \App\Models\PlayerBattingStat::where('match_id', $id)->where('status_text', 'non-striker')->update(['status_text' => 'not out']);
            \App\Models\PlayerBattingStat::updateOrCreate(
                ['match_id' => $id, 'player_name' => $nonStrikerName],
                ['status_text' => 'non-striker']
            );
        }

        if (!empty($bowlerName)) {
            \App\Models\PlayerBowlingStat::firstOrCreate(
                ['match_id' => $id, 'player_name' => $bowlerName],
                ['overs' => 0.0, 'runs' => 0, 'wickets' => 0, 'economy' => 0.00]
            );
        }

        return back()->with('success', 'Active players updated!');
    }

    public function switchInnings(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);

        $innings = (int)$request->input('innings', 2);
        \App\Services\CricketScorerService::startInnings(
            $id,
            $request->input('striker_id'),
            $request->input('non_striker_id'),
            $request->input('bowler_id'),
            $innings
        );

        return redirect()->route('local.scorer', $id)->with('success', "Innings $innings started!");
    }
        
    public function addPrediction(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        
        $request->validate([
            'title' => 'required',
            'summary' => 'required'
        ]);

        \App\Models\Prediction::create([
            'match_title' => $tournament->short_name . ' Match',
            'title' => $request->title,
            'summary' => $request->summary,
            'tag' => 'PREDICTION'
        ]);

        return back()->with('success', 'Match prediction added successfully!');
    }

    public function addFantasyTip(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        
        $request->validate([
            'title' => 'required',
            'summary' => 'required'
        ]);

        FantasyTip::create([
            'title' => $request->title,
            'summary' => $request->summary,
            'tag' => 'FANTASY'
        ]);

        return back()->with('success', 'Fantasy tip added successfully!');
    }

    public function publishTournament($id)
    {
        $tournament = $this->authorizeTournament($id);
        $tournament->status = 'published';
        $tournament->save();
        return back()->with('success', 'Tournament published successfully!');
    }

    public function addTeam(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        $name = trim($request->input('name'));
        if ($name) {
            \App\Models\Team::create([
                'tournament_id' => $id,
                'name' => $name,
                'short_name' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)),
                'color_code' => '#2563eb'
            ]);
        }
        return back()->with('success', 'Team added!');
    }

    public function addPlayer(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        $team = \App\Models\Team::where('tournament_id', $id)->findOrFail($request->input('team_id'));
        \App\Models\Player::create([
            'team_id' => $team->id,
            'name' => $request->input('name'),
            'role' => $request->input('role', 'batsman')
        ]);
        return back()->with('success', 'Player added!');
    }

    public function toss($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'tournament'])->findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        $isLocal = true;
        return view('admin.toss', compact('match', 'isLocal'));
    }

    public function saveToss(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        $tossWinnerId  = $request->input('toss_winner_id');
        $decision      = $request->input('decision'); // 'bat' or 'field'

        // Determine batting team for innings 1
        if ($decision === 'bat') {
            $battingTeamId = $tossWinnerId;
        } else {
            $battingTeamId = ($tossWinnerId == $match->team1_id) ? $match->team2_id : $match->team1_id;
        }

        $match->result_text = 'toss:' . $tossWinnerId . ':' . $decision;
        $match->current_innings = 1;
        $match->save();

        return redirect()->route('local.opening-players', ['id' => $id, 'batting_team_id' => $battingTeamId]);
    }

    public function openingPlayers($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'tournament'])->findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        $battingTeamId = request('batting_team_id');
        $battingTeam   = \App\Models\Team::with('players')->findOrFail($battingTeamId);
        $bowlingTeamId = ($battingTeamId == $match->team1_id) ? $match->team2_id : $match->team1_id;
        $bowlingTeam   = \App\Models\Team::with('players')->findOrFail($bowlingTeamId);
        $isLocal = true;
        return view('admin.opening_players', compact('match', 'battingTeam', 'bowlingTeam', 'isLocal'));
    }

    public function startInnings(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        
        \App\Services\CricketScorerService::startInnings(
            $id,
            $request->input('striker_id'),
            $request->input('non_striker_id'),
            $request->input('bowler_id'),
            1
        );

        return redirect()->route('local.scorer', $id)->with('success', 'Innings 1 started! Striker and Bowler active.');
    }

    public function matchDetail($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'tournament', 'battingStats', 'bowlingStats'])->findOrFail($id);
        $balls = \App\Models\BallByBall::where('match_id', $id)->orderBy('created_at', 'desc')->get();
        $isLocal = true;
        return view('admin.match_detail', compact('match', 'balls', 'isLocal'));
    }

    public function tournamentPreview($id)
    {
        $tournament = Tournament::with(['teams', 'matches.team1', 'matches.team2'])->findOrFail($id);
        $teams = $tournament->teams;
        $matches = $tournament->matches;

        $pointsTable = [];
        foreach ($teams as $team) {
            $pointsTable[$team->id] = [
                'team' => $team,
                'p'    => 0,
                'w'    => 0,
                'l'    => 0,
                'pts'  => 0,
                'nrr'  => '0.00',
            ];
        }

        foreach ($matches as $match) {
            if ($match->status === 'completed') {
                if (isset($pointsTable[$match->team1_id])) {
                    $pointsTable[$match->team1_id]['p']++;
                    if ($match->team1_score > $match->team2_score) {
                        $pointsTable[$match->team1_id]['w']++;
                        $pointsTable[$match->team1_id]['pts'] += 2;
                    } else {
                        $pointsTable[$match->team1_id]['l']++;
                    }
                }
                if (isset($pointsTable[$match->team2_id])) {
                    $pointsTable[$match->team2_id]['p']++;
                    if ($match->team2_score > $match->team1_score) {
                        $pointsTable[$match->team2_id]['w']++;
                        $pointsTable[$match->team2_id]['pts'] += 2;
                    } else {
                        $pointsTable[$match->team2_id]['l']++;
                    }
                }
            }
        }

        usort($pointsTable, fn($a, $b) => $b['pts'] <=> $a['pts']);

        $isLocal = true;
        return view('admin.tournament_preview', compact('tournament', 'teams', 'matches', 'pointsTable', 'isLocal'));
    }

    public function deleteMatch($id)
    {
        $match = CricketMatch::findOrFail($id);
        $this->authorizeTournament($match->tournament_id);
        $match->delete();
        return back()->with('success', 'Match deleted successfully.');
    }

    public function addScorecardStat(Request $request)
    {
        $matchId = (int)$request->input('match_id', 0);
        $match = CricketMatch::findOrFail($matchId);
        $this->authorizeTournament($match->tournament_id);
        $type = $request->input('stat_type', 'BATTER');
        $playerName = trim($request->input('player_name', ''));

        if ($matchId > 0 && !empty($playerName)) {
            if ($type === 'BATTER') {
                $runs = (int)$request->input('runs', 0);
                $balls = (int)$request->input('balls', 1);
                $sr = $balls > 0 ? round(($runs / $balls) * 100, 2) : 0.00;

                \App\Models\PlayerBattingStat::create([
                    'match_id' => $matchId,
                    'player_name' => $playerName,
                    'runs' => $runs,
                    'balls' => $balls,
                    'fours' => (int)$request->input('fours', 0),
                    'sixes' => (int)$request->input('sixes', 0),
                    'strike_rate' => $sr,
                    'status_text' => $request->input('status_text', 'not out')
                ]);
            } else {
                $overs = (float)$request->input('overs', 1.0);
                $runs = (int)$request->input('runs', 0);
                $econ = $overs > 0 ? round($runs / $overs, 2) : 0.00;

                \App\Models\PlayerBowlingStat::create([
                    'match_id' => $matchId,
                    'player_name' => $playerName,
                    'overs' => $overs,
                    'runs' => $runs,
                    'wickets' => (int)$request->input('wickets', 0),
                    'economy' => $econ
                ]);
            }

            return back()->with('success', "Scorecard stat for '$playerName' saved!");
        }

        return back()->with('error', 'Match ID and Player Name required.');
    }

    public function requestDelete($id)
    {
        $tournament = $this->authorizeTournament($id);
        $tournament->delete_requested = true;
        $tournament->save();
        return back()->with('success', "Deletion request for '{$tournament->name}' sent to Super Admin for approval.");
    }
}