<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tournament;
use App\Models\CricketMatch;
use App\Models\FantasyTip;
use App\Models\Venue;
use App\Models\Team;
use App\Models\Player;

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

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please sign in or register to access your CrickArena Local Dashboard.');
        }

        $user = Auth::user();
        
        // Search & Filter parameters
        $filterCity = trim($request->input('city', ''));
        if ($filterCity === 'custom' || empty($filterCity)) {
            $filterCity = trim($request->input('custom_city', ''));
        }

        $filterState = trim($request->input('state', ''));
        if ($filterState === 'custom' || empty($filterState)) {
            $filterState = trim($request->input('custom_state', ''));
        }

        $search = trim($request->input('search', ''));
        if ($search === 'custom' || empty($search)) {
            $search = trim($request->input('custom_search', ''));
        }
        $activeTab = trim($request->input('tab', 'matches'));

        // Available tournaments for dropdown
        $availableTournaments = Tournament::where('category', 'local')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->pluck('name')
            ->sort()
            ->values();

        // Available teams for dropdown
        $availableTeams = Team::whereHas('tournament', function($q) {
                $q->where('category', 'local');
            })
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->pluck('name')
            ->sort()
            ->values();

        if ($availableTeams->isEmpty()) {
            $availableTeams = Team::whereNotNull('name')
                ->where('name', '!=', '')
                ->distinct()
                ->pluck('name')
                ->sort()
                ->values();
        }

        // Available matches for dropdown (Titles or Team 1 vs Team 2)
        $availableMatches = CricketMatch::where(function($q) {
                $q->where('level_type', 'LOCAL')
                  ->orWhereHas('tournament', fn($tq) => $tq->where('category', 'local'));
            })
            ->with(['team1', 'team2'])
            ->get()
            ->map(function($m) {
                if (!empty($m->title)) {
                    return trim($m->title);
                }
                if ($m->team1 && $m->team2) {
                    return trim($m->team1->name . ' vs ' . $m->team2->name);
                }
                return null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Distinct available cities across local tournaments + common cricket cities
        $dbCities = Tournament::where('category', 'local')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city');
        $popularCities = collect(['Indore', 'Bhopal', 'Bangalore', 'Mumbai', 'Delhi', 'Jaipur', 'Ahmedabad', 'Pune', 'Hyderabad', 'Chennai', 'Kolkata', 'Lucknow']);
        $availableCities = $dbCities->concat($popularCities)->unique(fn($c) => strtolower($c))->values();

        // Distinct available states across local tournaments + common states
        $dbStates = Tournament::where('category', 'local')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state');
        $popularStates = collect(['MP', 'Karnataka', 'Maharashtra', 'Gujarat', 'Delhi', 'Rajasthan', 'Uttar Pradesh', 'Tamil Nadu', 'Telangana', 'Punjab', 'Haryana', 'West Bengal']);
        $availableStates = $dbStates->concat($popularStates)->unique(fn($s) => strtolower($s))->values();

        // 1. Scheduled Local Matches Query
        $scheduledQuery = CricketMatch::whereIn('status', ['scheduled', 'upcoming'])
            ->where(function($q) {
                $q->where('level_type', 'LOCAL')
                  ->orWhereHas('tournament', function($tq) {
                      $tq->where('category', 'local');
                  });
            })
            ->with(['team1', 'team2', 'venue', 'tournament']);

        if (!empty($filterCity)) {
            $scheduledQuery->where(function($q) use ($filterCity) {
                $q->whereHas('tournament', fn($tq) => $tq->where('city', 'like', "%{$filterCity}%"))
                  ->orWhereHas('venue', fn($vq) => $vq->where('city', 'like', "%{$filterCity}%"))
                  ->orWhere('custom_note', 'like', "%{$filterCity}%");
            });
        }
        if (!empty($filterState)) {
            $scheduledQuery->whereHas('tournament', fn($tq) => $tq->where('state', 'like', "%{$filterState}%"));
        }
        if (!empty($search)) {
            $scheduledQuery->where(function($q) use ($search) {
                $q->whereHas('tournament', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('team1', fn($t1) => $t1->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('team2', fn($t2) => $t2->where('name', 'like', "%{$search}%"))
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('custom_note', 'like', "%{$search}%");

                if (str_contains($search, ' vs ')) {
                    $parts = explode(' vs ', $search);
                    $p1 = trim($parts[0] ?? '');
                    $p2 = trim($parts[1] ?? '');
                    if ($p1 && $p2) {
                        $q->orWhere(function($subQ) use ($p1, $p2) {
                            $subQ->where(function($matchQ) use ($p1, $p2) {
                                $matchQ->whereHas('team1', fn($t1) => $t1->where('name', 'like', "%{$p1}%"))
                                       ->whereHas('team2', fn($t2) => $t2->where('name', 'like', "%{$p2}%"));
                            })->orWhere(function($matchQ) use ($p1, $p2) {
                                $matchQ->whereHas('team1', fn($t1) => $t1->where('name', 'like', "%{$p2}%"))
                                       ->whereHas('team2', fn($t2) => $t2->where('name', 'like', "%{$p1}%"));
                            });
                        });
                    }
                }
            });
        }
        $scheduledMatches = $scheduledQuery->orderBy('match_date', 'asc')->get();

        // 2. My Tournaments (Created by the logged-in user - Full Management Permissions)
        $myTournamentsQuery = Tournament::where('category', 'local')
            ->where('user_id', $user->id)
            ->with(['user', 'teams', 'matches']);

        // 3. Community & Other Tournaments (Created by other users - View Only Permissions)
        $otherTournamentsQuery = Tournament::where('category', 'local')
            ->where(function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                      ->orWhereNull('user_id');
            })
            ->with(['user', 'teams', 'matches']);

        if (!empty($filterCity)) {
            $myTournamentsQuery->where('city', 'like', "%{$filterCity}%");
            $otherTournamentsQuery->where('city', 'like', "%{$filterCity}%");
        }
        if (!empty($filterState)) {
            $myTournamentsQuery->where('state', 'like', "%{$filterState}%");
            $otherTournamentsQuery->where('state', 'like', "%{$filterState}%");
        }
        if (!empty($search)) {
            $myTournamentsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('teams', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('matches', fn($mq) => $mq->where('title', 'like', "%{$search}%"));
            });
            $otherTournamentsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('teams', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('matches', fn($mq) => $mq->where('title', 'like', "%{$search}%"));
            });
        }

        $myTournaments = $myTournamentsQuery->orderBy('id', 'desc')->get();
        $otherTournaments = $otherTournamentsQuery->orderBy('id', 'desc')->get();

        // Counts for user's own local tournaments
        $upcomingCount = $myTournaments->whereIn('status', ['draft', 'published', 'scheduled'])->count();
        $ongoingCount = $myTournaments->whereIn('status', ['ongoing', 'live'])->count();
        $completedCount = $myTournaments->where('status', 'completed')->count();
        $totalTournaments = $myTournaments->count();
        $totalViews = $myTournaments->sum('views_count');

        // Existing venues pool for tournament creation datalist
        $dbVenues = Venue::orderBy('name', 'asc')->get();
        $tournamentVenues = Tournament::whereNotNull('venue')
            ->where('venue', '!=', '')
            ->distinct()
            ->pluck('venue')
            ->map(fn($v) => (object)['name' => $v, 'city' => null]);
        $existingVenues = $dbVenues->concat($tournamentVenues)->unique('name')->values();

        // Existing series templates (distinct past tournament names and formats)
        $existingSeriesTemplates = Tournament::select('name', 'format', 'city', 'state', 'overs')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name', 'asc')
            ->get()
            ->unique('name')
            ->values();

        return view('local.dashboard', compact(
            'myTournaments',
            'otherTournaments',
            'scheduledMatches',
            'totalTournaments',
            'totalViews',
            'user',
            'upcomingCount',
            'ongoingCount',
            'completedCount',
            'availableCities',
            'availableStates',
            'availableTournaments',
            'availableTeams',
            'availableMatches',
            'filterCity',
            'filterState',
            'search',
            'activeTab',
            'existingVenues',
            'existingSeriesTemplates'
        ));
    }

    public function manageTournament($id)
    {
        $tournament = $this->authorizeTournament($id);
        $teams = \App\Models\Team::where('tournament_id', $id)->with('players')->get();
        $teamIds = $teams->pluck('id');
        $players = \App\Models\Player::whereIn('team_id', $teamIds)->get();
        $matches = CricketMatch::where('tournament_id', $id)->with(['team1', 'team2', 'venue'])->orderBy('id', 'desc')->get();

        // 1. Detect all players currently playing in live matches across any tournament/series
        $liveMatches = CricketMatch::where('status', 'live')
            ->with(['team1.players', 'team2.players', 'tournament'])
            ->get();

        $livePlayerMap = [];
        foreach ($liveMatches as $lm) {
            $tName = $lm->tournament?->name ?? 'Live Tournament';
            $mTitle = ($lm->team1?->name && $lm->team2?->name) ? "{$lm->team1->name} vs {$lm->team2->name}" : "Live Match";
            
            if ($lm->team1) {
                foreach ($lm->team1->players as $lp) {
                    $key = strtolower(trim($lp->name));
                    if (!isset($livePlayerMap[$key])) {
                        $livePlayerMap[$key] = [
                            'match_id' => $lm->id,
                            'match_title' => $mTitle,
                            'tournament_name' => $tName,
                            'playing_team' => $lm->team1->name
                        ];
                    }
                }
            }
            if ($lm->team2) {
                foreach ($lm->team2->players as $lp) {
                    $key = strtolower(trim($lp->name));
                    if (!isset($livePlayerMap[$key])) {
                        $livePlayerMap[$key] = [
                            'match_id' => $lm->id,
                            'match_title' => $mTitle,
                            'tournament_name' => $tName,
                            'playing_team' => $lm->team2->name
                        ];
                    }
                }
            }
        }

        // 2. Fetch existing pool of distinct local players
        $existingPlayers = \App\Models\Player::with('team.tournament')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name', 'asc')
            ->get()
            ->unique(fn($p) => strtolower(trim($p->name)))
            ->values();

        // 3. Fetch existing pool of distinct teams across the database
        $existingTeams = \App\Models\Team::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name', 'asc')
            ->get()
            ->unique(fn($t) => strtolower(trim($t->name)))
            ->values();

        // 4. Fetch venues pool for match scheduling
        $existingVenues = Venue::orderBy('name', 'asc')->get();

        return view('local.manage-tournament', compact('tournament', 'teams', 'players', 'matches', 'existingPlayers', 'livePlayerMap', 'existingTeams', 'existingVenues'));
    }

    public function createTournament(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $name = trim($request->input('name', ''));
        $city = trim($request->input('city', ''));
        if ($city === 'custom' || empty($city)) {
            $city = trim($request->input('custom_city', ''));
        }
        $state = trim($request->input('state', ''));
        if ($state === 'custom' || empty($state)) {
            $state = trim($request->input('custom_state', ''));
        }
        $venue = trim($request->input('venue', ''));
        if ($venue === 'custom' || empty($venue)) {
            $venue = trim($request->input('custom_venue', ''));
        }
        $format = trim($request->input('format', 'T20'));
        $overs = (int)$request->input('overs', 20);
        $type = trim($request->input('type', 'Knockout'));
        $bannerUrl = trim($request->input('banner_url', ''));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $description = trim($request->input('description', ''));
        $userId = Auth::id();

        if (!empty($name)) {
            $initialStatus = 'ongoing';
            if (!empty($startDate) && \Carbon\Carbon::parse($startDate)->isFuture()) {
                $initialStatus = 'upcoming';
            }

            $shortName = trim($request->input('short_name', ''));
            if (empty($shortName)) {
                $words = array_filter(preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', $name)));
                if (count($words) >= 2) {
                    $acronym = '';
                    foreach ($words as $w) {
                        $acronym .= $w[0];
                    }
                    $shortName = strtoupper(substr($acronym, 0, 6));
                }
                if (empty($shortName) || strlen($shortName) < 2) {
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $name);
                    $shortName = strtoupper(substr($clean ?: $name, 0, 6));
                }
            }
            if (empty($shortName)) {
                $shortName = 'LOCAL';
            }

            $createdTournament = Tournament::create([
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
                'status' => $initialStatus,
                'is_approved' => true,
                'delete_requested' => false,
                'views_count' => 0,
                'reward_tier' => 'ROOKIE'
            ]);

            return redirect()->route('local.manage-tournament', $createdTournament->id)->with('success', 'Tournament created successfully! You can now add teams and schedule matches.');
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

        $venueId = null;
        $venueName = trim($request->input('venue', ''));
        $existingVenueId = $request->input('existing_venue_id');

        if ($existingVenueId === 'tournament_default') {
            $venueName = $tournament->venue ?: ($tournament->city ? $tournament->city . ' Cricket Ground' : 'Cricket Ground');
            $matchedVenue = Venue::where('name', 'like', $venueName)->first();
            if ($matchedVenue) {
                $venueId = $matchedVenue->id;
            }
        } elseif (!empty($existingVenueId) && is_numeric($existingVenueId)) {
            $v = Venue::find($existingVenueId);
            if ($v) {
                $venueId = $v->id;
                $venueName = $v->name . ($v->city ? ', ' . $v->city : '');
            }
        } elseif (!empty($venueName)) {
            $matchedVenue = Venue::where('name', 'like', $venueName)->first();
            if ($matchedVenue) {
                $venueId = $matchedVenue->id;
            }
        }

        $customNote = $status === 'live' ? ($venueName ? $venueName . ' • In Progress' : 'Match in progress') : ($venueName ?: 'Match Scheduled');
        
        $tournament->matches()->create([
            'team1_id' => $team1Id,
            'team2_id' => $team2Id,
            'venue_id' => $venueId,
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

        $teamName = '';
        $shortName = '';
        $colorCode = '#2563eb';
        $logo = null;
        $logoUrl = null;

        if ($request->filled('existing_team_id')) {
            $existingTeam = Team::find($request->input('existing_team_id'));
            if ($existingTeam) {
                $teamName = trim($existingTeam->name);
                $shortName = $existingTeam->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $teamName), 0, 3));
                $colorCode = $existingTeam->color_code ?: '#2563eb';
                $logo = $existingTeam->logo;
                $logoUrl = $existingTeam->logo_url;
            }
        }

        if (empty($teamName)) {
            $teamName = trim($request->input('name', ''));
            $shortName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $teamName), 0, 3));
        }

        if (empty($teamName)) {
            return back()->with('error', 'Please select an existing team or enter a team name.');
        }

        // Duplicate Check: Check if team with same name is already in this tournament
        $alreadyInTournament = Team::where('tournament_id', $id)
            ->where('name', $teamName)
            ->exists();

        if ($alreadyInTournament) {
            return back()->with('error', "Team '{$teamName}' is already participating in this tournament!");
        }

        Team::create([
            'tournament_id' => $id,
            'name' => $teamName,
            'short_name' => $shortName,
            'color_code' => $colorCode,
            'logo' => $logo,
            'logo_url' => $logoUrl
        ]);

        return back()->with('success', "Team '{$teamName}' added to tournament!");
    }

    public function addPlayer(Request $request, $id)
    {
        $tournament = $this->authorizeTournament($id);
        $team = \App\Models\Team::where('tournament_id', $id)->findOrFail($request->input('team_id'));

        $playerName = '';
        $role = 'Batsman';
        $battingStyle = null;
        $bowlingStyle = null;
        $country = null;
        $profileImage = null;

        if ($request->filled('existing_player_id')) {
            $existing = \App\Models\Player::find($request->input('existing_player_id'));
            if ($existing) {
                $playerName = trim($existing->name);
                $role = $existing->role ?: 'Batsman';
                $battingStyle = $existing->batting_style;
                $bowlingStyle = $existing->bowling_style;
                $country = $existing->country;
                $profileImage = $existing->profile_image;
            }
        }

        if (empty($playerName)) {
            $playerName = trim($request->input('name', ''));
            $role = $request->input('role', 'Batsman');
        }

        if (empty($playerName)) {
            return back()->with('error', 'Please select an existing player or enter a player name.');
        }

        // Duplicate Check: Check if this player is already registered in this specific team's squad
        $alreadyInTeam = \App\Models\Player::where('team_id', $team->id)
            ->where('name', $playerName)
            ->exists();

        if ($alreadyInTeam) {
            return back()->with('error', "Player '{$playerName}' is already registered in {$team->name}'s squad!");
        }

        // Reuse existing styles or photo if available
        if (!$profileImage) {
            $existingByName = \App\Models\Player::where('name', $playerName)->first();
            if ($existingByName) {
                $battingStyle = $battingStyle ?: $existingByName->batting_style;
                $bowlingStyle = $bowlingStyle ?: $existingByName->bowling_style;
                $country = $country ?: $existingByName->country;
                $profileImage = $existingByName->profile_image;
            }
        }

        // Create player in team roster
        \App\Models\Player::create([
            'team_id' => $team->id,
            'name' => $playerName,
            'short_name' => substr($playerName, 0, 16),
            'role' => $role,
            'batting_style' => $battingStyle,
            'bowling_style' => $bowlingStyle,
            'country' => $country,
            'profile_image' => $profileImage
        ]);

        return back()->with('success', "Player '{$playerName}' successfully added to {$team->name}!");
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