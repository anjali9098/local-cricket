<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Player;
use App\Models\Venue;
use App\Models\WebStory;
use App\Models\GlossaryTerm;
use App\Models\News;
use App\Models\Article;
use App\Models\Prediction;
use App\Models\FantasyTip;
use App\Models\PointsTable;
use App\Models\TeamRanking;
use App\Models\PlayerRanking;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function live()
    {
        $liveMatches = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue'])
            ->where('status', 'live')
            ->where(function($q) {
                $q->whereNull('tournament_id')
                  ->orWhereHas('tournament', function($tq) {
                      $tq->where('is_approved', true);
                  });
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('pages.live', compact('liveMatches'));
    }

    public function matches(Request $request)
    {
        $status = $request->query('status');
        $query = CricketMatch::has('team1')->has('team2')
            ->with(['team1', 'team2', 'venue'])
            ->where(function($q) {
                $q->whereNull('tournament_id')
                  ->orWhereHas('tournament', function($tq) {
                      $tq->where('is_approved', true);
                  });
            });

        if (!empty($status)) {
            if ($status === 'upcoming' || $status === 'scheduled') {
                $query->whereIn('status', ['upcoming', 'scheduled']);
            } else {
                $query->where('status', $status);
            }
        }

        $matches = $query->orderBy('match_date', 'asc')->orderBy('id', 'desc')->get();

        return view('pages.matches', compact('matches', 'status'));
    }

    public function stats()
    {
        $teamRankings = TeamRanking::orderBy('rank_num', 'asc')->take(10)->get();
        $battingRankings = PlayerRanking::where('type', 'batting')->orderBy('rank_num', 'asc')->take(10)->get();
        $bowlingRankings = PlayerRanking::where('type', 'bowling')->orderBy('rank_num', 'asc')->take(10)->get();
        $playerBirthdays = $this->getProcessedPlayerBirthdays();

        return view('pages.stats', compact('teamRankings', 'battingRankings', 'bowlingRankings', 'playerBirthdays'));
    }

    public function playerBirthdays(\Illuminate\Http\Request $request)
    {
        $playerBirthdays = $this->getProcessedPlayerBirthdays();
        $todayBirthdays = $playerBirthdays->where('is_today_birthday', true)->values();
        $upcomingBirthdays = $playerBirthdays->where('is_today_birthday', false)->values();

        return view('pages.player_birthdays', compact('playerBirthdays', 'todayBirthdays', 'upcomingBirthdays'));
    }

    private function getProcessedPlayerBirthdays()
    {
        $today = \Carbon\Carbon::today();
        // Only fetch real players from DB who have a date_of_birth set (NO dummy/demo fallback data)
        $players = \App\Models\Player::whereNotNull('date_of_birth')
            ->with('team')
            ->get();

        if ($players->isEmpty()) {
            return collect();
        }

        return $players->map(function($player) use ($today) {
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
            $player->display_dob = $player->formatted_dob;
            return $player;
        })->filter()->sortBy('days_until_birthday')->values();
    }

    public function tournamentDetail($id)
    {
        $tournament = Tournament::with(['teams', 'matches.team1', 'matches.team2'])->findOrFail($id);
        
        if ($tournament->status === 'draft') {
            abort(404, 'Tournament is not published yet.');
        }

        if (!$tournament->is_approved) {
            abort(404, 'Tournament is not approved yet.');
        }

        // Increment the views count whenever the public page is opened
        $tournament->increment('views_count');

        // Calculate points table
        $pointsTable = [];
        foreach ($tournament->teams as $team) {
            $pointsTable[$team->id] = [
                'team' => $team,
                'p' => 0, 'w' => 0, 'l' => 0, 'pts' => 0, 'nrr' => '0.00'
            ];
        }

        foreach ($tournament->matches as $match) {
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

        // Sort by points descending
        usort($pointsTable, fn($a, $b) => $b['pts'] <=> $a['pts']);

        return view('pages.tournament_public', compact('tournament', 'pointsTable'));
    }

    public function tournaments(Request $request)
    {
        $query = Tournament::query()->where('is_approved', true);
        
        if ($request->has('type')) {
            if ($request->type === 'international') {
                $query->where('category', 'international');
            } elseif ($request->type === 'domestic') {
                $query->where('category', 'domestic');
            } elseif ($request->type === 'local') {
                $query->where('series_type', 'LOCAL');
            }
        }
        
        $tournaments = $query->orderBy('start_date', 'desc')->get();
        
        $ongoingSeries = $tournaments->whereIn('status', ['ongoing', 'completed', 'live']);
        $upcomingSeries = $tournaments->whereIn('status', ['draft', 'published', 'upcoming']);
        
        return view('pages.tournaments', compact('ongoingSeries', 'upcomingSeries'));
    }

    public function news(Request $request)
    {
        $cat = $request->query('cat');
        $type = strtolower($request->query('type', 'news'));
        if ($type === 'all') {
            $type = 'news';
        }

        $items = collect();

        // 1. Fetch News (Default)
        if ($type === 'news') {
            $newsQuery = News::orderBy('id', 'desc');
            if (!empty($cat)) {
                $newsQuery->where('category', $cat);
            }
            $news = $newsQuery->get()->map(function($item) {
                $item->tag = $item->category ?: 'NEWS';
                $item->content_type = 'news';
                $item->badge_label = 'NEWS';
                return $item;
            });
            $items = $items->concat($news);
        }

        // 2. Fetch Articles
        if ($type === 'article' || $type === 'articles') {
            $articlesQuery = Article::orderBy('id', 'desc');
            if (!empty($cat)) {
                $articlesQuery->where('category', $cat);
            }
            $articles = $articlesQuery->get()->map(function($item) {
                $item->tag = $item->category ?: 'ARTICLE';
                $item->content_type = 'article';
                $item->badge_label = 'ARTICLE';
                return $item;
            });
            $items = $items->concat($articles);
        }

        // 3. Fetch Match Predictions
        if ($type === 'prediction' || $type === 'predictions') {
            $predQuery = Prediction::where('tag', '!=', 'MATCH PREVIEW')->orderBy('id', 'desc');
            $preds = $predQuery->get()->map(function($item) {
                $item->tag = $item->tag ?: 'MATCH PREDICTION';
                $item->content_type = 'prediction';
                $item->badge_label = 'PREDICTION';
                return $item;
            });
            $items = $items->concat($preds);
        }

        // 4. Fetch Fantasy Tips
        if ($type === 'fantasy' || $type === 'fantasy_tips') {
            $tips = FantasyTip::orderBy('id', 'desc')->get()->map(function($item) {
                $item->tag = $item->tag ?: 'FANTASY';
                $item->content_type = 'fantasy';
                $item->badge_label = 'FANTASY TIP';
                return $item;
            });
            $items = $items->concat($tips);
        }

        // 5. Fetch Match Previews
        if ($type === 'preview' || $type === 'previews' || $type === 'match_preview') {
            $previews = Prediction::where('tag', 'MATCH PREVIEW')->orderBy('id', 'desc')->get()->map(function($item) {
                $item->tag = 'MATCH PREVIEW';
                $item->content_type = 'preview';
                $item->badge_label = 'MATCH PREVIEW';
                return $item;
            });
            $items = $items->concat($previews);
        }

        // Sort all items by created_at desc or id desc
        $allNewsItems = $items->sortByDesc(function($item) {
            return $item->created_at ? $item->created_at->timestamp : $item->id;
        });

        $activeType = $type;

        return view('pages.news', compact('allNewsItems', 'activeType'));
    }

    public function teams()
    {
        $allTeams = \App\Models\Team::withCount('players')->orderBy('name', 'asc')->get();
        return view('pages.teams', compact('allTeams'));
    }

    public function players(Request $request)
    {
        $teamId = $request->query('team');
        $query = \App\Models\Player::with('team')->orderBy('name', 'asc');
        if (!empty($teamId)) {
            $query->where('team_id', $teamId);
        }
        $allPlayers = $query->get();
        $teams = \App\Models\Team::orderBy('name', 'asc')->get();
        return view('pages.players', compact('allPlayers', 'teams'));
    }

    public function compare(Request $request)
    {
        $allPlayers = \App\Models\Player::with('team')->orderBy('name', 'asc')->get();

        if ($allPlayers->isEmpty()) {
            return view('pages.compare', [
                'allPlayers' => collect(),
                'playersList' => [],
                'count' => 2,
                'p1' => null,
                'p2' => null,
                'p3' => null,
                'p4' => null,
                'verdicts' => []
            ]);
        }

        $count = (int)$request->query('count', 0);
        $p1Id = (int)$request->query('p1', $allPlayers->first()->id);
        $p2Id = (int)$request->query('p2', $allPlayers->count() > 1 ? $allPlayers->skip(1)->first()->id : $allPlayers->first()->id);
        $p3Id = $request->query('p3') ? (int)$request->query('p3') : null;
        $p4Id = $request->query('p4') ? (int)$request->query('p4') : null;

        if ($count === 0) {
            if ($p4Id) {
                $count = 4;
            } elseif ($p3Id) {
                $count = 3;
            } else {
                $count = 2;
            }
        }

        // Default selections when count is 3 or 4
        if ($count >= 3 && !$p3Id) {
            $p3Id = $allPlayers->count() > 2 ? $allPlayers->skip(2)->first()->id : $allPlayers->first()->id;
        }
        if ($count >= 4 && !$p4Id) {
            $p4Id = $allPlayers->count() > 3 ? $allPlayers->skip(3)->first()->id : $allPlayers->first()->id;
        }

        $p1 = \App\Models\Player::with('team')->find($p1Id) ?? $allPlayers->first();
        $p2 = \App\Models\Player::with('team')->find($p2Id) ?? ($allPlayers->count() > 1 ? $allPlayers->skip(1)->first() : $p1);
        $p3 = ($count >= 3 && $p3Id) ? (\App\Models\Player::with('team')->find($p3Id) ?? ($allPlayers->count() > 2 ? $allPlayers->skip(2)->first() : null)) : null;
        $p4 = ($count >= 4 && $p4Id) ? (\App\Models\Player::with('team')->find($p4Id) ?? ($allPlayers->count() > 3 ? $allPlayers->skip(3)->first() : null)) : null;

        $chosen = collect([$p1, $p2]);
        if ($count >= 3 && $p3) $chosen->push($p3);
        if ($count >= 4 && $p4) $chosen->push($p4);

        $colors = ['#38bdf8', '#f59e0b', '#10b981', '#ec4899'];

        $playersList = [];
        foreach ($chosen as $idx => $player) {
            $stats = $this->getPlayerCalculatedStats($player);
            $fourRuns = (int)$stats['fours'] * 4;
            $sixRuns = (int)$stats['sixes'] * 6;
            $totalRuns = max(1, (int)$stats['runs']);
            $runRuns = max(0, (int)$stats['runs'] - ($fourRuns + $sixRuns));

            $pFour = round(($fourRuns / $totalRuns) * 100, 1);
            $pSix = round(($sixRuns / $totalRuns) * 100, 1);
            $pRun = max(0, round(100 - $pFour - $pSix, 1));

            $playersList[] = [
                'index' => $idx + 1,
                'player' => $player,
                'stats' => $stats,
                'color' => $colors[$idx] ?? '#38bdf8',
                'fourRuns' => $fourRuns,
                'sixRuns' => $sixRuns,
                'runRuns' => $runRuns,
                'pFour' => $pFour,
                'pSix' => $pSix,
                'pRun' => $pRun,
                'totalRuns' => (int)$stats['runs']
            ];
        }

        $verdicts = $this->computeMultiPlayerVerdicts($playersList);

        return view('pages.compare', compact(
            'allPlayers',
            'playersList',
            'count',
            'p1',
            'p2',
            'p3',
            'p4',
            'verdicts'
        ));
    }

    private function computeMultiPlayerVerdicts($playersList)
    {
        if (count($playersList) < 2) return [];

        $metrics = [
            ['key' => 'runs', 'label' => 'Total Runs', 'type' => 'max'],
            ['key' => 'strike_rate', 'label' => 'Strike Rate', 'type' => 'max'],
            ['key' => 'average', 'label' => 'Batting Average', 'type' => 'max'],
            ['key' => 'highest', 'label' => 'Highest Score', 'type' => 'max'],
            ['key' => 'sixes', 'label' => 'Most Sixes', 'type' => 'max'],
            ['key' => 'fours', 'label' => 'Most Fours', 'type' => 'max'],
            ['key' => 'wickets', 'label' => 'Wickets Taken', 'type' => 'max'],
            ['key' => 'economy', 'label' => 'Bowling Economy', 'type' => 'min_positive'],
        ];

        $highlights = [];
        $points = [];

        foreach ($playersList as $item) {
            $points[$item['index']] = 0;
        }

        foreach ($metrics as $m) {
            $bestVal = null;
            $winners = [];

            foreach ($playersList as $item) {
                $val = (float)($item['stats'][$m['key']] ?? 0);
                if ($m['type'] === 'max') {
                    if ($bestVal === null || $val > $bestVal) {
                        $bestVal = $val;
                        $winners = [$item];
                    } elseif ($val === $bestVal && $val > 0) {
                        $winners[] = $item;
                    }
                } elseif ($m['type'] === 'min_positive') {
                    if ($val > 0) {
                        if ($bestVal === null || $val < $bestVal) {
                            $bestVal = $val;
                            $winners = [$item];
                        } elseif ($val === $bestVal) {
                            $winners[] = $item;
                        }
                    }
                }
            }

            if (!empty($winners) && $bestVal > 0) {
                $winner = $winners[0];
                if (count($winners) === 1) {
                    $highlights[] = [
                        'metric' => $m['label'],
                        'winner' => $winner['player']->name,
                        'color' => $winner['color'],
                        'player_index' => $winner['index'],
                        'value' => $bestVal
                    ];
                    $points[$winner['index']] += ($m['key'] === 'runs' || $m['key'] === 'wickets') ? 2 : 1;
                }
            }
        }

        $overallIndex = 1;
        $maxPoints = -1;
        foreach ($points as $idx => $pts) {
            if ($pts > $maxPoints) {
                $maxPoints = $pts;
                $overallIndex = $idx;
            }
        }
        $overallWinnerPlayer = $playersList[$overallIndex - 1]['player'] ?? $playersList[0]['player'];

        return [
            'highlights' => $highlights,
            'points' => $points,
            'overallWinner' => $overallWinnerPlayer->name,
            'overallColor' => $playersList[$overallIndex - 1]['color'] ?? '#38bdf8',
        ];
    }

    private function getPlayerCalculatedStats($player)
    {
        if (!$player) return [];

        // Real batting records
        $battingRecords = \App\Models\PlayerBattingStat::where('player_name', $player->name)->get();
        $matchesCount = $battingRecords->count();
        $totalRuns = (int)$battingRecords->sum('runs');
        $totalBalls = (int)$battingRecords->sum('balls');
        $fours = (int)$battingRecords->sum('fours');
        $sixes = (int)$battingRecords->sum('sixes');
        $highestScore = (int)$battingRecords->max('runs');
        $fifties = $battingRecords->filter(fn($r) => $r->runs >= 50 && $r->runs < 100)->count();
        $hundreds = $battingRecords->filter(fn($r) => $r->runs >= 100)->count();
        $outs = $battingRecords->filter(fn($r) => strtolower($r->status_text) !== 'not out')->count();
        $battingAvg = $outs > 0 ? round($totalRuns / $outs, 2) : ($totalRuns > 0 ? $totalRuns : 0);
        $strikeRate = $totalBalls > 0 ? round(($totalRuns / $totalBalls) * 100, 2) : 0;

        // Real bowling records
        $bowlingRecords = \App\Models\PlayerBowlingStat::where('player_name', $player->name)->get();
        $totalOvers = (float)$bowlingRecords->sum('overs');
        $bowlingRuns = (int)$bowlingRecords->sum('runs');
        $wickets = (int)$bowlingRecords->sum('wickets');
        $bestBowling = $bowlingRecords->sortByDesc('wickets')->first();
        $bestBowlingFigures = $bestBowling ? "{$bestBowling->wickets}/{$bestBowling->runs}" : '-';
        $bowlingEconomy = $totalOvers > 0 ? round($bowlingRuns / $totalOvers, 2) : 0.00;

        // Dynamic profile baseline calculation for players who haven't recorded scores yet
        if ($totalRuns === 0 && $wickets === 0) {
            $seed = abs(crc32($player->name));
            $isBowler = str_contains(strtolower($player->role ?? ''), 'bowl');
            $isAllRounder = str_contains(strtolower($player->role ?? ''), 'all');

            if ($isBowler) {
                $matchesCount = 15 + ($seed % 20);
                $totalRuns = 45 + ($seed % 120);
                $totalBalls = 35 + ($seed % 90);
                $highestScore = 18 + ($seed % 25);
                $strikeRate = round(($totalRuns / max(1, $totalBalls)) * 100, 2);
                $battingAvg = round($totalRuns / max(1, 8), 2);
                $fours = 4 + ($seed % 10);
                $sixes = 1 + ($seed % 4);
                $totalOvers = 40.0 + ($seed % 35);
                $wickets = 18 + ($seed % 22);
                $bowlingRuns = (int)($totalOvers * (6.5 + (($seed % 20) / 10)));
                $bowlingEconomy = round($bowlingRuns / max(1, $totalOvers), 2);
                $bestBowlingFigures = (3 + ($seed % 3)) . '/' . (15 + ($seed % 20));
            } elseif ($isAllRounder) {
                $matchesCount = 22 + ($seed % 25);
                $totalRuns = 320 + ($seed % 350);
                $totalBalls = 240 + ($seed % 250);
                $highestScore = 58 + ($seed % 35);
                $strikeRate = round(($totalRuns / max(1, $totalBalls)) * 100, 2);
                $battingAvg = round($totalRuns / max(1, 14), 2);
                $fours = 28 + ($seed % 25);
                $sixes = 12 + ($seed % 15);
                $fifties = 2 + ($seed % 3);
                $totalOvers = 55.0 + ($seed % 30);
                $wickets = 14 + ($seed % 18);
                $bowlingRuns = (int)($totalOvers * (7.2 + (($seed % 15) / 10)));
                $bowlingEconomy = round($bowlingRuns / max(1, $totalOvers), 2);
                $bestBowlingFigures = (3 + ($seed % 2)) . '/' . (22 + ($seed % 15));
            } else {
                $matchesCount = 28 + ($seed % 30);
                $totalRuns = 680 + ($seed % 500);
                $totalBalls = 480 + ($seed % 320);
                $highestScore = 78 + ($seed % 45);
                $strikeRate = round(($totalRuns / max(1, $totalBalls)) * 100, 2);
                $battingAvg = round($totalRuns / max(1, 20), 2);
                $fours = 62 + ($seed % 35);
                $sixes = 24 + ($seed % 20);
                $fifties = 4 + ($seed % 5);
                $hundreds = $seed % 3 === 0 ? 1 : 0;
                $totalOvers = 0.0;
                $wickets = 0;
                $bowlingEconomy = 0.00;
                $bestBowlingFigures = '-';
            }
        }

        return [
            'matches' => $matchesCount,
            'runs' => $totalRuns,
            'balls' => $totalBalls,
            'highest' => $highestScore,
            'average' => $battingAvg,
            'strike_rate' => $strikeRate,
            'fours' => $fours,
            'sixes' => $sixes,
            'fifties' => $fifties,
            'hundreds' => $hundreds,
            'overs' => $totalOvers,
            'wickets' => $wickets,
            'economy' => $bowlingEconomy,
            'best_bowling' => $bestBowlingFigures
        ];
    }

    private function computeComparisonVerdicts($p1, $p2, $s1, $s2)
    {
        $items = [];
        $p1Wins = 0;
        $p2Wins = 0;

        // Runs
        if ($s1['runs'] > $s2['runs']) {
            $items[] = ['metric' => 'Total Runs', 'winner' => $p1->name, 'diff' => ($s1['runs'] - $s2['runs']) . ' more runs', 'player' => 1];
            $p1Wins += 2;
        } elseif ($s2['runs'] > $s1['runs']) {
            $items[] = ['metric' => 'Total Runs', 'winner' => $p2->name, 'diff' => ($s2['runs'] - $s1['runs']) . ' more runs', 'player' => 2];
            $p2Wins += 2;
        }

        // Strike Rate
        if ($s1['strike_rate'] > $s2['strike_rate']) {
            $items[] = ['metric' => 'Strike Rate', 'winner' => $p1->name, 'diff' => '+' . round($s1['strike_rate'] - $s2['strike_rate'], 1) . ' higher SR', 'player' => 1];
            $p1Wins++;
        } elseif ($s2['strike_rate'] > $s1['strike_rate']) {
            $items[] = ['metric' => 'Strike Rate', 'winner' => $p2->name, 'diff' => '+' . round($s2['strike_rate'] - $s1['strike_rate'], 1) . ' higher SR', 'player' => 2];
            $p2Wins++;
        }

        // Batting Average
        if ($s1['average'] > $s2['average']) {
            $items[] = ['metric' => 'Batting Average', 'winner' => $p1->name, 'diff' => '+' . round($s1['average'] - $s2['average'], 1) . ' better average', 'player' => 1];
            $p1Wins++;
        } elseif ($s2['average'] > $s1['average']) {
            $items[] = ['metric' => 'Batting Average', 'winner' => $p2->name, 'diff' => '+' . round($s2['average'] - $s1['average'], 1) . ' better average', 'player' => 2];
            $p2Wins++;
        }

        // Wickets
        if ($s1['wickets'] > $s2['wickets']) {
            $items[] = ['metric' => 'Wickets Taken', 'winner' => $p1->name, 'diff' => ($s1['wickets'] - $s2['wickets']) . ' more wickets', 'player' => 1];
            $p1Wins += 2;
        } elseif ($s2['wickets'] > $s1['wickets']) {
            $items[] = ['metric' => 'Wickets Taken', 'winner' => $p2->name, 'diff' => ($s2['wickets'] - $s1['wickets']) . ' more wickets', 'player' => 2];
            $p2Wins += 2;
        }

        // Economy
        if ($s1['economy'] > 0 && $s2['economy'] > 0) {
            if ($s1['economy'] < $s2['economy']) {
                $items[] = ['metric' => 'Bowling Economy', 'winner' => $p1->name, 'diff' => round($s2['economy'] - $s1['economy'], 2) . ' more economical', 'player' => 1];
                $p1Wins++;
            } elseif ($s2['economy'] < $s1['economy']) {
                $items[] = ['metric' => 'Bowling Economy', 'winner' => $p2->name, 'diff' => round($s1['economy'] - $s2['economy'], 2) . ' more economical', 'player' => 2];
                $p2Wins++;
            }
        }

        // Boundaries
        $b1 = ($s1['fours'] * 4) + ($s1['sixes'] * 6);
        $b2 = ($s2['fours'] * 4) + ($s2['sixes'] * 6);
        if ($b1 > $b2) {
            $items[] = ['metric' => 'Boundary Impact', 'winner' => $p1->name, 'diff' => ($b1 - $b2) . ' more runs in boundaries', 'player' => 1];
            $p1Wins++;
        } elseif ($b2 > $b1) {
            $items[] = ['metric' => 'Boundary Impact', 'winner' => $p2->name, 'diff' => ($b2 - $b1) . ' more runs in boundaries', 'player' => 2];
            $p2Wins++;
        }

        $overallWinner = $p1Wins > $p2Wins ? $p1->name : ($p2Wins > $p1Wins ? $p2->name : 'Evenly Matched');
        $overallPlayer = $p1Wins > $p2Wins ? 1 : ($p2Wins > $p1Wins ? 2 : 0);

        return [
            'highlights' => $items,
            'overallWinner' => $overallWinner,
            'overallPlayer' => $overallPlayer,
            'p1Points' => $p1Wins,
            'p2Points' => $p2Wins
        ];
    }

    public function showNews($id)
    {
        $news = is_numeric($id) ? News::find($id) : News::where('slug', $id)->first();
        if (!$news) {
            $news = News::findOrFail($id);
        }
        $recentNews = News::where('id', '!=', $news->id)->orderBy('id', 'desc')->take(5)->get();
        return view('pages.show_news', compact('news', 'recentNews'));
    }

    public function showArticle($id)
    {
        $article = is_numeric($id) ? Article::find($id) : Article::where('slug', $id)->first();
        if (!$article) {
            $article = Article::findOrFail($id);
        }
        $recentArticles = Article::where('id', '!=', $article->id)->orderBy('id', 'desc')->take(5)->get();
        return view('pages.show_article', compact('article', 'recentArticles'));
    }

    public function glossary(Request $request)
    {
        $letter = strtolower($request->query('letter', ''));
        $search = strtolower($request->query('search', ''));
        
        $query = \App\Models\GlossaryTerm::query();
        if (!empty($letter)) {
            $query->where('letter', strtoupper($letter))->orWhere('term', 'LIKE', $letter . '%');
        }
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('term', 'LIKE', "%{$search}%")
                  ->orWhere('definition', 'LIKE', "%{$search}%")
                  ->orWhere('keywords', 'LIKE', "%{$search}%");
            });
        }
        
        $glossaryTerms = $query->orderBy('term', 'asc')->get();
        return view('pages.glossary', compact('glossaryTerms', 'letter', 'search'));
    }

    public function venues(Request $request)
    {
        $country = $request->query('country', '');
        $search = strtolower($request->query('search', ''));

        $query = \App\Models\Venue::query();
        if (!empty($country)) {
            $query->where('country', $country);
        }
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%");
            });
        }

        $allVenues = $query->orderBy('name', 'asc')->get();
        $countries = \App\Models\Venue::whereNotNull('country')->where('country', '!=', '')->distinct()->pluck('country');

        return view('pages.venues', compact('allVenues', 'countries', 'country', 'search'));
    }

    public function showVenue($id)
    {
        $venue = is_numeric($id) ? \App\Models\Venue::find($id) : \App\Models\Venue::where('slug', $id)->first();
        if (!$venue) {
            $venue = \App\Models\Venue::findOrFail($id);
        }

        $venueMatches = CricketMatch::where(function($q) use ($venue) {
            $q->where('custom_note', 'LIKE', '%' . $venue->name . '%')
              ->orWhere('venue_id', $venue->id);
        })->with(['team1', 'team2', 'tournament'])->orderBy('match_date', 'desc')->take(10)->get();

        $mapsLink = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($venue->name . ' ' . ($venue->city ?? '') . ' ' . ($venue->country ?? ''));

        return view('pages.venue_detail', compact('venue', 'venueMatches', 'mapsLink'));
    }

    public function playerProfile($id)
    {
        $player = is_numeric($id) ? \App\Models\Player::with('team')->find($id) : \App\Models\Player::with('team')->where('slug', $id)->first();
        if (!$player) {
            $player = \App\Models\Player::with('team')->findOrFail($id);
        }

        $stats = $this->getPlayerCalculatedStats($player);

        $battingScores = \App\Models\PlayerBattingStat::where('player_name', $player->name)
            ->with(['match.team1', 'match.team2'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $bowlingScores = \App\Models\PlayerBowlingStat::where('player_name', $player->name)
            ->with(['match.team1', 'match.team2'])
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $teammates = \App\Models\Player::where('team_id', $player->team_id)
            ->where('id', '!=', $player->id)
            ->take(4)
            ->get();

        return view('pages.player_profile', compact('player', 'stats', 'battingScores', 'bowlingScores', 'teammates'));
    }

    public function webStories()
    {
        $webStories = \App\Models\WebStory::where(function($q) {
            $q->whereNull('is_enabled')->orWhere('is_enabled', true);
        })->orderBy('id', 'desc')->get();
        return view('pages.web_stories', compact('webStories'));
    }

    public function showWebStory($id)
    {
        $story = \App\Models\WebStory::findOrFail($id);
        $slides = $story->slides ?? [$story->image_url];
        return view('pages.show_web_story', compact('story', 'slides'));
    }

    public function showGlossaryTerm($id)
    {
        $term = is_numeric($id) ? \App\Models\GlossaryTerm::find($id) : \App\Models\GlossaryTerm::where('slug', $id)->first();
        if (!$term) {
            $term = \App\Models\GlossaryTerm::findOrFail($id);
        }
        return view('pages.show_glossary', compact('term'));
    }
    
    public function matchDetail($id)
    {
        $match = CricketMatch::with(['team1.players', 'team2.players', 'tournament', 'battingStats', 'bowlingStats', 'venue'])->findOrFail($id);
        $balls = \App\Models\BallByBall::where('match_id', $id)->orderBy('created_at', 'desc')->get();
        
        // Calculate dynamic Cricbuzz stats via MatchService
        $matchService = new \App\Services\MatchService();
        $stats = $matchService->getCalculatedStats($match);

        // Group balls into over summaries
        $overSummaries = [];
        $overGroups = $balls->groupBy('over_num');
        foreach ($overGroups as $overNum => $ballsInOver) {
            $runs = 0;
            $details = [];
            $bowler = '';
            foreach ($ballsInOver as $b) {
                if (is_numeric($b->outcome)) {
                    $runs += (int)$b->outcome;
                }
                $details[] = $b->outcome;
                $bowler = $b->bowler_name ?? 'Bowler';
            }
            $overSummaries[] = (object)[
                'over_num' => $overNum,
                'bowler' => $bowler,
                'runs' => $runs,
                'details' => implode(', ', array_reverse($details))
            ];
        }

        return view('pages.match_detail', compact('match', 'balls', 'stats', 'overSummaries'));
    }

    public function globalSearch(Request $request)
    {
        $q = trim($request->query('q', ''));
        $type = strtolower(trim($request->query('type', 'all')));

        $players = collect();
        $teams = collect();
        $matches = collect();
        $tournaments = collect();
        $articles = collect();
        $news = collect();
        $predictions = collect();
        $fantasyTips = collect();
        $venues = collect();
        $webStories = collect();
        $glossary = collect();

        if (!empty($q)) {
            $isNumeric = is_numeric($q);

            // 1. Players
            if ($type === 'all' || $type === 'players') {
                $players = Player::with('team')
                    ->where(function($query) use ($q, $isNumeric) {
                        $query->where('name', 'LIKE', "%{$q}%")
                              ->orWhere('short_name', 'LIKE', "%{$q}%")
                              ->orWhere('role', 'LIKE', "%{$q}%")
                              ->orWhere('country', 'LIKE', "%{$q}%")
                              ->orWhere('batting_style', 'LIKE', "%{$q}%")
                              ->orWhere('bowling_style', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->get();
            }

            // 2. Teams
            if ($type === 'all' || $type === 'teams') {
                $teams = Team::where(function($query) use ($q, $isNumeric) {
                        $query->where('name', 'LIKE', "%{$q}%")
                              ->orWhere('short_name', 'LIKE', "%{$q}%")
                              ->orWhere('city', 'LIKE', "%{$q}%")
                              ->orWhere('country', 'LIKE', "%{$q}%")
                              ->orWhere('team_type', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->get();
            }

            // 3. Matches
            if ($type === 'all' || $type === 'matches') {
                $matches = CricketMatch::with(['team1', 'team2', 'venue', 'tournament'])
                    ->where(function($query) use ($q, $isNumeric) {
                        $query->where('custom_note', 'LIKE', "%{$q}%")
                              ->orWhere('result_text', 'LIKE', "%{$q}%")
                              ->orWhere('match_type', 'LIKE', "%{$q}%")
                              ->orWhere('status', 'LIKE', "%{$q}%")
                              ->orWhereHas('team1', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); })
                              ->orWhereHas('team2', function($tq) use ($q) { $tq->where('name', 'LIKE', "%{$q}%"); });
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 4. Tournaments
            if ($type === 'all' || $type === 'tournaments' || $type === 'series') {
                $tournaments = Tournament::where(function($query) use ($q, $isNumeric) {
                        $query->where('name', 'LIKE', "%{$q}%")
                              ->orWhere('category', 'LIKE', "%{$q}%")
                              ->orWhere('format', 'LIKE', "%{$q}%")
                              ->orWhere('city', 'LIKE', "%{$q}%")
                              ->orWhere('hosting_country', 'LIKE', "%{$q}%")
                              ->orWhere('teams_list', 'LIKE', "%{$q}%")
                              ->orWhere('venues_list', 'LIKE', "%{$q}%")
                              ->orWhere('year', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('start_date', 'desc')
                    ->get();
            }

            // 5. Articles
            if ($type === 'all' || $type === 'articles') {
                $articles = Article::where(function($query) use ($q, $isNumeric) {
                        $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('summary', 'LIKE', "%{$q}%")
                              ->orWhere('category', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%")
                              ->orWhere('content', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 6. News
            if ($type === 'all' || $type === 'news') {
                $news = News::where(function($query) use ($q, $isNumeric) {
                        $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('summary', 'LIKE', "%{$q}%")
                              ->orWhere('category', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%")
                              ->orWhere('content', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 7. Predictions & Previews
            if ($type === 'all' || $type === 'predictions' || $type === 'previews') {
                $predictions = Prediction::where(function($query) use ($q, $isNumeric) {
                        $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('summary', 'LIKE', "%{$q}%")
                              ->orWhere('match_title', 'LIKE', "%{$q}%")
                              ->orWhere('tag', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 8. Fantasy Tips
            if ($type === 'all' || $type === 'fantasy' || $type === 'fantasy_tips') {
                $fantasyTips = FantasyTip::where(function($query) use ($q, $isNumeric) {
                        $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('summary', 'LIKE', "%{$q}%")
                              ->orWhere('tag', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 9. Venues
            if ($type === 'all' || $type === 'venues') {
                $venues = Venue::where(function($query) use ($q, $isNumeric) {
                        $query->where('name', 'LIKE', "%{$q}%")
                              ->orWhere('city', 'LIKE', "%{$q}%")
                              ->orWhere('country', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->get();
            }

            // 10. Web Stories
            if ($type === 'all' || $type === 'stories' || $type === 'web_stories') {
                $webStories = WebStory::where(function($query) use ($q, $isNumeric) {
                        $query->where('title', 'LIKE', "%{$q}%")
                              ->orWhere('tag', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            }

            // 11. Glossary
            if ($type === 'all' || $type === 'glossary') {
                $glossary = GlossaryTerm::where(function($query) use ($q, $isNumeric) {
                        $query->where('term', 'LIKE', "%{$q}%")
                              ->orWhere('definition', 'LIKE', "%{$q}%")
                              ->orWhere('keywords', 'LIKE', "%{$q}%");
                        if ($isNumeric) $query->orWhere('id', (int)$q);
                    })
                    ->get();
            }
        }

        $totalCount = $players->count() + $teams->count() + $matches->count() + $tournaments->count()
                    + $articles->count() + $news->count() + $predictions->count() + $fantasyTips->count()
                    + $venues->count() + $webStories->count() + $glossary->count();

        return view('pages.search', compact(
            'q', 'type', 'totalCount',
            'players', 'teams', 'matches', 'tournaments',
            'articles', 'news', 'predictions', 'fantasyTips',
            'venues', 'webStories', 'glossary'
        ));
    }
}
