<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CricketMatch;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\Player;
use App\Models\Article;
use App\Models\News;
use App\Models\User;
use App\Models\Prediction;
use App\Models\FantasyTip;
use App\Models\TeamRanking;
use App\Models\Venue;
use App\Models\WebStory;
use App\Models\GlossaryTerm;
use App\Models\BallByBall;
use App\Models\PlayerBattingStat;
use App\Models\PlayerBowlingStat;

class AdminController extends Controller
{
    protected function saveUploadedFile(\Illuminate\Http\UploadedFile $file, string $folder = 'uploads/images'): string
    {
        $mime = $file->getMimeType() ?: 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
    }

    protected function saveBase64Image(string $base64String, string $folder = 'uploads/images'): ?string
    {
        return trim($base64String);
    }

    protected function handleUploadedImage(Request $request, string $fileKey, string $urlKey, ?string $fallback = null, string $folder = 'uploads/images'): ?string
    {
        // 1. Check all candidate file keys for actual file uploads
        $fileKeys = array_unique([$fileKey, 'poster_file', 'poster_image_file', 'image_file', 'logo_file', 'image', 'file', 'poster_image', 'profile_image_file', 'photo_file']);
        foreach ($fileKeys as $k) {
            if ($request->hasFile($k) && $request->file($k)->isValid()) {
                $file = $request->file($k);
                $mime = $file->getMimeType() ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        // 2. Check candidate URL/text keys
        $urlKeys = array_unique([$urlKey, $fileKey, 'poster_image', 'image_url', 'banner_url', 'logo_url', 'profile_image', 'photo_url', 'logo']);
        foreach ($urlKeys as $k) {
            if ($request->filled($k)) {
                $val = trim($request->input($k));
                if (!empty($val)) {
                    return $val;
                }
            }
        }

        return $fallback;
    }

    protected function handleUploadedImagesMultiple(Request $request, string $fileKey, string $folder = 'uploads/web_stories'): array
    {
        $urls = [];

        // Check if multiple files were uploaded
        $fileKeys = array_unique([$fileKey, 'images', 'slides']);
        foreach ($fileKeys as $k) {
            if ($request->hasFile($k)) {
                $files = $request->file($k);
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $mime = $file->getMimeType() ?: 'image/jpeg';
                        $urls[] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                    }
                }
                if (!empty($urls)) {
                    return $urls;
                }
            }
        }

        // Check for base64 encoded slides from client-side compressor
        if ($request->has('slides_base64') && is_array($request->input('slides_base64'))) {
            foreach ($request->input('slides_base64') as $b64) {
                $b64 = trim($b64);
                if (!empty($b64)) {
                    $urls[] = $b64;
                }
            }
            if (!empty($urls)) {
                return $urls;
            }
        }

        // Check for array of slides in input
        if ($request->has('slides') && is_array($request->input('slides'))) {
            foreach ($request->input('slides') as $s) {
                $s = trim($s);
                if (!empty($s)) {
                    if (str_starts_with($s, 'data:image/') || str_contains($s, ';base64,')) {
                        $saved = $this->saveBase64Image($s, $folder);
                        if ($saved) {
                            $urls[] = $saved;
                        }
                    } else {
                        $urls[] = $s;
                    }
                }
            }
        }

        return $urls;
    }

    public function index()
    {
        $matchCount = CricketMatch::count();
        $liveCount = CricketMatch::where('status', 'live')->count();
        $tournCount = Tournament::count();
        $teamCount = Team::count();
        $playerCount = Player::count();
        $articleCount = Article::count();
        $userCount = User::count();
        $adminMatches = CricketMatch::has('team1')->has('team2')->with(['team1', 'team2'])->orderBy('id', 'desc')->take(10)->get();

        $adminTournaments = Tournament::with('user')->orderBy('id', 'desc')->get();
        $totalTournaments = $tournCount;
        $totalViews = Tournament::sum('views_count');

        $pendingApprovals = Tournament::where('category', 'local')->where('is_approved', false)->with('user')->orderBy('id', 'desc')->get();
        $pendingDeletions = Tournament::where('category', 'local')->where('delete_requested', true)->with('user')->orderBy('id', 'desc')->get();

        return view('admin.dashboard', compact(
            'matchCount', 'liveCount', 'tournCount', 'teamCount', 'playerCount', 'articleCount', 'userCount', 
            'adminMatches', 'adminTournaments', 'totalTournaments', 'totalViews', 'pendingApprovals', 'pendingDeletions'
        ));
    }

