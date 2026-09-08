<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CricketMatch;
use App\Models\Tournament;
use App\Models\Prediction;
use App\Models\FantasyTip;
use App\Models\Article;
use App\Models\News;
use App\Models\Team;
use App\Models\TeamRanking;
use App\Models\PlayerRanking;
use App\Models\PointsTable;
use App\Models\PlayerBirthday;
use App\Models\Player;
use App\Models\Venue;
use App\Models\WebStory;
use App\Models\GlossaryTerm;

class HomeController extends Controller
{
    public function index()
    {
        $todayDate = \Carbon\Carbon::today()->toDateString();

        $allMatches = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue', 'tournament'])
            ->where(function($q) {
                $q->whereNull('tournament_id')
                  ->orWhereHas('tournament', function($tq) {
                      $tq->where('is_approved', true);
                  });
            })
            ->where(function($q) use ($todayDate) {
                // Live matches always show on home
                $q->where('status', 'live')
                  // Or matches scheduled/completed on today's date
                  ->orWhereDate('match_date', $todayDate);
            })
            ->orderByRaw("CASE WHEN status = 'live' THEN 1 WHEN status = 'scheduled' OR status = 'upcoming' THEN 2 ELSE 3 END")
            ->orderBy('match_date', 'asc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        if ($allMatches->isEmpty()) {
            $allMatches = CricketMatch::has('team1')->has('team2')
                ->with(['team1', 'team2', 'venue', 'tournament'])
                ->where(function($q) {
                    $q->whereNull('tournament_id')
                      ->orWhereHas('tournament', function($tq) {
                          $tq->where('is_approved', true);
                      });
                })
                ->whereIn('status', ['upcoming', 'scheduled'])
                ->whereDate('match_date', '>=', $todayDate)
                ->orderBy('match_date', 'asc')
                ->take(8)
                ->get();
        }

        // Fetch all tournaments with teams and recent matches (exclude empty tournaments without teams)
        $allTournaments = Tournament::with([
                'matches' => function($q) {
                    $q->has('team1')->has('team2')->orderBy('id', 'desc')->take(3);
                },
                'matches.team1', 'matches.team2', 'teams'
            ])
            ->where(function($q) { $q->whereNull('is_approved')->orWhere('is_approved', true); })
            ->where(function($q) { $q->whereNull('is_enabled')->orWhere('is_enabled', true); })
            ->where(function($q) {
                $q->has('teams', '>=', 2)
                  ->orWhereHas('matches', function($mq) {
                      $mq->has('team1')->has('team2');
                  });
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $liveSeries      = collect();
        $ongoingSeries   = collect();
        $upcomingSeries  = collect();
        $completedSeries = collect();
        $localSeries     = collect();

        foreach ($allTournaments as $tournament) {
            // Check if local grassroots tournament - always include in localSeries tab
            if (strtoupper($tournament->series_type ?? '') === 'LOCAL' || strtolower($tournament->category ?? '') === 'local') {
                $localSeries->push($tournament);
            }

            $matchStatuses = $tournament->matches->map(fn($m) => strtolower($m->effective_status ?? $m->status ?? ''))->toArray();
            $tStatus = strtolower($tournament->status ?? 'upcoming');

            // 1. Live: Has active live match or tournament explicitly marked live
            if ($tStatus === 'live' || in_array('live', $matchStatuses)) {
                $liveSeries->push($tournament);
                $ongoingSeries->push($tournament); // Live tournaments are also ongoing!
            }
            // 2. Completed: Tournament marked completed or all matches completed
            elseif ($tStatus === 'completed' || (!empty($matchStatuses) && count(array_filter($matchStatuses, fn($s) => $s === 'completed')) === count($matchStatuses))) {
                $completedSeries->push($tournament);
            }
            // 3. Ongoing: Tournament explicitly marked ongoing, or has completed matches with more to play
            elseif ($tStatus === 'ongoing' || (!empty($matchStatuses) && in_array('completed', $matchStatuses))) {
                $ongoingSeries->push($tournament);
            }
            // 4. Upcoming: Tournament upcoming, draft, published, or all matches scheduled
            else {
                $upcomingSeries->push($tournament);
            }
        }

        $predictions = Prediction::where('tag', '!=', 'MATCH PREVIEW')->orderBy('id', 'desc')->take(6)->get();
        $fantasyTips = FantasyTip::orderBy('id', 'desc')->take(6)->get();
        $matchPreviews = Prediction::where('tag', 'MATCH PREVIEW')->orderBy('id', 'desc')->take(6)->get();
        $articles = Article::orderBy('id', 'desc')->take(6)->get();
        $newsList = News::orderBy('id', 'desc')->take(6)->get();

        // 6 Latest Popular Teams for Homepage
        $popularTeams = Team::orderBy('id', 'desc')->take(6)->get();

        $teamRankings = TeamRanking::orderBy('rank_num', 'asc')->take(10)->get();

        // 100% Dynamic Real Batting Rankings from match batting scores
        $battingRankings = \Illuminate\Support\Facades\DB::table('player_batting_stats')
            ->select('player_name', \Illuminate\Support\Facades\DB::raw('SUM(runs) as stat_value'))
            ->groupBy('player_name')
            ->orderByDesc('stat_value')
            ->take(10)
            ->get()
            ->map(function($item, $index) {
                $item->rank_num = $index + 1;
                $cleanName = preg_replace('/[^A-Za-z]/', '', $item->player_name);
                $item->badge_text = strtoupper(substr($cleanName, 0, 3));
                return $item;
            });

        if ($battingRankings->isEmpty()) {
            $battingRankings = PlayerRanking::where('type', 'batting')->orderBy('rank_num', 'asc')->take(10)->get();
        }

        // 100% Dynamic Real Bowling Rankings from match bowling scores
        $bowlingRankings = \Illuminate\Support\Facades\DB::table('player_bowling_stats')
            ->select('player_name', \Illuminate\Support\Facades\DB::raw('SUM(wickets) as stat_value'), \Illuminate\Support\Facades\DB::raw('SUM(runs) as runs_conceded'))
            ->groupBy('player_name')
            ->orderByDesc('stat_value')
            ->orderBy('runs_conceded', 'asc')
            ->take(10)
            ->get()
            ->map(function($item, $index) {
                $item->rank_num = $index + 1;
                $cleanName = preg_replace('/[^A-Za-z]/', '', $item->player_name);
                $item->badge_text = strtoupper(substr($cleanName, 0, 3));
                return $item;
            });

        if ($bowlingRankings->isEmpty()) {
            $bowlingRankings = PlayerRanking::where('type', 'bowling')->orderBy('rank_num', 'asc')->take(10)->get();
        }

        // 100% Dynamic Real Points Table from active tournament & matches
        $activeTournament = Tournament::whereHas('matches')
            ->orderByRaw("CASE WHEN status = 'live' THEN 1 WHEN status = 'ongoing' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->first();

        if (!$activeTournament) {
            $activeTournament = Tournament::orderBy('id', 'desc')->first();
        }

        $pointsTable = collect();
        if ($activeTournament) {
            $tournamentMatches = CricketMatch::where('tournament_id', $activeTournament->id)->get();
            $teamIds = $activeTournament->teams()->pluck('teams.id')->toArray();
            foreach ($tournamentMatches as $tm) {
                if ($tm->team1_id && !in_array($tm->team1_id, $teamIds)) $teamIds[] = $tm->team1_id;
                if ($tm->team2_id && !in_array($tm->team2_id, $teamIds)) $teamIds[] = $tm->team2_id;
            }

            $tournamentTeams = Team::whereIn('id', $teamIds)->get();
            foreach ($tournamentTeams as $team) {
                $played = 0;
                $won = 0;
                $lost = 0;
                $points = 0;

                foreach ($tournamentMatches as $tm) {
                    if ($tm->team1_id == $team->id || $tm->team2_id == $team->id) {
                        if (in_array($tm->status, ['completed', 'live'])) {
                            $played++;
                        }
                        if ($tm->status === 'completed') {
                            if ($tm->winner_team_id == $team->id) {
                                $won++;
                                $points += 2;
                            } elseif ($tm->winner_team_id && $tm->winner_team_id != $team->id) {
                                $lost++;
                            }
                        }
                    }
                }

                $teamCode = $team->short_name ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $team->name), 0, 3));
                $pointsTable->push((object)[
                    'team_code' => $teamCode,
                    'team_name' => $team->name,
                    'played' => $played,
                    'won' => $won,
                    'points' => $points,
                    'tournament_name' => $activeTournament->name
                ]);
            }

            $pointsTable = $pointsTable->sortByDesc('points')->values();
        }

        if ($pointsTable->isEmpty()) {
            $pointsTable = PointsTable::orderBy('points', 'desc')->orderBy('won', 'desc')->take(6)->get();
        }

        // Real Player Birthdays from DB (Only players with explicit Date of Birth set)
        $today = \Carbon\Carbon::today();
        $playerBirthdays = Player::whereNotNull('date_of_birth')
            ->where('date_of_birth', '!=', '')
            ->with('team')
            ->get()
            ->map(function($player) use ($today) {
                try {
                    $dob = \Carbon\Carbon::parse($player->date_of_birth);
                } catch (\Exception $e) {
                    return null;
                }
                $thisYearBirthday = \Carbon\Carbon::create($today->year, $dob->month, $dob->day)->startOfDay();
                if ($thisYearBirthday->isPast() && !$thisYearBirthday->isToday()) {
                    $nextBirthday = \Carbon\Carbon::create($today->year + 1, $dob->month, $dob->day)->startOfDay();
                } else {
                    $nextBirthday = $thisYearBirthday;
                }
                $daysLeft = (int)ceil($today->diffInDays($nextBirthday, false));
                if ($daysLeft < 0) $daysLeft = 0;

                $player->days_until_birthday = $daysLeft;
                $player->is_today_birthday = $today->isSameDay($thisYearBirthday);
                $player->current_age = $dob->age;
                $player->turning_age = $player->is_today_birthday ? $dob->age : $dob->age + 1;
                $player->formatted_dob = $dob->format('d M Y');
                $player->birthday_date_text = $dob->format('d M');
                return $player;
            })->filter()->sortBy('days_until_birthday')->values()->take(6);

        // 6 Latest Popular Players for Homepage
        $popularPlayers = Player::with('team')->orderByRaw("CASE WHEN is_popular = 1 THEN 1 ELSE 2 END")->orderBy('display_order', 'asc')->orderBy('id', 'desc')->take(6)->get();

        // 6 Latest Venues for Homepage
        $venues = Venue::orderBy('id', 'desc')->take(6)->get();

        // 6 Latest Web Stories for Homepage
        $webStories = WebStory::where(function($q) {
            $q->whereNull('is_enabled')->orWhere('is_enabled', true);
        })->orderBy('id', 'desc')->take(6)->get();

        // 6 Latest Glossary Terms for Homepage
        $glossaryTerms = GlossaryTerm::orderBy('id', 'desc')->take(6)->get();

        return view('home', compact(
            'allMatches',
            'liveSeries',
            'ongoingSeries',
            'upcomingSeries',
            'completedSeries',
            'localSeries',
            'predictions',
            'fantasyTips',
            'matchPreviews',
            'articles',
            'newsList',
            'popularTeams',
            'teamRankings',
            'battingRankings',
            'bowlingRankings',
            'pointsTable',
            'playerBirthdays',
            'popularPlayers',
            'venues',
            'webStories',
            'glossaryTerms'
        ));
    }
}
