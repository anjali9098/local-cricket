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
use App\Models\IccPlayerRanking;
use App\Models\Player;
use App\Models\Venue;
use App\Models\WebStory;
use App\Models\GlossaryTerm;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $todayDate = \Carbon\Carbon::today()->toDateString();

        // 1. Live & Upcoming Matches (Cached for 45 seconds so live updates stay fast and avoid query spikes)
        $allApprovedMatches = Cache::remember('home_approved_matches_v4', 45, function() {
            return CricketMatch::approved()
                ->has('team1')->has('team2')
                ->with(['team1', 'team2', 'venue', 'tournament'])
                ->where(function($q) {
                    $q->whereNull('tournament_id')
                      ->orWhereHas('tournament', function($tq) {
                          $tq->where('is_approved', true);
                      });
                })
                ->orderBy('id', 'desc')
                ->take(20)
                ->get();
        });

        // 1. Live Matches (Active live matches playing right now or today)
        $liveMatches = $allApprovedMatches->filter(function($m) {
            return $m->effective_status === 'live';
        })->values();

        // 2. Upcoming Matches (Scheduled future matches)
        $upcomingMatches = $allApprovedMatches->filter(function($m) {
            return $m->effective_status === 'upcoming';
        })->sortBy('match_date')->values();

        // Top carousel: strictly LIVE matches first, followed by UPCOMING matches (no completed matches)
        $allMatches = $liveMatches->merge($upcomingMatches);

        // 2. Series & Tournaments (Cached for 120 seconds)
        $seriesData = Cache::remember('home_series_data_v3', 120, function() {
            $allTournaments = Tournament::with([
                    'matches' => function($q) {
                        $q->has('team1')->has('team2')->orderBy('id', 'desc')->take(3);
                    },
                    'matches.team1', 'matches.team2', 'teams'
                ])
                ->where(function($q) { $q->whereNull('is_approved')->orWhere('is_approved', true); })
                ->where(function($q) { $q->whereNull('is_enabled')->orWhere('is_enabled', true); })
                ->orderBy('id', 'desc')
                ->take(24)
                ->get();

            $liveSeries      = collect();
            $ongoingSeries   = collect();
            $upcomingSeries  = collect();
            $completedSeries = collect();
            $localSeries     = collect();

            foreach ($allTournaments as $tournament) {
                if (strtoupper($tournament->series_type ?? '') === 'LOCAL' || strtolower($tournament->category ?? '') === 'local') {
                    $localSeries->push($tournament);
                }

                $matchStatuses = $tournament->matches->map(fn($m) => strtolower($m->effective_status ?? $m->status ?? ''))->toArray();
                $tStatus = strtolower($tournament->status ?? 'upcoming');

                if ($tStatus === 'live' || in_array('live', $matchStatuses)) {
                    $liveSeries->push($tournament);
                    $ongoingSeries->push($tournament);
                } elseif ($tStatus === 'completed' || (!empty($matchStatuses) && count(array_filter($matchStatuses, fn($s) => $s === 'completed')) === count($matchStatuses))) {
                    $completedSeries->push($tournament);
                } elseif ($tStatus === 'ongoing' || (!empty($matchStatuses) && in_array('completed', $matchStatuses))) {
                    $ongoingSeries->push($tournament);
                } else {
                    $upcomingSeries->push($tournament);
                }
            }

            return compact('liveSeries', 'ongoingSeries', 'upcomingSeries', 'completedSeries', 'localSeries');
        });

        $liveSeries      = $seriesData['liveSeries'];
        $ongoingSeries   = $seriesData['ongoingSeries'];
        $upcomingSeries  = $seriesData['upcomingSeries'];
        $completedSeries = $seriesData['completedSeries'];
        $localSeries     = $seriesData['localSeries'];

        // 3. Editorial & Static Content (Cached for 5 minutes)
        $editorial = Cache::remember('home_editorial_v3', 300, function() {
            return [
                'predictions'   => Prediction::where('tag', '!=', 'MATCH PREVIEW')->orderBy('id', 'desc')->take(6)->get(),
                'fantasyTips'   => FantasyTip::orderBy('id', 'desc')->take(6)->get(),
                'matchPreviews' => Prediction::where('tag', 'MATCH PREVIEW')->orderBy('id', 'desc')->take(6)->get(),
                'articles'      => Article::orderBy('id', 'desc')->take(6)->get(),
                'newsList'      => News::orderBy('id', 'desc')->take(6)->get(),
                'popularTeams'  => Team::orderBy('id', 'desc')->take(6)->get(),
                'teamRankings'  => TeamRanking::orderBy('rank_num', 'asc')->take(10)->get(),
                'venues'        => Venue::orderBy('id', 'desc')->take(6)->get(),
                'webStories'    => WebStory::where(function($q) {
                    $q->whereNull('is_enabled')->orWhere('is_enabled', true);
                })->orderBy('id', 'desc')->take(6)->get(),
                'glossaryTerms' => GlossaryTerm::orderBy('id', 'desc')->take(6)->get(),
            ];
        });

        $predictions   = $editorial['predictions'];
        $fantasyTips   = $editorial['fantasyTips'];
        $matchPreviews = $editorial['matchPreviews'];
        $articles      = $editorial['articles'];
        $newsList      = $editorial['newsList'];
        $popularTeams  = $editorial['popularTeams'];
        $teamRankings  = $editorial['teamRankings'];
        $venues        = $editorial['venues'];
        $webStories    = $editorial['webStories'];
        $glossaryTerms = $editorial['glossaryTerms'];

        // 4. ICC Multi-format Rankings for Homepage (Cached for 30 minutes, eliminates 12 DB queries!)
        $iccRankings = Cache::remember('home_icc_rankings_v3', 1800, function() {
            return [
                'teams' => [
                    'TEST' => TeamRanking::where('gender', 'men')->where('format', 'TEST')->orderBy('rank_num')->take(5)->get(),
                    'ODI'  => TeamRanking::where('gender', 'men')->where('format', 'ODI')->orderBy('rank_num')->take(5)->get(),
                    'T20'  => TeamRanking::where('gender', 'men')->where('format', 'T20')->orderBy('rank_num')->take(5)->get(),
                ],
                'batting' => [
                    'TEST' => IccPlayerRanking::where('gender', 'men')->where('category', 'batting')->where('format', 'TEST')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'ODI'  => IccPlayerRanking::where('gender', 'men')->where('category', 'batting')->where('format', 'ODI')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'T20'  => IccPlayerRanking::where('gender', 'men')->where('category', 'batting')->where('format', 'T20')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                ],
                'bowling' => [
                    'TEST' => IccPlayerRanking::where('gender', 'men')->where('category', 'bowling')->where('format', 'TEST')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'ODI'  => IccPlayerRanking::where('gender', 'men')->where('category', 'bowling')->where('format', 'ODI')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'T20'  => IccPlayerRanking::where('gender', 'men')->where('category', 'bowling')->where('format', 'T20')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                ],
                'all_rounder' => [
                    'TEST' => IccPlayerRanking::where('gender', 'men')->where('category', 'all_rounder')->where('format', 'TEST')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'ODI'  => IccPlayerRanking::where('gender', 'men')->where('category', 'all_rounder')->where('format', 'ODI')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                    'T20'  => IccPlayerRanking::where('gender', 'men')->where('category', 'all_rounder')->where('format', 'T20')->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')->take(5)->get(),
                ],
            ];
        });

        // 5. Dynamic Batting, Bowling Rankings & Points Table (Cached for 10 minutes)
        $statsAndPoints = Cache::remember('home_stats_and_points_v3', 600, function() {
            // Batting Rankings
            $battingRankings = DB::table('player_batting_stats')
                ->select('player_name', DB::raw('SUM(runs) as stat_value'))
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
                $battingRankings = IccPlayerRanking::where('category', 'batting')
                    ->whereNotNull('current_rank')
                    ->where('current_rank', '!=', '--')
                    ->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')
                    ->take(10)
                    ->get()
                    ->map(function($r, $idx) {
                        return (object)[
                            'rank_num' => $idx + 1,
                            'player_name' => $r->player_name,
                            'badge_text' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $r->country ?: $r->player_name), 0, 3)),
                            'stat_value' => $r->best_rank && $r->best_rank !== '--' ? 'Best #' . $r->best_rank : '--',
                        ];
                    });
            }

            // Bowling Rankings
            $bowlingRankings = DB::table('player_bowling_stats')
                ->select('player_name', DB::raw('SUM(wickets) as stat_value'), DB::raw('SUM(runs) as runs_conceded'))
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
                $bowlingRankings = IccPlayerRanking::where('category', 'bowling')
                    ->whereNotNull('current_rank')
                    ->where('current_rank', '!=', '--')
                    ->orderByRaw('CAST(current_rank AS UNSIGNED) ASC')
                    ->take(10)
                    ->get()
                    ->map(function($r, $idx) {
                        return (object)[
                            'rank_num' => $idx + 1,
                            'player_name' => $r->player_name,
                            'badge_text' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $r->country ?: $r->player_name), 0, 3)),
                            'stat_value' => $r->best_rank && $r->best_rank !== '--' ? 'Best #' . $r->best_rank : '--',
                        ];
                    });
            }

            // Points Table
            $activeTournament = Tournament::whereHas('teams')
                ->whereHas('matches')
                ->take(10)
                ->get()
                ->sortByDesc(function($t) {
                    $completed = $t->matches->filter(fn($m) => in_array($m->status, ['completed', 'live']))->count();
                    return ($completed * 100) + $t->teams->count();
                })
                ->first();

            if (!$activeTournament) {
                $activeTournament = Tournament::whereHas('teams')->first() ?? Tournament::first();
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
                            $status = strtolower($tm->effective_status ?? $tm->status ?? '');
                            if (in_array($status, ['completed', 'live'])) {
                                $played++;
                            }
                            if ($status === 'completed') {
                                $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($tm->team1_score ?? '0'))[0] ?? '0');
                                $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($tm->team2_score ?? '0'))[0] ?? '0');

                                $winnerId = null;
                                if ($s1 > $s2) {
                                    $winnerId = $tm->team1_id;
                                } elseif ($s2 > $s1) {
                                    $winnerId = $tm->team2_id;
                                }

                                if ($winnerId && $winnerId == $team->id) {
                                    $won++;
                                    $points += 2;
                                } elseif ($winnerId && $winnerId != $team->id) {
                                    $lost++;
                                } elseif ($s1 > 0 && $s1 === $s2) {
                                    $points += 1;
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
                        'lost' => $lost,
                        'points' => $points,
                        'tournament_name' => $activeTournament->name
                    ]);
                }

                $pointsTable = $pointsTable->sortByDesc('points')->values();
            }

            return compact('battingRankings', 'bowlingRankings', 'pointsTable');
        });

        $battingRankings = $statsAndPoints['battingRankings'];
        $bowlingRankings = $statsAndPoints['bowlingRankings'];
        $pointsTable     = $statsAndPoints['pointsTable'];

        // 6. Real Player Birthdays (Cached for 1 hour)
        $playerBirthdays = Cache::remember('home_player_birthdays_' . $todayDate, 3600, function() {
            $today = \Carbon\Carbon::today();
            return Player::whereNotNull('date_of_birth')
                ->with('team')
                ->take(120)
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
        });

        // 7. Popular Players (Cached for 10 minutes)
        $popularPlayers = Cache::remember('home_popular_players_v3', 600, function() {
            return Player::with('team')->orderByRaw("CASE WHEN is_popular = 1 THEN 1 ELSE 2 END")->orderBy('id', 'desc')->take(6)->get();
        });

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
            'glossaryTerms',
            'iccRankings'
        ));
    }
}