    public function createTournament(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (!empty($name)) {
            $shortName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
            Tournament::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'name' => $name,
                'short_name' => $shortName,
                'format' => $request->input('format', 'T20'),
                'series_type' => 'GLOBAL',
                'category' => 'global',
                'city' => $request->input('city', ''),
                'state' => $request->input('state', ''),
                'venue' => $request->input('venue', ''),
                'overs' => (int)$request->input('overs', 20),
                'type' => $request->input('type', 'Knockout'),
                'banner_url' => $request->input('banner_url', ''),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'description' => $request->input('description', ''),
                'status' => 'draft',
            ]);
            return redirect()->route('admin.dashboard')->with('success', "Global Tournament/Series '$name' created successfully!");
        }
        return back()->with('error', 'Tournament name is required.');
    }

    public function manageTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $teams = Team::where('tournament_id', $id)->get();
        $teamIds = $teams->pluck('id');
        $players = Player::whereIn('team_id', $teamIds)->get();
        $matches = CricketMatch::where('tournament_id', $id)->with(['team1', 'team2'])->orderBy('id', 'desc')->get();

        return view('admin.manage-tournament', compact('tournament', 'teams', 'players', 'matches'));
    }

    public function addTeam(Request $request, $id)
    {
        $name = trim($request->input('name'));
        if ($name) {
            Team::create([
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
        Player::create([
            'team_id' => $request->input('team_id'),
            'name' => $request->input('name'),
            'role' => $request->input('role', 'batsman')
        ]);
        return back()->with('success', 'Player added!');
    }

    public function addMatch(Request $request, $id)
    {
        $team1_id = $request->input('team1_id');
        $team2_id = $request->input('team2_id');

        if ($team1_id && $team2_id && $team1_id != $team2_id) {
            CricketMatch::create([
                'tournament_id' => $id,
                'team1_id'      => $team1_id,
                'team2_id'      => $team2_id,
                'status'        => 'scheduled',
                'match_type'    => Tournament::find($id)->format ?? 'T20',
                'custom_note'   => $request->input('venue', ''),
                'level_type'    => 'GLOBAL',
                'match_date'    => $request->input('scheduled_at'),
            ]);
            return back()->with('success', 'Match scheduled!');
        }
        return back()->with('error', 'Invalid teams selected.');
    }

    public function toss($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'tournament'])->findOrFail($id);
        return view('admin.toss', compact('match'));
    }

    public function saveToss(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        $tossWinnerId  = $request->input('toss_winner_id');
        $decision      = $request->input('decision');

        if ($decision === 'bat') {
            $battingTeamId = $tossWinnerId;
        } else {
            $battingTeamId = ($tossWinnerId == $match->team1_id) ? $match->team2_id : $match->team1_id;
        }

        $match->result_text = 'toss:' . $tossWinnerId . ':' . $decision;
        $match->current_innings = 1;
        $match->save();

        return redirect()->route('admin.opening-players', ['id' => $id, 'batting_team_id' => $battingTeamId]);
    }

    public function openingPlayers($id)
    {
        $match = CricketMatch::with(['team1', 'team2', 'tournament'])->findOrFail($id);
        $battingTeamId = request('batting_team_id');
        $battingTeam   = Team::with('players')->findOrFail($battingTeamId);
        $bowlingTeamId = ($battingTeamId == $match->team1_id) ? $match->team2_id : $match->team1_id;
        $bowlingTeam   = Team::with('players')->findOrFail($bowlingTeamId);
        $isLocal = false;
        return view('admin.opening_players', compact('match', 'battingTeam', 'bowlingTeam', 'isLocal'));
    }

    public function scorer($id)
    {
        $match = CricketMatch::with(['team1.players', 'team2.players', 'tournament', 'battingStats', 'bowlingStats'])->findOrFail($id);
        $isLocal = false;
        
        $allBalls = \App\Models\BallByBall::where('match_id', $id)
            ->orderBy('id', 'asc')
            ->get();
            
        $lastBalls = $allBalls->take(-24);
        
        return view('admin.scorer', compact('match', 'isLocal', 'lastBalls', 'allBalls'));
    }

    public function startInnings(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);
        \App\Services\CricketScorerService::startInnings(
            $id,
            $request->input('striker_id'),
            $request->input('non_striker_id'),
            $request->input('bowler_id'),
            1
        );
        return redirect()->route('admin.scorer', $id)->with('success', 'Innings 1 started! Striker and Bowler active.');
    }

    public function updateScore(Request $request)
    {
        $match = CricketMatch::findOrFail($request->match_id);
        
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
        $match = CricketMatch::findOrFail($request->match_id);
        
        $success = \App\Services\CricketScorerService::undoBall($request->match_id);
        if ($success) {
            return back()->with('success', 'Last ball undone successfully!');
        }
        return back()->with('error', 'No balls to undo!');
    }

    public function changeActivePlayers(Request $request, $id)
    {
        $match = CricketMatch::findOrFail($id);

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
            \App\Models\PlayerBattingStat::where('match_id', $id)->where('status_text', 'non-striker')->update(['status_text' => 'non-striker']);
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
        $innings = (int)$request->input('innings', 2);
        
        \App\Services\CricketScorerService::startInnings(
            $id,
            $request->input('striker_id'),
            $request->input('non_striker_id'),
            $request->input('bowler_id'),
            $innings
        );

        return redirect()->route('admin.scorer', $id)->with('success', "Innings $innings started!");
    }

    public function createMatch(Request $request)
    {
        $team1Name = trim($request->input('team1_name', ''));
        $team2Name = trim($request->input('team2_name', ''));
        $tournId = $request->input('tournament_id');
        $seriesName = trim($request->input('name', $request->input('series_name', '')));

        $tournament = null;
        if (!empty($tournId) && is_numeric($tournId)) {
            $tournament = Tournament::find($tournId);
        }

        if (!$tournament && !empty($seriesName)) {
            $short = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $seriesName), 0, 6));
            $tournament = Tournament::firstOrCreate(
                ['name' => $seriesName],
                [
                    'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    'short_name' => $short,
                    'format' => $request->input('format', $request->input('match_type', 'T20')),
                    'series_type' => $request->input('series_type', 'GLOBAL'),
                    'category' => 'International',
                    'city' => $request->input('city', 'Mumbai'),
                    'state' => $request->input('state', ''),
                    'venue' => $request->input('venue', ''),
                    'banner_url' => $request->input('banner_url', ''),
                    'overs' => (int)$request->input('overs', 20),
                    'type' => $request->input('type', 'Knockout'),
                    'start_date' => $request->input('start_date', date('Y-m-d')),
                    'end_date' => $request->input('end_date', date('Y-m-d')),
                    'description' => '',
                    'year' => date('Y'),
                    'status' => $request->input('status', 'upcoming') === 'live' ? 'ongoing' : ($request->input('status', 'upcoming') === 'completed' ? 'completed' : 'upcoming'),
                    'is_approved' => true,
                    'is_enabled' => true,
                    'display_order' => 1,
                    'views_count' => 0
                ]
            );
        }

        if (empty($team1Name) && empty($team2Name) && $tournament) {
            return redirect()->route('admin.teams', ['tournament_id' => $tournament->id])
                ->with('success', "Match Series '{$tournament->name}' created successfully! Now please create participating teams.");
        }

        if (empty($team1Name) || empty($team2Name)) {
            return redirect()->route('admin.match')->with('error', 'Both Team 1 and Team 2 names are required to schedule a match.');
        }

        $tournId = $tournament?->id;

        $team1 = Team::where('name', $team1Name)
            ->where(function($q) use ($tournId) {
                if ($tournId) $q->where('tournament_id', $tournId)->orWhereNull('tournament_id');
            })
            ->first();

        if (!$team1) {
            $team1 = Team::create([
                'name' => $team1Name,
                'tournament_id' => $tournId,
                'short_name' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $team1Name), 0, 3)) ?: 'T1',
                'team_type' => 'international',
                'color_code' => '#2563eb'
            ]);
        } elseif ($tournId && empty($team1->tournament_id)) {
            $team1->tournament_id = $tournId;
            $team1->save();
        }

        $team2 = Team::where('name', $team2Name)
            ->where(function($q) use ($tournId) {
                if ($tournId) $q->where('tournament_id', $tournId)->orWhereNull('tournament_id');
            })
            ->first();

        if (!$team2) {
            $team2 = Team::create([
                'name' => $team2Name,
                'tournament_id' => $tournId,
                'short_name' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $team2Name), 0, 3)) ?: 'T2',
                'team_type' => 'international',
                'color_code' => '#38bdf8'
            ]);
        } elseif ($tournId && empty($team2->tournament_id)) {
            $team2->tournament_id = $tournId;
            $team2->save();
        }

        $status = $request->input('status', 'upcoming');
        $format = $request->input('match_type', $request->input('format', 'T20'));
        $level = $request->input('level_type', $tournament ? $tournament->name . ' - Match' : 'GLOBAL MATCH');
        $matchDate = $request->input('match_date', now());

        $match = CricketMatch::create([
            'tournament_id' => $tournament?->id,
            'team1_id' => $team1->id,
            'team2_id' => $team2->id,
            'match_type' => $format,
            'level_type' => $level,
            'status' => $status,
            'match_date' => $matchDate,
            'team1_score' => (int)$request->input('team1_score', 0),
            'team1_wickets' => (int)$request->input('team1_wickets', 0),
            'team1_overs' => (float)$request->input('team1_overs', 0.0),
            'team2_score' => (int)$request->input('team2_score', 0),
            'team2_wickets' => (int)$request->input('team2_wickets', 0),
            'team2_overs' => (float)$request->input('team2_overs', 0.0),
            'custom_note' => $status === 'live' ? 'Match in progress' : 'Match Scheduled'
        ]);

        $tournName = $tournament ? " under series '{$tournament->name}'" : "";
        return redirect()->route('admin.match')->with('success', "Match '{$team1->name} vs {$team2->name}' created successfully{$tournName}!");
    }

    public function updateMatch(Request $request)
    {
        $matchId = (int)$request->input('match_id', 0);
        $match = CricketMatch::find($matchId);

        if ($match) {
            $match->status = $request->input('status', 'live');
            $match->team1_score = (int)$request->input('team1_score', 0);
            $match->team1_wickets = (int)$request->input('team1_wickets', 0);
            $match->team2_score = (int)$request->input('team2_score', 0);
            $match->team2_wickets = (int)$request->input('team2_wickets', 0);
            $match->custom_note = $request->input('custom_note', 'Match in progress');
            $match->save();

            return redirect()->route('admin.dashboard')->with('success', "Match #$matchId updated successfully!");
        }

        return redirect()->route('admin.dashboard')->with('error', 'Match not found.');
    }

    public function addBallCommentary(Request $request)
    {
        $matchId = (int)$request->input('match_id', 0);
        $overNum = trim($request->input('over_num', '0.1'));
        $outcome = trim($request->input('outcome', 'Dot ball'));

        if ($matchId > 0 && !empty($outcome)) {
            BallByBall::create([
                'match_id' => $matchId,
                'over_num' => $overNum,
                'outcome' => $outcome,
                'bowler_name' => $request->input('bowler_name', 'Bowler'),
                'batsman_name' => $request->input('batsman_name', 'Batsman')
            ]);

            return redirect()->route('admin.dashboard')->with('success', "Ball $overNum commentary published live!");
        }

        return redirect()->route('admin.dashboard')->with('error', 'Match ID and outcome required.');
    }

    public function addScorecardStat(Request $request)
    {
        $matchId = (int)$request->input('match_id', 0);
        $type = $request->input('stat_type', 'BATTER');
        $playerName = trim($request->input('player_name', ''));

        if ($matchId > 0 && !empty($playerName)) {
            if ($type === 'BATTER') {
                $runs = (int)$request->input('runs', 0);
                $balls = (int)$request->input('balls', 1);
                $sr = $balls > 0 ? round(($runs / $balls) * 100, 2) : 0.00;

                PlayerBattingStat::create([
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

                PlayerBowlingStat::create([
                    'match_id' => $matchId,
                    'player_name' => $playerName,
                    'overs' => $overs,
                    'runs' => $runs,
                    'wickets' => (int)$request->input('wickets', 0),
                    'economy' => $econ
                ]);
            }

            return redirect()->route('admin.dashboard')->with('success', "Scorecard stat for '$playerName' saved!");
        }

        return redirect()->route('admin.dashboard')->with('error', 'Match ID and Player Name required.');
    }


    public function addArticle(Request $request)
    {
        $title = trim($request->input('title', ''));
        if (!empty($title)) {
            $imageUrl = $this->handleUploadedImage($request, 'poster_file', 'image_url', '');

            if (empty($imageUrl)) {
                $images = [
                    'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1512719994953-eabf50895df7?w=800&auto=format&fit=crop&q=80'
                ];
                $imageUrl = $images[array_rand($images)];
            }

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($title);
            }

            $content = $request->input('content', '');
            $summary = trim($request->input('meta_description', ''));
            if (empty($summary) && !empty($content)) {
                $summary = substr(strip_tags($content), 0, 160);
            }

            Article::create([
                'category' => $request->input('category', 'INTERNATIONAL'),
                'title' => $title,
                'slug' => $slug,
                'meta_description' => $request->input('meta_description', ''),
                'keywords' => $request->input('keywords', ''),
                'h1_heading' => $request->input('h1_heading', $title),
                'content' => $content,
                'summary' => $summary,
                'image_url' => $imageUrl,
                'read_time' => $request->input('read_time', '4 MIN READ'),
                'display_order' => (int)$request->input('display_order', 1),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'published_date' => date('M d')
            ]);

            return redirect()->route('admin.article')->with('success', 'Article published successfully!');
        }

        return redirect()->route('admin.article')->with('error', 'Article title is required.');
    }

    public function addNews(Request $request)
    {
        $title = trim($request->input('title', ''));
        if (!empty($title)) {
            $imageUrl = $this->handleUploadedImage($request, 'poster_file', 'image_url', '');

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($title);
            }

            $content = $request->input('content', '');
            $summary = trim($request->input('meta_description', ''));
            if (empty($summary) && !empty($content)) {
                $summary = substr(strip_tags($content), 0, 160);
            } elseif (empty($summary)) {
                $summary = $request->input('summary', '');
            }

            News::create([
                'category' => $request->input('category', 'CRICKET'),
                'title' => $title,
                'slug' => $slug,
                'meta_description' => $request->input('meta_description', ''),
                'keywords' => $request->input('keywords', ''),
                'h1_heading' => $request->input('h1_heading', $title),
                'content' => $content,
                'summary' => $summary,
                'image_url' => $imageUrl,
                'read_time' => $request->input('read_time', '3 MIN READ'),
                'display_order' => (int)$request->input('display_order', 1),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'published_date' => date('M d')
            ]);

            return redirect()->route('admin.news')->with('success', 'News published successfully!');
        }

        return redirect()->route('admin.news')->with('error', 'News title is required.');
    }

    public function addPrediction(Request $request, $id = null)
    {
        $title = trim($request->input('title', ''));
        $summary = trim($request->input('summary', ''));
        $type = strtoupper($request->input('type', 'PREDICTION'));

        $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        if (!empty($title)) {
            if ($type === 'FANTASY') {
                FantasyTip::create([
                    'tag' => 'FANTASY',
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'full_content' => $summary,
                    'meta_description' => $request->input('meta_description', $summary),
                    'keywords' => $request->input('keywords', ''),
                    'poster_image' => $posterUrl,
                    'display_order' => (int)$request->input('display_order', 1),
                    'is_enabled' => $request->has('is_enabled') ? true : false,
                ]);
                return redirect()->route('admin.prediction')->with('success', "Fantasy Tip added successfully!");
            } else {
                Prediction::create([
                    'tag' => 'MATCH PREDICTION',
                    'match_title' => 'Match Prediction',
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'full_content' => $summary,
                    'meta_description' => $request->input('meta_description', $summary),
                    'keywords' => $request->input('keywords', ''),
                    'poster_image' => $posterUrl,
                    'display_order' => (int)$request->input('display_order', 1),
                    'is_enabled' => $request->has('is_enabled') ? true : false,
                ]);
                return redirect()->route('admin.prediction')->with('success', "Match Prediction added successfully!");
            }
        }

        return redirect()->route('admin.prediction')->with('error', 'Title is required.');
    }

    public function addFantasyTip(Request $request, $id = null)
    {
        return $this->addPrediction($request, $id);
    }

    public function addSeries(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (!empty($name)) {
            $short = trim($request->input('short_name', ''));
            if (empty($short)) {
                $short = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
            }

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($name);
            }

            $formats = $request->input('match_formats', []);
            $formatsStr = is_array($formats) ? implode(',', $formats) : (string)$formats;
            $primaryFormat = !empty($formats) ? (is_array($formats) ? $formats[0] : $formats) : 'T20';

            $posterUrl = $this->handleUploadedImage($request, 'poster_image_file', 'poster_image', $request->input('banner_url', ''), 'uploads/series');

            Tournament::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'name' => $name,
                'slug' => $slug,
                'short_name' => $short,
                'year' => $request->input('year', date('Y')),
                'display_order' => (int)$request->input('display_order', 1),
                'meta_description' => $request->input('meta_description', ''),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'category' => $request->input('category', 'International'),
                'series_type' => 'GLOBAL',
                'match_formats' => $formatsStr,
                'format' => $primaryFormat,
                'teams_list' => $request->input('teams_list', ''),
                'venues_list' => $request->input('venues_list', ''),
                'hosting_country' => $request->input('hosting_country', ''),
                'total_matches' => (int)$request->input('total_matches', 10),
                'menu_order' => (int)$request->input('menu_order', 0),
                'full_description' => $request->input('full_description', ''),
                'description' => $request->input('meta_description', $request->input('full_description', '')),
                'dream11_id' => $request->input('dream11_id', ''),
                'cricbuzz_id' => $request->input('cricbuzz_id', ''),
                'espn_id' => $request->input('espn_id', ''),
                'icc_id' => $request->input('icc_id', ''),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'banner_url' => $posterUrl,
                'poster_image' => $posterUrl,
                'status' => $request->has('is_enabled') ? 'ongoing' : 'draft',
                'views_count' => 0
            ]);

            return redirect()->route('admin.series')->with('success', "Series '$name' created successfully!");
        }

        return redirect()->route('admin.series')->with('error', 'Series name is required.');
    }

    public function updateSeries(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);
        $name = trim($request->input('name', ''));
        if (!empty($name)) {
            $short = trim($request->input('short_name', ''));
            if (empty($short)) {
                $short = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
            }

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($name);
            }

            $formats = $request->input('match_formats', []);
            $formatsStr = is_array($formats) ? implode(',', $formats) : (string)$formats;
            $primaryFormat = !empty($formats) ? (is_array($formats) ? $formats[0] : $formats) : ($tournament->format ?? 'T20');

            $posterUrl = $this->handleUploadedImage($request, 'poster_image_file', 'poster_image', $tournament->poster_image ?? $tournament->banner_url, 'uploads/series');

            $tournament->update([
                'name' => $name,
                'slug' => $slug,
                'short_name' => $short,
                'year' => $request->input('year', $tournament->year ?? date('Y')),
                'display_order' => (int)$request->input('display_order', 1),
                'meta_description' => $request->input('meta_description', ''),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'category' => $request->input('category', $tournament->category ?? 'International'),
                'match_formats' => $formatsStr,
                'format' => $primaryFormat,
                'teams_list' => $request->input('teams_list', ''),
                'venues_list' => $request->input('venues_list', ''),
                'hosting_country' => $request->input('hosting_country', ''),
                'total_matches' => (int)$request->input('total_matches', 10),
                'menu_order' => (int)$request->input('menu_order', 0),
                'full_description' => $request->input('full_description', ''),
                'description' => $request->input('meta_description', $request->input('full_description', '')),
                'dream11_id' => $request->input('dream11_id', ''),
                'cricbuzz_id' => $request->input('cricbuzz_id', ''),
                'espn_id' => $request->input('espn_id', ''),
                'icc_id' => $request->input('icc_id', ''),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'banner_url' => $posterUrl,
                'poster_image' => $posterUrl,
                'status' => $request->has('is_enabled') ? ($tournament->status === 'draft' ? 'ongoing' : $tournament->status) : 'draft',
            ]);
            return redirect()->route('admin.series')->with('success', "Series '$name' updated successfully!");
        }
        return redirect()->route('admin.series')->with('error', 'Series name is required.');
    }

    public function addTeamRanking(Request $request)
    {
        $teamName = trim($request->input('team_name', ''));
        if (!empty($teamName)) {
            $logoUrl = $this->handleUploadedImage($request, 'poster_file', 'logo_url', $request->input('poster_image', ''));

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($teamName);
            }

            TeamRanking::create([
                'rank_num' => (int)$request->input('rank_num', 1),
                'team_name' => $teamName,
                'slug' => $slug,
                'logo_url' => $logoUrl,
                'matches_played' => (int)$request->input('matches_played', 0),
                'won' => (int)$request->input('won', 0),
                'nrr' => $request->input('nrr', '0.00'),
                'points' => (int)$request->input('points', 0),
                'category' => $request->input('category', 'ALL'),
                'display_order' => (int)$request->input('display_order', 1),
                'keywords' => $request->input('keywords', ''),
            ]);

            return redirect()->route('admin.ranking')->with('success', 'Team Standing created successfully!');
        }

        return redirect()->route('admin.ranking')->with('error', 'Team name is required.');
    }

    public function updateTeamRanking(Request $request, $id)
    {
        $team = TeamRanking::findOrFail($id);
        $teamName = trim($request->input('team_name', $team->team_name));

        $logoUrl = $this->handleUploadedImage($request, 'poster_file', 'logo_url', $team->logo_url ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($teamName);
        }

        $team->update([
            'rank_num' => (int)$request->input('rank_num', $team->rank_num),
            'team_name' => $teamName,
            'slug' => $slug,
            'logo_url' => $logoUrl,
            'matches_played' => (int)$request->input('matches_played', $team->matches_played),
            'won' => (int)$request->input('won', $team->won),
            'nrr' => $request->input('nrr', $team->nrr),
            'points' => (int)$request->input('points', $team->points),
            'category' => $request->input('category', $team->category ?? 'ALL'),
            'display_order' => (int)$request->input('display_order', $team->display_order ?? 1),
            'keywords' => $request->input('keywords', $team->keywords ?? ''),
        ]);

        return redirect()->route('admin.ranking')->with('success', 'Team Standing updated successfully!');
    }

    public function addPopularTeam(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (!empty($name)) {
            $short = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4));
            Team::create([
                'name' => $name,
                'short_name' => $short,
                'city' => $request->input('city', 'India'),
                'country' => 'India',
                'color_code' => $request->input('color_code', '#2563eb'),
                'team_type' => 'international'
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Popular team created successfully!');
        }

        return redirect()->route('admin.dashboard')->with('error', 'Team name is required.');
    }

    public function publishTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = 'published';
        $tournament->save();
        return back()->with('success', 'Tournament published successfully!');
    }

    public function markOngoing($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = 'ongoing';
        $tournament->save();
        return back()->with('success', 'Tournament marked as ongoing!');
    }

    public function markCompleted($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->status = 'completed';
        $tournament->save();
        return back()->with('success', 'Tournament marked as completed!');
    }


    public function deleteTeam($id)
    {
        $team = \App\Models\Team::findOrFail($id);

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
        $player->delete();
        return back()->with('success', 'Player deleted successfully.');
    }

    public function matchDetail($id)
    {
        $match = \App\Models\CricketMatch::with(['team1', 'team2', 'tournament', 'battingStats', 'bowlingStats'])->findOrFail($id);
        $balls = \App\Models\BallByBall::where('match_id', $id)->orderBy('created_at', 'desc')->get();
        return view('admin.match_detail', compact('match', 'balls'));
    }

    public function tournamentPreview($id)
    {
        $tournament = \App\Models\Tournament::with(['teams', 'matches.team1', 'matches.team2'])->findOrFail($id);
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

        return view('admin.tournament_preview', compact('tournament', 'teams', 'matches', 'pointsTable'));
    }

    public function deleteMatch($id)
    {
        $match = CricketMatch::findOrFail($id);
        $match->delete();
        return back()->with('success', 'Match deleted successfully.');
    }

    public function addWebStory(Request $request)
    {
        $title = trim($request->input('title', ''));
        $author = trim($request->input('author', ''));
        if (empty($author) && \Illuminate\Support\Facades\Auth::check()) {
            $author = \Illuminate\Support\Facades\Auth::user()->name;
        }
        if (empty($author)) {
            $author = 'Admin';
        }

        $slides = $this->handleUploadedImagesMultiple($request, 'images');
        $coverUrl = !empty($slides) ? $slides[0] : null;

        $singleCover = $this->handleUploadedImage($request, 'image', 'image_url', '');
        if (!empty($singleCover)) {
            if (empty($coverUrl)) {
                $coverUrl = $singleCover;
            }
            if (empty($slides)) {
                $slides = [$singleCover];
            }
        }

        if (empty($title) && $request->hasFile('images')) {
            $firstFile = is_array($request->file('images')) ? $request->file('images')[0] : $request->file('images');
            $originalName = pathinfo($firstFile->getClientOriginalName(), PATHINFO_FILENAME);
            $title = ucwords(str_replace(['-', '_'], ' ', $originalName));
        }

        if (empty($title)) {
            $title = 'New Web Story';
        }

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        if ($coverUrl || !empty($slides)) {
            $publishDate = $request->filled('publish_date') ? \Carbon\Carbon::parse($request->input('publish_date')) : now();

            \App\Models\WebStory::create([
                'title' => $title,
                'slug' => $slug,
                'image_url' => $coverUrl,
                'tag' => $request->input('tag', 'STORY'),
                'display_order' => (int)$request->input('display_order', 1),
                'keywords' => $request->input('keywords', ''),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'slides' => $slides,
                'author' => $author,
                'created_at' => $publishDate,
                'updated_at' => $publishDate,
            ]);

            return redirect()->route('admin.story')->with('success', 'Web Story created successfully!');
        }

        return redirect()->route('admin.story')->with('error', 'Please upload at least one image or provide an image URL.');
    }

    public function showCreateMatchForm()
    {
        $liveMatches = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue', 'tournament'])
            ->where('status', 'live')
            ->orderBy('match_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $upcomingMatches = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue', 'tournament'])
            ->whereIn('status', ['upcoming', 'scheduled'])
            ->orderBy('match_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $completedMatches = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue', 'tournament'])
            ->where('status', 'completed')
            ->orderBy('match_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $matches = CricketMatch::has('team1')->has('team2')->with(['team1', 'team2'])->orderBy('id', 'desc')->get();
        $tournaments = Tournament::where('is_approved', true)->with('teams')->orderBy('id', 'desc')->get();

        return view('admin.create_match', compact('liveMatches', 'upcomingMatches', 'completedMatches', 'matches', 'tournaments'));
    }

    public function showAddSeriesForm(Request $request)
    {
        try {
            $editItem = null;
            if ($request->query('edit')) {
                $editItem = Tournament::find($request->query('edit'));
            }
            $tournaments = Tournament::with(['teams', 'matches', 'user'])->orderBy('id', 'desc')->get();
            $allTeams = Team::orderBy('name', 'asc')->get();
            $allVenues = Venue::orderBy('name', 'asc')->get();
            return view('admin.add_series', compact('tournaments', 'editItem', 'allTeams', 'allVenues'));
        } catch (\Throwable $e) {
            $tournaments = Tournament::orderBy('id', 'desc')->get();
            $editItem = null;
            $allTeams = collect([]);
            $allVenues = collect([]);
            return view('admin.add_series', compact('tournaments', 'editItem', 'allTeams', 'allVenues'))->with('error', 'Loaded in safe mode: ' . $e->getMessage());
        }
    }

    public function showAddFantasyTipForm(Request $request)
    {
        return $this->showAddPredictionForm($request);
    }

    public function showAddPredictionForm(Request $request)
    {
        $editItem = null;
        $editType = 'PREDICTION';
        if ($request->query('edit')) {
            $editItem = \App\Models\Prediction::find($request->query('edit'));
            if (!$editItem) {
                $editItem = \App\Models\FantasyTip::find($request->query('edit'));
                if ($editItem) $editType = 'FANTASY';
            }
        }
        $predictions = \App\Models\Prediction::where('tag', '!=', 'MATCH PREVIEW')->orderBy('id', 'desc')->get();
        $fantasyTips = \App\Models\FantasyTip::orderBy('id', 'desc')->get();
        return view('admin.add_prediction', compact('predictions', 'fantasyTips', 'editItem', 'editType'));
    }

    public function showAddArticleForm(Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\Article::find($request->query('edit'));
        }
        $articles = \App\Models\Article::orderBy('id', 'desc')->get();
        return view('admin.add_article', compact('articles', 'editItem'));
    }

    public function showAddNewsForm(Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\News::find($request->query('edit'));
        }
        $news = \App\Models\News::orderBy('id', 'desc')->get();
        return view('admin.add_news', compact('news', 'editItem'));
    }

    public function showAddPopularTeamForm(Request $request)
    {
        return $this->showTeamsForm($request);
    }

    public function showAddTeamRankingForm(Request $request)
    {
        $editTeam = null;
        $editPlayer = null;
        if ($request->query('edit_team')) {
            $editTeam = TeamRanking::find($request->query('edit_team'));
        } elseif ($request->query('edit_player')) {
            $editPlayer = \App\Models\PlayerRanking::find($request->query('edit_player'));
        }
        $teamRankings = TeamRanking::orderBy('rank_num', 'asc')->get();
        $playerRankings = \App\Models\PlayerRanking::orderBy('type')->orderBy('rank_num', 'asc')->get();
        return view('admin.add_team_ranking', compact('teamRankings', 'playerRankings', 'editTeam', 'editPlayer'));
    }

    public function showAddWebStoryForm(Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\WebStory::find($request->query('edit'));
        }
        $webStories = \App\Models\WebStory::orderBy('id', 'desc')->get();
        return view('admin.add_web_story', compact('webStories', 'editItem'));
    }

    public function showAddGlossaryForm(Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\GlossaryTerm::find($request->query('edit'));
        }
        $glossaryTerms = \App\Models\GlossaryTerm::orderBy('id', 'desc')->get();
        return view('admin.add_glossary', compact('glossaryTerms', 'editItem'));
    }

    public function addGlossary(Request $request)
    {
        $term = trim($request->input('term', ''));
        $definition = trim($request->input('definition', ''));

        $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($term);
        }

        $letter = trim($request->input('letter', ''));
        if (empty($letter)) {
            $letter = strtoupper(substr($term, 0, 1));
        }

        if (!empty($term) && !empty($definition)) {
            \App\Models\GlossaryTerm::create([
                'term' => $term,
                'slug' => $slug,
                'definition' => $definition,
                'letter' => $letter,
                'keywords' => $request->input('keywords', ''),
                'poster_image' => $posterUrl,
                'display_order' => (int)$request->input('display_order', 1),
            ]);

            return redirect()->route('admin.glossary')->with('success', 'Glossary Term created successfully!');
        }

        return redirect()->route('admin.glossary')->with('error', 'Term and Definition are required.');
    }

    public function updatePrediction(Request $request, $id)
    {
        $type = strtoupper($request->input('type', 'PREDICTION'));
        $title = trim($request->input('title', ''));
        $summary = trim($request->input('summary', ''));

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        if ($type === 'FANTASY') {
            $item = \App\Models\FantasyTip::find($id);
            $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', $item ? $item->poster_image : '');
            if (!$item) {
                \App\Models\Prediction::where('id', $id)->delete();
                \App\Models\FantasyTip::create([
                    'tag' => 'FANTASY',
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'full_content' => $summary,
                    'meta_description' => $request->input('meta_description', $summary),
                    'keywords' => $request->input('keywords', ''),
                    'poster_image' => $posterUrl,
                    'display_order' => (int)$request->input('display_order', 1),
                    'is_enabled' => $request->has('is_enabled') ? true : false,
                ]);
                return redirect()->route('admin.prediction')->with('success', 'Saved as Fantasy Tip successfully!');
            }
            $item->update([
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'full_content' => $summary,
                'meta_description' => $request->input('meta_description', $summary),
                'keywords' => $request->input('keywords', $item->keywords),
                'poster_image' => $posterUrl ?: $item->poster_image,
                'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'tag' => 'FANTASY'
            ]);
            return redirect()->route('admin.prediction')->with('success', 'Fantasy Tip updated successfully!');
        } else {
            $item = \App\Models\Prediction::find($id);
            $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', $item ? $item->poster_image : '');
            if (!$item) {
                \App\Models\FantasyTip::where('id', $id)->delete();
                \App\Models\Prediction::create([
                    'tag' => 'MATCH PREDICTION',
                    'match_title' => 'Match Prediction',
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'full_content' => $summary,
                    'meta_description' => $request->input('meta_description', $summary),
                    'keywords' => $request->input('keywords', ''),
                    'poster_image' => $posterUrl,
                    'display_order' => (int)$request->input('display_order', 1),
                    'is_enabled' => $request->has('is_enabled') ? true : false,
                ]);
                return redirect()->route('admin.prediction')->with('success', 'Saved as Match Prediction successfully!');
            }
            $item->update([
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'full_content' => $summary,
                'meta_description' => $request->input('meta_description', $summary),
                'keywords' => $request->input('keywords', $item->keywords),
                'poster_image' => $posterUrl ?: $item->poster_image,
                'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
                'is_enabled' => $request->has('is_enabled') ? true : false,
                'tag' => 'MATCH PREDICTION'
            ]);
            return redirect()->route('admin.prediction')->with('success', 'Match Prediction updated successfully!');
        }
    }

    public function deletePrediction($id)
    {
        \App\Models\Prediction::findOrFail($id)->delete();
        return redirect()->route('admin.prediction')->with('success', 'Prediction deleted successfully!');
    }

    public function updateFantasyTip(Request $request, $id)
    {
        return $this->updatePrediction($request, $id);
    }

    public function deleteFantasyTip($id)
    {
        \App\Models\FantasyTip::findOrFail($id)->delete();
        return redirect()->route('admin.prediction')->with('success', 'Fantasy Tip deleted successfully!');
    }

    public function updateArticle(Request $request, $id)
    {
        $item = \App\Models\Article::findOrFail($id);
        
        $imageUrl = $this->handleUploadedImage($request, 'poster_file', 'image_url', $item->image_url);

        $title = trim($request->input('title', $item->title));
        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        $content = $request->input('content', $item->content);
        $summary = trim($request->input('meta_description', ''));
        if (empty($summary) && !empty($content)) {
            $summary = substr(strip_tags($content), 0, 160);
        } elseif (empty($summary)) {
            $summary = $item->summary;
        }

        $item->update([
            'category' => $request->input('category', $item->category),
            'title' => $title,
            'slug' => $slug,
            'meta_description' => $request->input('meta_description', $item->meta_description),
            'keywords' => $request->input('keywords', $item->keywords),
            'h1_heading' => $request->input('h1_heading', $item->h1_heading ?: $title),
            'content' => $content,
            'summary' => $summary,
            'image_url' => $imageUrl,
            'read_time' => $request->input('read_time', $item->read_time),
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
            'is_enabled' => $request->has('is_enabled') ? true : false,
        ]);
        return redirect()->route('admin.article')->with('success', 'Article updated successfully!');
    }

    public function deleteArticle($id)
    {
        \App\Models\Article::findOrFail($id)->delete();
        return redirect()->route('admin.article')->with('success', 'Article deleted successfully!');
    }

    public function updateNews(Request $request, $id)
    {
        $item = \App\Models\News::findOrFail($id);

        $imageUrl = $this->handleUploadedImage($request, 'poster_file', 'image_url', $item->image_url);

        $title = trim($request->input('title', $item->title));
        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        $content = $request->input('content', $item->content);
        $summary = trim($request->input('meta_description', ''));
        if (empty($summary) && !empty($content)) {
            $summary = substr(strip_tags($content), 0, 160);
        } elseif (empty($summary)) {
            $summary = $request->input('summary', $item->summary);
        }

        $item->update([
            'category' => $request->input('category', $item->category),
            'title' => $title,
            'slug' => $slug,
            'meta_description' => $request->input('meta_description', $item->meta_description),
            'keywords' => $request->input('keywords', $item->keywords),
            'h1_heading' => $request->input('h1_heading', $item->h1_heading ?: $title),
            'content' => $content,
            'summary' => $summary,
            'image_url' => $imageUrl,
            'read_time' => $request->input('read_time', $item->read_time),
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
            'is_enabled' => $request->has('is_enabled') ? true : false,
        ]);

        return redirect()->route('admin.news')->with('success', 'News updated successfully!');
    }

    public function deleteNews($id)
    {
        \App\Models\News::findOrFail($id)->delete();
        return redirect()->route('admin.news')->with('success', 'News deleted successfully!');
    }

    public function updateWebStory(Request $request, $id)
    {
        $item = \App\Models\WebStory::findOrFail($id);
        
        $slides = $item->slides ?? [];
        $uploadedSlides = $this->handleUploadedImagesMultiple($request, 'images');
        if (!empty($uploadedSlides)) {
            $slides = $uploadedSlides;
        }

        $coverUrl = !empty($slides) ? $slides[0] : $item->image_url;
        $singleCover = $this->handleUploadedImage($request, 'image', 'image_url', '');
        if (!empty($singleCover)) {
            $coverUrl = $singleCover;
            if (empty($slides)) {
                $slides = [$singleCover];
            }
        }

        $author = trim($request->input('author', ''));
        if (empty($author)) {
            $author = $item->author ?? 'Admin';
        }

        $title = trim($request->input('title', $item->title));
        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        $updateData = [
            'title' => $title,
            'slug' => $slug,
            'author' => $author,
            'tag' => $request->input('tag', $item->tag ?? 'STORY'),
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
            'keywords' => $request->input('keywords', $item->keywords ?? ''),
            'is_enabled' => $request->has('is_enabled') ? true : false,
            'image_url' => $coverUrl,
            'slides' => $slides
        ];

        if ($request->filled('publish_date')) {
            $updateData['created_at'] = \Carbon\Carbon::parse($request->input('publish_date'));
        }

        $item->update($updateData);

        return redirect()->route('admin.story')->with('success', 'Web Story updated successfully!');
    }

    public function deleteWebStory($id)
    {
        \App\Models\WebStory::findOrFail($id)->delete();
        return redirect()->route('admin.story')->with('success', 'Web Story deleted successfully!');
    }

    public function updateGlossaryTerm(Request $request, $id)
    {
        $item = \App\Models\GlossaryTerm::findOrFail($id);
        $term = trim($request->input('term', $item->term));

        $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', $item->poster_image ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($term);
        }

        $letter = trim($request->input('letter', ''));
        if (empty($letter)) {
            $letter = strtoupper(substr($term, 0, 1));
        }

        $item->update([
            'term' => $term,
            'slug' => $slug,
            'definition' => trim($request->input('definition', $item->definition)),
            'letter' => $letter,
            'keywords' => $request->input('keywords', $item->keywords),
            'poster_image' => $posterUrl,
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1)
        ]);
        return redirect()->route('admin.glossary')->with('success', 'Glossary Term updated successfully!');
    }

    public function deleteGlossaryTerm($id)
    {
        \App\Models\GlossaryTerm::findOrFail($id)->delete();
        return redirect()->route('admin.glossary')->with('success', 'Glossary Term deleted successfully!');
    }

    public function showNotifications()
    {
        $pendingApprovals = Tournament::where('category', 'local')->where('is_approved', false)->with('user')->orderBy('id', 'desc')->get();
        $pendingDeletions = Tournament::where('category', 'local')->where('delete_requested', true)->with('user')->orderBy('id', 'desc')->get();

        return view('admin.notifications', compact('pendingApprovals', 'pendingDeletions'));
    }

    public function approveTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->is_approved = true;
        $tournament->save();
        return back()->with('success', "Tournament '{$tournament->name}' approved successfully!");
    }

    public function rejectDeletion($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->delete_requested = false;
        $tournament->save();
        return back()->with('success', "Deletion request for '{$tournament->name}' rejected. Tournament remains active.");
    }

    public function deleteTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $name = $tournament->name;
        
        // Delete related matches, balls, scorecard stats
        $matches = CricketMatch::where('tournament_id', $id)->get();
        foreach ($matches as $m) {
            \App\Models\BallByBall::where('match_id', $m->id)->delete();
            \App\Models\PlayerBattingStat::where('match_id', $m->id)->delete();
            \App\Models\PlayerBowlingStat::where('match_id', $m->id)->delete();
            $m->delete();
        }
        
        // Delete related teams and players
        $teams = \App\Models\Team::where('tournament_id', $id)->get();
        foreach ($teams as $t) {
            \App\Models\Player::where('team_id', $t->id)->delete();
            $t->delete();
        }
        
        $tournament->delete();
        return back()->with('success', "Tournament '{$name}' deleted successfully!");
    }

    public function addPlayerRanking(Request $request)
    {
        $playerName = trim($request->input('player_name', ''));
        $type = strtolower($request->input('type', 'batting'));
        if (!empty($playerName)) {
            $photoUrl = $this->handleUploadedImage($request, 'poster_file', 'photo_url', $request->input('poster_image', ''));

            $slug = trim($request->input('slug', ''));
            if (empty($slug)) {
                $slug = \Illuminate\Support\Str::slug($playerName);
            }

            \App\Models\PlayerRanking::create([
                'type' => $type,
                'rank_num' => (int)$request->input('rank_num', 1),
                'badge_text' => strtoupper($request->input('badge_text', 'IND')),
                'player_name' => $playerName,
                'slug' => $slug,
                'photo_url' => $photoUrl,
                'stat_value' => (int)$request->input('stat_value', 0),
                'display_order' => (int)$request->input('display_order', 1),
                'keywords' => $request->input('keywords', ''),
            ]);

            return redirect()->route('admin.ranking')->with('success', 'Player Ranking created successfully!');
        }

        return redirect()->route('admin.ranking')->with('error', 'Player name is required.');
    }

    public function updatePlayerRanking(Request $request, $id)
    {
        $item = \App\Models\PlayerRanking::findOrFail($id);
        $playerName = trim($request->input('player_name', $item->player_name));
        $type = strtolower($request->input('type', $item->type));

        $photoUrl = $this->handleUploadedImage($request, 'poster_file', 'photo_url', $item->photo_url ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($playerName);
        }

        $item->update([
            'type' => $type,
            'rank_num' => (int)$request->input('rank_num', $item->rank_num),
            'badge_text' => strtoupper($request->input('badge_text', $item->badge_text ?? 'IND')),
            'player_name' => $playerName,
            'slug' => $slug,
            'photo_url' => $photoUrl,
            'stat_value' => (int)$request->input('stat_value', $item->stat_value),
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
            'keywords' => $request->input('keywords', $item->keywords ?? ''),
        ]);

        return redirect()->route('admin.ranking')->with('success', 'Player Ranking updated successfully!');
    }

    public function deletePlayerRanking($id)
    {
        \App\Models\PlayerRanking::findOrFail($id)->delete();
        return redirect()->route('admin.ranking')->with('success', 'Player ranking deleted successfully!');
    }

    public function deleteTeamRanking($id)
    {
        TeamRanking::findOrFail($id)->delete();
        return redirect()->route('admin.ranking')->with('success', 'Team ranking deleted successfully!');
    }

    // =================== MATCH PREVIEW (Standalone) ===================

    public function showMatchPreviewForm(\Illuminate\Http\Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\Prediction::find($request->query('edit'));
        }
        $previews = \App\Models\Prediction::where('tag', 'MATCH PREVIEW')->orderBy('id', 'desc')->get();
        return view('admin.add_match_preview', compact('previews', 'editItem'));
    }

    public function addMatchPreview(\Illuminate\Http\Request $request)
    {
        $title = trim($request->input('title', ''));
        $summary = trim($request->input('summary', ''));
        if (empty($title)) {
            return back()->with('error', 'Title is required.')->withInput();
        }

        $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        \App\Models\Prediction::create([
            'tag' => 'MATCH PREVIEW',
            'match_title' => $title,
            'title' => $title,
            'slug' => $slug,
            'summary' => $summary,
            'full_content' => $summary,
            'meta_description' => $request->input('meta_description', substr(strip_tags($summary), 0, 160)),
            'keywords' => $request->input('keywords', ''),
            'poster_image' => $posterUrl,
            'display_order' => (int)$request->input('display_order', 1),
            'is_enabled' => $request->has('is_enabled') ? true : false,
        ]);
        return redirect()->route('admin.match-preview')->with('success', 'Match Preview published successfully!');
    }

    public function updateMatchPreview(\Illuminate\Http\Request $request, $id)
    {
        $item = \App\Models\Prediction::findOrFail($id);
        $title = trim($request->input('title', $item->title));

        $posterUrl = $this->handleUploadedImage($request, 'poster_file', 'poster_image', $item->poster_image ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($title);
        }

        $summary = trim($request->input('summary', $item->summary));

        $item->update([
            'title' => $title,
            'slug' => $slug,
            'summary' => $summary,
            'full_content' => $summary,
            'meta_description' => $request->input('meta_description', substr(strip_tags($summary), 0, 160)),
            'keywords' => $request->input('keywords', $item->keywords),
            'poster_image' => $posterUrl,
            'display_order' => (int)$request->input('display_order', $item->display_order ?? 1),
            'is_enabled' => $request->has('is_enabled') ? true : false,
            'tag' => 'MATCH PREVIEW',
        ]);
        return redirect()->route('admin.match-preview')->with('success', 'Match Preview updated successfully!');
    }

    public function deleteMatchPreview($id)
    {
        \App\Models\Prediction::findOrFail($id)->delete();
        return redirect()->route('admin.match-preview')->with('success', 'Match Preview deleted successfully!');
    }

    // =================== TEAMS MANAGEMENT ===================

    public function showTeamsForm(\Illuminate\Http\Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = Team::find($request->query('edit'));
        }
        $teams = Team::with(['tournament', 'players'])->orderBy('id', 'desc')->get();
        $tournaments = Tournament::orderBy('name', 'asc')->get();
        return view('admin.admin_teams', compact('teams', 'editItem', 'tournaments'));
    }

    public function createTeam(\Illuminate\Http\Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return back()->with('error', 'Team name is required.')->withInput();
        }
        $shortName = strtoupper(trim($request->input('short_name', strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)))));
        $logo = $this->handleUploadedImage($request, 'logo_file', 'logo_url', $request->input('logo', ''));

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        Team::create([
            'name' => $name,
            'slug' => $slug,
            'short_name' => $shortName ?: strtoupper(substr($name, 0, 3)),
            'tournament_id' => $request->input('tournament_id') ?: null,
            'team_type' => $request->input('team_type', 'international'),
            'color_code' => $request->input('color_code', '#2563eb'),
            'city' => $request->input('city', ''),
            'country' => $request->input('country', ''),
            'logo' => $logo,
            'logo_url' => $logo,
            'display_order' => (int)$request->input('display_order', 1),
            'description' => $request->input('description', ''),
            'keywords' => $request->input('keywords', ''),
        ]);
        return redirect()->route('admin.popular')->with('success', "Team '{$name}' created successfully!");
    }

    public function updateTeam(\Illuminate\Http\Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $name = trim($request->input('name', $team->name));
        $logo = $this->handleUploadedImage($request, 'logo_file', 'logo_url', $team->logo_url ?? $team->logo ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        $team->update([
            'name' => $name,
            'slug' => $slug,
            'short_name' => strtoupper(trim($request->input('short_name', $team->short_name))),
            'team_type' => $request->input('team_type', $team->team_type),
            'color_code' => $request->input('color_code', $team->color_code),
            'city' => $request->input('city', $team->city ?? ''),
            'country' => $request->input('country', $team->country ?? ''),
            'logo' => $logo,
            'logo_url' => $logo,
            'tournament_id' => $request->input('tournament_id') ?: $team->tournament_id,
            'display_order' => (int)$request->input('display_order', $team->display_order ?? 1),
            'description' => $request->input('description', $team->description ?? ''),
            'keywords' => $request->input('keywords', $team->keywords ?? ''),
        ]);
        return redirect()->route('admin.popular')->with('success', 'Team updated successfully!');
    }

    public function deleteTeamEntry($id)
    {
        $team = Team::findOrFail($id);
        \App\Models\Player::where('team_id', $id)->delete();
        $team->delete();
        return redirect()->route('admin.popular')->with('success', 'Team deleted successfully!');
    }

    // =================== PLAYERS MANAGEMENT ===================

    public function showPlayersForm(\Illuminate\Http\Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\Player::find($request->query('edit'));
        }
        $players = \App\Models\Player::with('team')->orderBy('id', 'desc')->get();
        $teams = Team::orderBy('name', 'asc')->get();
        return view('admin.admin_players', compact('players', 'editItem', 'teams'));
    }

    public function createPlayer(\Illuminate\Http\Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return back()->with('error', 'Player name is required.')->withInput();
        }
        $country = trim($request->input('country', $request->input('nationality', '')));

        $profileImage = $this->handleUploadedImage($request, 'poster_file', 'profile_image', '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        $dob = $request->input('date_of_birth') ?: null;
        $birthdayText = $dob ? date('d M', strtotime($dob)) : null;

        \App\Models\Player::create([
            'name' => $name,
            'slug' => $slug,
            'short_name' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)),
            'initials' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2)),
            'team_id' => $request->input('team_id') ?: null,
            'role' => $request->input('role', 'Batsman'),
            'batting_style' => $request->input('batting_style', ''),
            'bowling_style' => $request->input('bowling_style', ''),
            'country' => $country,
            'nationality' => $country,
            'jersey_number' => $request->input('jersey_number', ''),
            'date_of_birth' => $dob,
            'birthday_text' => $birthdayText,
            'profile_image' => $profileImage,
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'display_order' => (int)$request->input('display_order', 1),
            'description' => $request->input('description', ''),
            'keywords' => $request->input('keywords', ''),
        ]);
        return redirect()->route('admin.players')->with('success', "Player '{$name}' created successfully!");
    }

    public function updatePlayer(\Illuminate\Http\Request $request, $id)
    {
        $player = \App\Models\Player::findOrFail($id);
        $name = trim($request->input('name', $player->name));
        $country = trim($request->input('country', $request->input('nationality', $player->country ?? $player->nationality ?? '')));

        $profileImage = $this->handleUploadedImage($request, 'poster_file', 'profile_image', $player->profile_image ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        $dob = $request->input('date_of_birth') ?: $player->date_of_birth;
        $birthdayText = $dob ? date('d M', strtotime($dob)) : null;

        $player->update([
            'name' => $name,
            'slug' => $slug,
            'short_name' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)),
            'initials' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2)),
            'team_id' => $request->input('team_id') ?: $player->team_id,
            'role' => $request->input('role', $player->role ?? 'Batsman'),
            'batting_style' => $request->input('batting_style', $player->batting_style ?? ''),
            'bowling_style' => $request->input('bowling_style', $player->bowling_style ?? ''),
            'country' => $country,
            'nationality' => $country,
            'jersey_number' => $request->input('jersey_number', $player->jersey_number ?? ''),
            'date_of_birth' => $dob,
            'birthday_text' => $birthdayText,
            'profile_image' => $profileImage,
            'is_popular' => $request->has('is_popular') ? 1 : 0,
            'display_order' => (int)$request->input('display_order', $player->display_order ?? 1),
            'description' => $request->input('description', $player->description ?? ''),
            'keywords' => $request->input('keywords', $player->keywords ?? ''),
        ]);
        return redirect()->route('admin.players')->with('success', 'Player updated successfully!');
    }

    public function deletePlayerEntry($id)
    {
        \App\Models\Player::findOrFail($id)->delete();
        return redirect()->route('admin.players')->with('success', 'Player deleted successfully!');
    }

    // =================== VENUES MANAGEMENT ===================

    public function showVenuesForm(\Illuminate\Http\Request $request)
    {
        $editItem = null;
        if ($request->query('edit')) {
            $editItem = \App\Models\Venue::find($request->query('edit'));
        }
        $venues = \App\Models\Venue::orderBy('id', 'desc')->get();
        return view('admin.admin_venues', compact('venues', 'editItem'));
    }

    public function createVenue(\Illuminate\Http\Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return back()->with('error', 'Venue name is required.')->withInput();
        }
        $imageUrl = $this->handleUploadedImage($request, 'image_file', 'image_url', '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        \App\Models\Venue::create([
            'name' => $name,
            'slug' => $slug,
            'city' => $request->input('city', ''),
            'country' => $request->input('country', 'India'),
            'capacity' => $request->input('capacity', ''),
            'image_url' => $imageUrl,
            'display_order' => (int)$request->input('display_order', 1),
            'description' => $request->input('description', ''),
            'keywords' => $request->input('keywords', ''),
        ]);
        return redirect()->route('admin.venues')->with('success', "Venue '{$name}' created successfully!");
    }

    public function updateVenue(\Illuminate\Http\Request $request, $id)
    {
        $venue = \App\Models\Venue::findOrFail($id);
        $name = trim($request->input('name', $venue->name));
        $imageUrl = $this->handleUploadedImage($request, 'image_file', 'image_url', $venue->image_url ?? '');

        $slug = trim($request->input('slug', ''));
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($name);
        }

        $venue->update([
            'name' => $name,
            'slug' => $slug,
            'city' => $request->input('city', $venue->city ?? ''),
            'country' => $request->input('country', $venue->country ?? 'India'),
            'capacity' => $request->input('capacity', $venue->capacity ?? ''),
            'image_url' => $imageUrl,
            'display_order' => (int)$request->input('display_order', $venue->display_order ?? 1),
            'description' => $request->input('description', $venue->description ?? ''),
            'keywords' => $request->input('keywords', $venue->keywords ?? ''),
        ]);
        return redirect()->route('admin.venues')->with('success', 'Venue updated successfully!');
    }

    public function deleteVenue($id)
    {
        \App\Models\Venue::findOrFail($id)->delete();
        return redirect()->route('admin.venues')->with('success', 'Venue deleted successfully!');
    }

    // =================== QUICK ADD & JSON APIs FOR POPUPS ===================

    public function getTeamsJson()
    {
        $teams = Team::orderBy('name', 'asc')->get(['id', 'name', 'short_name', 'city', 'country', 'color_code', 'logo_url']);
        return response()->json(['success' => true, 'teams' => $teams]);
    }

    public function quickAddTeam(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Team name is required.'], 422);
        }

        $shortName = trim($request->input('short_name', ''));
        if (empty($shortName)) {
            $shortName = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        }

        $slug = \Illuminate\Support\Str::slug($name);
        $colorCode = $request->input('color_code', '#0284c7');
        $teamType = $request->input('team_type', 'Club');
        $country = $request->input('country', 'India');

        $team = Team::create([
            'name' => $name,
            'short_name' => $shortName,
            'slug' => $slug,
            'color_code' => $colorCode,
            'team_type' => $teamType,
            'country' => $country,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Team '{$team->name}' added successfully!",
            'team' => $team
        ]);
    }

    public function getVenuesJson()
    {
        $venues = Venue::orderBy('name', 'asc')->get(['id', 'name', 'city', 'country', 'capacity', 'image_url']);
        return response()->json(['success' => true, 'venues' => $venues]);
    }

    public function quickAddVenue(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Venue name is required.'], 422);
        }

        $city = trim($request->input('city', ''));
        $country = trim($request->input('country', 'India'));
        $capacity = trim($request->input('capacity', ''));
        $slug = \Illuminate\Support\Str::slug($name);

        $venue = Venue::create([
            'name' => $name,
            'slug' => $slug,
            'city' => $city,
            'country' => $country,
            'capacity' => $capacity
        ]);

        return response()->json([
            'success' => true,
            'message' => "Venue '{$venue->name}' added successfully!",
            'venue' => $venue
        ]);
    }

    /**
     * Show CricketData.org API matches management screen
     */
    public function showApiMatches(Request $request, \App\Services\CricketApiService $apiService)
    {
        $statusFilter = $request->query('status', 'all');
        $approvalFilter = $request->query('approval', 'all');
        $search = trim($request->query('search', ''));

        $query = CricketMatch::where('is_api_match', true)->with(['team1', 'team2', 'venue', 'tournament']);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($approvalFilter === 'approved') {
            $query->where('is_approved', true);
        } elseif ($approvalFilter === 'pending') {
            $query->where('is_approved', false);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('team1', function($tq) use ($search) {
                    $tq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('team2', function($tq) use ($search) {
                    $tq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('venue', function($vq) use ($search) {
                    $vq->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $matches = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $stats = $apiService->getApiUsageStats();

        $totalApiMatches = CricketMatch::where('is_api_match', true)->count();
        $approvedCount = CricketMatch::where('is_api_match', true)->where('is_approved', true)->count();
        $pendingCount = CricketMatch::where('is_api_match', true)->where('is_approved', false)->count();
        $liveCount = CricketMatch::where('is_api_match', true)->where('status', 'live')->count();

        return view('admin.api_matches', compact(
            'matches',
            'stats',
            'statusFilter',
            'approvalFilter',
            'search',
            'totalApiMatches',
            'approvedCount',
            'pendingCount',
            'liveCount'
        ));
    }

    /**
     * Trigger CricketData API sync on-demand
     */
    public function fetchApiMatches(Request $request, \App\Services\CricketApiService $apiService)
    {
        $autoApprove = $request->boolean('auto_approve', false);
        $result = $apiService->syncCurrentMatches($autoApprove);

        if ($result['success']) {
            return redirect()->route('admin.api-matches')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.api-matches')
            ->with('error', $result['message']);
    }

    /**
     * Toggle match approval status (Published / Pending)
     */
    public function toggleApiMatchApproval($id)
    {
        $match = CricketMatch::findOrFail($id);
        $match->is_approved = !$match->is_approved;
        $match->save();

        $msg = $match->is_approved 
            ? "Match '{$match->team1?->name} vs {$match->team2?->name}' approved & published to live site!" 
            : "Match '{$match->team1?->name} vs {$match->team2?->name}' hidden from live site.";

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete an API match
     */
    public function deleteApiMatch($id)
    {
        $match = CricketMatch::findOrFail($id);
        $name = "{$match->team1?->name} vs {$match->team2?->name}";
        $match->delete();

        return redirect()->back()->with('success', "API Match '{$name}' deleted successfully.");
    }
}

