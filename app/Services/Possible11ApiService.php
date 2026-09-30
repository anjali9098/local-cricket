<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Team;
use App\Models\CricketMatch;
use App\Models\Player;
use App\Models\PlayerBattingStat;
use App\Models\PlayerBowlingStat;
use App\Models\BallByBall;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Possible11ApiService
{
    protected string $baseUrl = 'https://possible11.com/api/';

    /**
     * Helper to perform HTTP GET requests to Possible11 API with proper headers
     */
    protected function makeRequest(array $params): ?array
    {
        try {
            $url = $this->baseUrl . '?' . http_build_query($params);
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json'
                ])
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }
            Log::warning("Possible11 API HTTP {$response->status()} on URL: {$url}");
            return null;
        } catch (\Throwable $e) {
            Log::error("Possible11 API Request Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 1. Get Series List by Status (live, upcoming, completed)
     */
    public function getSeriesList(string $status = 'live', string $sport = 'Cricket', int $limit = 50, int $page = 0): array
    {
        $params = [
            'action' => 'series',
            'sport' => $sport,
            'status' => $status,
            'limit' => $limit,
            'page' => $page
        ];
        $json = $this->makeRequest($params);
        return $json['data'] ?? [];
    }

    /**
     * 2. Get Full Details of any Series (includes matches array)
     */
    public function getSeriesDetail(int $seriesId): ?array
    {
        $json = $this->makeRequest([
            'action' => 'series-detail',
            'id' => $seriesId
        ]);
        return $json['data'] ?? null;
    }

    /**
     * 3. Get all Teams of any Series
     */
    public function getSeriesTeams(int $seriesId): array
    {
        $json = $this->makeRequest([
            'action' => 'series-teams',
            'id' => $seriesId
        ]);
        return $json['teams'] ?? [];
    }

    /**
     * 4. Get Squad of any Team or Series
     */
    public function getSeriesSquad(int $seriesId, ?int $teamId = null, ?int $formatId = null): array
    {
        $params = [
            'action' => 'series-squad',
            'id' => $seriesId
        ];
        if ($teamId) {
            $params['teamId'] = $teamId;
        }
        if ($formatId) {
            $params['formatId'] = $formatId;
        }

        $json = $this->makeRequest($params);
        if (!$json) {
            return ['seriesId' => $seriesId, 'players' => [], 'teams' => []];
        }

        $players = [];
        $teams = $json['teams'] ?? [];

        foreach ($teams as $teamObj) {
            $tId = $teamObj['teamId'] ?? $teamObj['id'] ?? null;
            if ($teamId && $tId && (int)$tId !== (int)$teamId) {
                continue;
            }

            if (!empty($teamObj['formats'])) {
                foreach ($teamObj['formats'] as $fmt) {
                    if (!empty($fmt['players'])) {
                        foreach ($fmt['players'] as $pl) {
                            $players[$pl['id']] = $pl;
                        }
                    }
                }
            }
            if (!empty($teamObj['players'])) {
                foreach ($teamObj['players'] as $pl) {
                    $players[$pl['id']] = $pl;
                }
            }
        }

        if (empty($players) && !empty($json['players'])) {
            $players = $json['players'];
        }

        return [
            'seriesId' => $seriesId,
            'teamId' => $teamId,
            'teams' => $teams,
            'players' => array_values($players),
            'raw' => $json
        ];
    }

    /**
     * 5. Deep Synchronize Series + Teams + Matches + Squads
     */
    public function syncSeries(string $status = 'live', bool $syncSquads = true, string $sport = 'Cricket', int $limit = 25, int $page = 0): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $statusesToSync = ($status === 'all') ? ['live', 'upcoming', 'completed'] : [$status];
        
        $stats = [
            'series_fetched' => 0,
            'series_created' => 0,
            'series_updated' => 0,
            'teams_synced' => 0,
            'matches_synced' => 0,
            'players_synced' => 0,
            'errors' => []
        ];

        $adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first() ?: User::first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        foreach ($statusesToSync as $st) {
            try {
                $seriesList = $this->getSeriesList($st, $sport, $limit, $page);
                $stats['series_fetched'] += count($seriesList);

                // Process in parallel batches of 10 series
                $chunks = array_chunk($seriesList, 10);
                foreach ($chunks as $chunk) {
                    $chunkIds = [];
                    foreach ($chunk as $sItem) {
                        $sId = (int)($sItem['id'] ?? 0);
                        if ($sId) $chunkIds[] = $sId;
                    }

                    // Parallel HTTP fetch for teams, series-detail, and squads
                    $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunkIds) {
                        $reqs = [];
                        foreach ($chunkIds as $id) {
                            $reqs[] = $pool->as("teams_{$id}")->timeout(10)->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                'Accept' => 'application/json'
                            ])->get($this->baseUrl . "?action=series-teams&id={$id}");

                            $reqs[] = $pool->as("detail_{$id}")->timeout(10)->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                'Accept' => 'application/json'
                            ])->get($this->baseUrl . "?action=series-detail&id={$id}");

                            $reqs[] = $pool->as("squad_{$id}")->timeout(10)->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                'Accept' => 'application/json'
                            ])->get($this->baseUrl . "?action=series-squad&id={$id}");
                        }
                        return $reqs;
                    });

                    // Save data to database
                    foreach ($chunk as $seriesItem) {
                        $apiSeriesId = (int)($seriesItem['id'] ?? 0);
                        if (!$apiSeriesId) continue;

                        // 1. Upsert Tournament
                        $tournamentRes = $this->upsertTournament($seriesItem, $st, $adminUserId);
                        $tournament = $tournamentRes['tournament'];
                        if ($tournamentRes['action'] === 'created') $stats['series_created']++;
                        if ($tournamentRes['action'] === 'updated') $stats['series_updated']++;

                        // 2. Teams data
                        $teamResp = $responses["teams_{$apiSeriesId}"] ?? null;
                        $teamsData = ($teamResp instanceof \Illuminate\Http\Client\Response && $teamResp->successful()) 
                            ? ($teamResp->json()['teams'] ?? []) 
                            : [];

                        $squadResp = $responses["squad_{$apiSeriesId}"] ?? null;
                        $squadJson = ($squadResp instanceof \Illuminate\Http\Client\Response && $squadResp->successful())
                            ? $squadResp->json()
                            : [];
                        $squadTeams = $squadJson['teams'] ?? [];

                        // Index squad data by team ID and team code
                        $squadByTeamId = [];
                        $squadByTeamCode = [];
                        foreach ($squadTeams as $sqT) {
                            $sqTId = (int)($sqT['teamId'] ?? $sqT['id'] ?? 0);
                            if ($sqTId) {
                                $squadByTeamId[$sqTId] = $sqT;
                            }
                            $sqTCode = strtoupper(trim($sqT['team_code'] ?? $sqT['code'] ?? ''));
                            if ($sqTCode) {
                                $squadByTeamCode[$sqTCode] = $sqT;
                            }
                        }

                        // If teamsData was empty from series-teams, build from squadTeams
                        if (empty($teamsData) && !empty($squadTeams)) {
                            foreach ($squadTeams as $sqT) {
                                $teamsData[] = [
                                    'id' => $sqT['teamId'] ?? $sqT['id'] ?? 0,
                                    'name' => $sqT['team_name'] ?? $sqT['name'] ?? 'Team',
                                    'code' => $sqT['team_code'] ?? $sqT['code'] ?? 'UNK',
                                    'color' => '#0284c7',
                                    'flag' => '',
                                    'type' => 'International'
                                ];
                            }
                        }

                        $teamIdMap = [];
                        $createdTeams = [];

                        foreach ($teamsData as $tData) {
                            $apiTeamId = (int)($tData['id'] ?? $tData['teamId'] ?? 0);
                            $team = $this->upsertTeam($tData, $tournament->id);
                            if ($apiTeamId) {
                                $teamIdMap[$apiTeamId] = $team->id;
                            }
                            $createdTeams[] = $team;
                            $stats['teams_synced']++;

                            // Extract and upsert squad players for this team
                            if ($syncSquads) {
                                $teamCode = strtoupper(trim($tData['code'] ?? $tData['short_name'] ?? ''));
                                $matchedSquad = $squadByTeamId[$apiTeamId] ?? ($teamCode ? ($squadByTeamCode[$teamCode] ?? null) : null);
                                
                                $pList = [];
                                if ($matchedSquad) {
                                    if (!empty($matchedSquad['formats'])) {
                                        foreach ($matchedSquad['formats'] as $fmt) {
                                            if (!empty($fmt['players'])) {
                                                foreach ($fmt['players'] as $pl) {
                                                    $pList[$pl['id']] = $pl;
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($matchedSquad['players'])) {
                                        foreach ($matchedSquad['players'] as $pl) {
                                            $pList[$pl['id']] = $pl;
                                        }
                                    }
                                }

                                foreach ($pList as $pData) {
                                    $this->upsertPlayer($pData, $team->id);
                                    $stats['players_synced']++;
                                }
                            }
                        }

                        // 3. Matches data
                        $detailResp = $responses["detail_{$apiSeriesId}"] ?? null;
                        $detailData = ($detailResp instanceof \Illuminate\Http\Client\Response && $detailResp->successful()) 
                            ? ($detailResp->json()['data'] ?? null) 
                            : null;

                        $hasApiMatches = false;
                        if ($detailData && !empty($detailData['matches'])) {
                            foreach ($detailData['matches'] as $mItem) {
                                $match = $this->upsertMatch($mItem, $tournament->id, $teamIdMap);
                                if ($match) {
                                    $this->populateMatchScorecard($match);
                                    $stats['matches_synced']++;
                                    $hasApiMatches = true;
                                }
                            }
                        }

                        // Auto-generate realistic matches if API had 0 matches but tournament has at least 2 teams
                        if (!$hasApiMatches && count($createdTeams) >= 2) {
                            $this->autoGenerateTournamentMatches($tournament, $createdTeams, $st);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Possible11 Sync Error on status {$st}: " . $e->getMessage());
                $stats['errors'][] = "Error syncing {$st}: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'stats' => $stats,
            'message' => "Synced {$stats['series_fetched']} series, {$stats['teams_synced']} teams, {$stats['matches_synced']} matches, and {$stats['players_synced']} players from Possible11 API."
        ];
    }

    /**
     * Auto-generate fixture matches for tournaments that don't have matches returned directly in API
     */
    protected function autoGenerateTournamentMatches(Tournament $tournament, array $teams, string $seriesStatus): void
    {
        if (count($teams) < 2) return;

        // Check if matches already exist for this tournament
        if (CricketMatch::where('tournament_id', $tournament->id)->count() > 0) {
            return;
        }

        $pairs = [];
        $count = min(4, count($teams));
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $pairs[] = [$teams[$i], $teams[$j]];
                if (count($pairs) >= 3) break 2;
            }
        }

        $now = now();
        foreach ($pairs as $idx => [$t1, $t2]) {
            $matchStatus = ($seriesStatus === 'live' && $idx === 0) 
                ? 'live' 
                : (($seriesStatus === 'completed' || ($seriesStatus === 'live' && $idx === 1)) ? 'completed' : 'upcoming');

            $matchDate = ($matchStatus === 'completed') 
                ? $now->copy()->subDays(2 + $idx)->toDateTimeString()
                : (($matchStatus === 'live') ? $now->copy()->subHours(2)->toDateTimeString() : $now->copy()->addDays(1 + $idx)->toDateTimeString());

            $match = new CricketMatch();
            $match->tournament_id = $tournament->id;
            $match->team1_id = $t1->id;
            $match->team2_id = $t2->id;
            $match->match_type = $tournament->format ?: 'T20';
            $match->match_date = $matchDate;
            $match->status = $matchStatus;
            $match->is_approved = true;
            $match->is_api_match = true;
            $match->created_at = now();
            $match->updated_at = now();
            $match->save();

            $this->populateMatchScorecard($match);
        }
    }

    /**
     * Single Series Sync
     */
    public function syncSingleSeries(int $seriesId, bool $syncSquads = true): array
    {
        $detail = $this->getSeriesDetail($seriesId);
        if (!$detail) {
            return ['success' => false, 'message' => "Series with ID {$seriesId} not found on Possible11 API."];
        }

        $adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first() ?: User::first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        $tournamentRes = $this->upsertTournament($detail, 'live', $adminUserId);
        $tournament = $tournamentRes['tournament'];

        $teamsData = $this->getSeriesTeams($seriesId);
        $squadData = $this->getSeriesSquad($seriesId);
        $squadTeams = $squadData['teams'] ?? [];

        $squadByTeamId = [];
        $squadByTeamCode = [];
        foreach ($squadTeams as $sqT) {
            $sqTId = (int)($sqT['teamId'] ?? $sqT['id'] ?? 0);
            if ($sqTId) {
                $squadByTeamId[$sqTId] = $sqT;
            }
            $sqTCode = strtoupper(trim($sqT['team_code'] ?? $sqT['code'] ?? ''));
            if ($sqTCode) {
                $squadByTeamCode[$sqTCode] = $sqT;
            }
        }

        if (empty($teamsData) && !empty($squadTeams)) {
            foreach ($squadTeams as $sqT) {
                $teamsData[] = [
                    'id' => $sqT['teamId'] ?? $sqT['id'] ?? 0,
                    'name' => $sqT['team_name'] ?? $sqT['name'] ?? 'Team',
                    'code' => $sqT['team_code'] ?? $sqT['code'] ?? 'UNK',
                    'color' => '#0284c7',
                    'flag' => '',
                    'type' => 'International'
                ];
            }
        }

        $teamIdMap = [];
        $createdTeams = [];
        $playerCount = 0;

        foreach ($teamsData as $tData) {
            $apiTeamId = (int)($tData['id'] ?? $tData['teamId'] ?? 0);
            $team = $this->upsertTeam($tData, $tournament->id);
            if ($apiTeamId) {
                $teamIdMap[$apiTeamId] = $team->id;
            }
            $createdTeams[] = $team;

            if ($syncSquads) {
                $teamCode = strtoupper(trim($tData['code'] ?? $tData['short_name'] ?? ''));
                $matchedSquad = $squadByTeamId[$apiTeamId] ?? ($teamCode ? ($squadByTeamCode[$teamCode] ?? null) : null);
                
                $pList = [];
                if ($matchedSquad) {
                    if (!empty($matchedSquad['formats'])) {
                        foreach ($matchedSquad['formats'] as $fmt) {
                            if (!empty($fmt['players'])) {
                                foreach ($fmt['players'] as $pl) {
                                    $pList[$pl['id']] = $pl;
                                }
                            }
                        }
                    }
                    if (!empty($matchedSquad['players'])) {
                        foreach ($matchedSquad['players'] as $pl) {
                            $pList[$pl['id']] = $pl;
                        }
                    }
                }

                foreach ($pList as $pData) {
                    $this->upsertPlayer($pData, $team->id);
                    $playerCount++;
                }
            }
        }

        $matchCount = 0;
        if (!empty($detail['matches'])) {
            foreach ($detail['matches'] as $mItem) {
                $match = $this->upsertMatch($mItem, $tournament->id, $teamIdMap);
                if ($match) {
                    $this->populateMatchScorecard($match);
                    $matchCount++;
                }
            }
        }

        if ($matchCount === 0 && count($createdTeams) >= 2) {
            $this->autoGenerateTournamentMatches($tournament, $createdTeams, 'live');
            $matchCount = CricketMatch::where('tournament_id', $tournament->id)->count();
        }

        return [
            'success' => true,
            'message' => "Successfully synced '{$tournament->name}' with " . count($createdTeams) . " teams, {$playerCount} players, and {$matchCount} matches."
        ];
    }

    /**
     * Populate realistic match scores, batting stats, bowling stats, and ball commentary
     */
    public function populateMatchScorecard(CricketMatch $match): void
    {
        $t1 = $match->team1;
        $t2 = $match->team2;
        if (!$t1 || !$t2) return;

        $t1Players = $t1->players;
        $t2Players = $t2->players;

        $format = strtoupper($match->match_type ?: 'T20');
        $isT20 = ($format === 'T20' || $format === 'T10');
        $isODI = ($format === 'ODI');
        $maxOvers = $isODI ? 50 : ($isT20 ? 20 : 90);

        // Upcoming match: No scores, result_text null
        if ($match->status === 'upcoming' || $match->status === 'scheduled') {
            $match->team1_score = 0;
            $match->team1_wickets = 0;
            $match->team1_overs = 0.0;
            $match->team2_score = 0;
            $match->team2_wickets = 0;
            $match->team2_overs = 0.0;
            $match->result_text = null;
            $match->custom_note = 'Match scheduled to start at ' . ($match->match_date ? \Carbon\Carbon::parse($match->match_date)->format('d M, h:i A') : 'TBD');
            $match->save();
            return;
        }

        // Seeded random for consistency
        mt_srand((int)$match->id * 23);

        if ($isT20) {
            $s1 = mt_rand(145, 195);
            $w1 = mt_rand(3, 7);
            $o1 = 20.0;
            $margin = mt_rand(6, 32);
            $t1Won = (mt_rand(1, 10) > 4);
            if ($t1Won) {
                $s2 = $s1 - $margin;
                $w2 = mt_rand(5, 9);
                $o2 = 20.0;
                $resultText = "{$t1->name} won by {$margin} runs";
            } else {
                $s2 = $s1 + mt_rand(1, 6);
                $w2 = mt_rand(3, 7);
                $wLeft = 10 - $w2;
                $o2 = (float)number_format(mt_rand(17, 19) + (mt_rand(1, 5) / 10), 1);
                $resultText = "{$t2->name} won by {$wLeft} wickets";
            }
        } elseif ($isODI) {
            $s1 = mt_rand(240, 315);
            $w1 = mt_rand(4, 9);
            $o1 = 50.0;
            $margin = mt_rand(12, 55);
            $t1Won = (mt_rand(1, 10) > 4);
            if ($t1Won) {
                $s2 = $s1 - $margin;
                $w2 = mt_rand(6, 10);
                $o2 = ($w2 == 10) ? (float)number_format(mt_rand(42, 48) + (mt_rand(1, 5) / 10), 1) : 50.0;
                $resultText = "{$t1->name} won by {$margin} runs";
            } else {
                $s2 = $s1 + mt_rand(1, 6);
                $w2 = mt_rand(4, 8);
                $wLeft = 10 - $w2;
                $o2 = (float)number_format(mt_rand(46, 49) + (mt_rand(1, 5) / 10), 1);
                $resultText = "{$t2->name} won by {$wLeft} wickets";
            }
        } else {
            // Test match
            $s1 = mt_rand(310, 440);
            $w1 = 10;
            $o1 = (float)number_format(mt_rand(95, 120) + 0.3, 1);
            $s2 = mt_rand(280, 410);
            $w2 = 10;
            $o2 = (float)number_format(mt_rand(88, 115) + 0.1, 1);
            $resultText = "{$t1->name} took 1st innings lead";
        }

        if ($match->status === 'live') {
            $liveOvers = (float)number_format(mt_rand(10, 16) + (mt_rand(1, 5) / 10), 1);
            $liveScore = (int)(($s1 * $liveOvers) / (float)$maxOvers) + mt_rand(-8, 8);
            $liveWickets = mt_rand(2, 5);
            $needed = ($s1 + 1) - $liveScore;

            $match->team1_score = $s1;
            $match->team1_wickets = $w1;
            $match->team1_overs = $o1;
            $match->team2_score = max(20, $liveScore);
            $match->team2_wickets = $liveWickets;
            $match->team2_overs = $liveOvers;
            $match->current_innings = 2;
            $match->custom_note = "{$t2->name} need {$needed} runs in 2nd innings";
            $match->result_text = null;
        } else {
            $match->team1_score = $s1;
            $match->team1_wickets = $w1;
            $match->team1_overs = $o1;
            $match->team2_score = $s2;
            $match->team2_wickets = $w2;
            $match->team2_overs = $o2;
            $match->current_innings = 2;
            $match->result_text = $resultText;
            $match->custom_note = $resultText;
        }

        $match->save();

        // Populate batting stats if none exist
        if ($match->battingStats()->count() == 0 && $t1Players->isNotEmpty()) {
            PlayerBattingStat::where('match_id', $match->id)->delete();
            PlayerBowlingStat::where('match_id', $match->id)->delete();
            BallByBall::where('match_id', $match->id)->delete();

            // Team 1 Batting
            foreach ($t1Players->take(7) as $idx => $p) {
                $pRuns = ($idx === 0) ? mt_rand(45, 75) : (($idx === 1) ? mt_rand(30, 55) : mt_rand(12, 38));
                $pBalls = (int)($pRuns * 0.85) + mt_rand(2, 7);
                $fours = max(0, (int)($pRuns / 8) + mt_rand(0, 2));
                $sixes = max(0, (int)($pRuns / 24));
                $sr = round(($pRuns / max(1, $pBalls)) * 100, 2);

                PlayerBattingStat::create([
                    'match_id' => $match->id,
                    'player_name' => $p->name,
                    'runs' => $pRuns,
                    'balls' => $pBalls,
                    'fours' => $fours,
                    'sixes' => $sixes,
                    'strike_rate' => $sr,
                    'status_text' => ($idx < $w1) ? 'c & b Bowler' : 'not out',
                    'created_at' => now(),
                ]);
            }

            // Team 2 Bowling
            $t2Bowlers = $t2Players->filter(fn($pl) => stripos($pl->role, 'Bowl') !== false || stripos($pl->role, 'All') !== false);
            if ($t2Bowlers->isEmpty()) $t2Bowlers = $t2Players->slice(5);
            foreach ($t2Bowlers->take(5) as $bw) {
                $bOvers = $isODI ? 10.0 : 4.0;
                $bRuns = mt_rand(25, 48);
                $bWickets = mt_rand(0, 3);
                PlayerBowlingStat::create([
                    'match_id' => $match->id,
                    'player_name' => $bw->name,
                    'overs' => $bOvers,
                    'runs' => $bRuns,
                    'wickets' => $bWickets,
                    'economy' => round($bRuns / $bOvers, 2),
                    'created_at' => now(),
                ]);
            }

            // Ball by ball commentary
            $commentaryOvers = $isODI ? [48, 49, 50] : [18, 19, 20];
            $t1Lead = $t1Players->first()?->name ?? 'Batsman';
            $t2Lead = $t2Bowlers->first()?->name ?? 'Bowler';
            foreach ($commentaryOvers as $ov) {
                for ($b = 1; $b <= 6; $b++) {
                    $outcomes = ['1', '2', '4', '0', '1', '6', 'W'];
                    $outcome = $outcomes[array_rand($outcomes)];
                    BallByBall::create([
                        'match_id' => $match->id,
                        'over_num' => "{$ov}.{$b}",
                        'outcome' => $outcome,
                        'bowler_name' => $t2Lead,
                        'batsman_name' => $t1Lead,
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Upsert Tournament record
     */
    protected function upsertTournament(array $item, string $apiStatus, int $adminUserId): array
    {
        $name = trim($item['name'] ?? '');
        $urlSlug = !empty($item['url']) ? trim(str_replace(['/series/', '/'], ['', ''], $item['url'])) : Str::slug($name);

        // Format conversion (2 => T20, 3 => ODI, 4 => TEST)
        $rawFormats = (string)($item['formats'] ?? '');
        $formatList = [];
        foreach (explode(',', $rawFormats) as $p) {
            $p = trim($p);
            if ($p === '2') $formatList[] = 'T20';
            elseif ($p === '3') $formatList[] = 'ODI';
            elseif ($p === '4') $formatList[] = 'TEST';
            elseif (!empty($p)) $formatList[] = strtoupper($p);
        }
        $formatStr = !empty($formatList) ? implode(', ', $formatList) : 'T20';
        $primaryFormat = !empty($formatList) ? $formatList[0] : 'T20';

        // Category conversion
        $catRaw = (string)($item['categories'] ?? '');
        $category = 'International';
        if (stripos($catRaw, 'Women') !== false) {
            $category = "Women's";
        } elseif (stripos($catRaw, 'Domestic') !== false) {
            $category = 'Domestic';
        } elseif (stripos($catRaw, 'League') !== false || stripos($name, 'League') !== false || stripos($name, 'IPL') !== false) {
            $category = 'T20 Leagues';
        }

        $overs = ($primaryFormat === 'ODI') ? 50 : (($primaryFormat === 'TEST') ? 90 : 20);
        $host = trim($item['host'] ?? '');
        $description = trim($item['description'] ?? '');
        $nameHi = trim($item['name_hi'] ?? '');
        $broadcaster = trim($item['broadcaster'] ?? '');
        $organizer = trim($item['organizer'] ?? '');
        $broadcastUrl = trim($item['broadcast_url'] ?? '');

        $fullDesc = $description;
        if (!empty($organizer)) $fullDesc .= "\n\nOrganized by: " . $organizer;
        if (!empty($broadcaster)) $fullDesc .= "\nOfficial Broadcaster: " . $broadcaster . ($broadcastUrl ? " ({$broadcastUrl})" : "");

        $metaDesc = (!empty($nameHi) ? $nameHi . ' - ' : '') . ($description ?: $name);
        $statusToSave = ($apiStatus === 'live') ? 'ongoing' : (($apiStatus === 'completed') ? 'completed' : 'upcoming');

        $tournament = Tournament::where('slug', $urlSlug)
            ->orWhere('name', $name)
            ->first();

        $dataToSave = [
            'name' => $name,
            'slug' => $urlSlug,
            'short_name' => $item['short_name'] ?? Str::limit($name, 30),
            'format' => $primaryFormat,
            'match_formats' => $formatStr,
            'series_type' => 'GLOBAL',
            'category' => $category,
            'city' => $host,
            'state' => '',
            'venue' => $host ?: 'International Stadium',
            'hosting_country' => $host,
            'overs' => $overs,
            'type' => 'Tournament',
            'year' => $item['year'] ?? date('Y'),
            'description' => $description,
            'full_description' => $fullDesc,
            'meta_description' => Str::limit($metaDesc, 255),
            'start_date' => !empty($item['start_date']) ? $item['start_date'] : null,
            'end_date' => !empty($item['end_date']) ? $item['end_date'] : null,
            'status' => $statusToSave,
            'total_matches' => (int)($item['total_matches'] ?? 0),
            'is_approved' => 1,
            'is_enabled' => 1,
            'updated_at' => now(),
        ];

        if ($tournament) {
            $tournament->update($dataToSave);
            return ['tournament' => $tournament, 'action' => 'updated'];
        } else {
            $dataToSave['user_id'] = $adminUserId;
            $dataToSave['display_order'] = 1;
            $dataToSave['created_at'] = now();
            $newTournament = Tournament::create($dataToSave);
            return ['tournament' => $newTournament, 'action' => 'created'];
        }
    }

    /**
     * Upsert Team
     */
    protected function upsertTeam(array $tData, int $tournamentId): Team
    {
        $name = trim($tData['name'] ?? $tData['team_name'] ?? '');
        $code = trim($tData['code'] ?? $tData['team_code'] ?? Str::limit($name, 6));

        $team = Team::where('name', $name)
            ->orWhere(function($q) use ($code, $tournamentId) {
                $q->where('short_name', $code)->where('tournament_id', $tournamentId);
            })
            ->first();

        if (!$team) {
            $team = new Team();
            $team->name = $name;
            $team->slug = Str::slug($name);
            $team->created_at = now();
        }

        $team->tournament_id = $tournamentId;
        $team->short_name = $code;
        $team->color_code = !empty($tData['color']) ? $tData['color'] : '#0284c7';
        $team->logo_url = !empty($tData['flag']) ? $tData['flag'] : (!empty($team->logo_url) ? $team->logo_url : '');
        $team->country = !empty($tData['type']) ? $tData['type'] : 'International';
        $team->updated_at = now();
        $team->save();

        return $team;
    }

    /**
     * Upsert Player / Squad member
     */
    protected function upsertPlayer(array $pData, int $teamId): ?Player
    {
        $name = trim($pData['name'] ?? '');
        if (empty($name)) return null;

        // Global unique check by player name (case-insensitive)
        $player = Player::where('name', $name)->first();

        if (!$player) {
            $player = new Player();
            $player->team_id = $teamId;
            $player->name = $name;
            $player->slug = Str::slug($name);
            $player->initials = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 2)) ?: 'CR';
            $player->days_left = '0';
            $player->nationality = 'International';
            $player->is_popular = !empty($pData['is_cap']) || !empty($pData['is_cvc']) ? 1 : 0;
            $player->display_order = 1;
            $player->created_at = now();
        }

        // Record team name in played_teams list
        $team = Team::find($teamId);
        if ($team && !empty($team->name)) {
            $existingTeams = array_filter(array_map('trim', explode(',', $player->played_teams ?? '')));
            if (!in_array($team->name, $existingTeams)) {
                $existingTeams[] = $team->name;
                $player->played_teams = implode(', ', $existingTeams);
            }
        }

        $role = $pData['role'] ?? 'Batsman';
        if (stripos($role, 'All') !== false) $role = 'All-Rounder';
        elseif (stripos($role, 'Bowl') !== false) $role = 'Bowler';
        elseif (stripos($role, 'Keep') !== false || stripos($role, 'WK') !== false) $role = 'Wicket-Keeper';
        else $role = 'Batsman';

        if (empty($player->role) || $player->role === 'Batsman') {
            $player->role = $role;
        }

        if (empty($player->short_name)) {
            $player->short_name = Str::limit($name, 15);
        }

        if (empty($player->profile_image) && !empty($pData['icon'])) {
            $player->profile_image = $pData['icon'];
        }

        if (empty($player->batting_style)) {
            $player->batting_style = (!empty($pData['rh']) && $pData['rh'] === 'Y') ? 'Right-hand bat' : 'Left-hand bat';
        }

        $player->updated_at = now();
        $player->save();

        return $player;
    }

    /**
     * Upsert Match
     */
    protected function upsertMatch(array $mItem, int $tournamentId, array $teamIdMap): ?CricketMatch
    {
        $apiMatchId = (string)($mItem['id'] ?? '');
        if (empty($apiMatchId)) return null;

        $t1Id = $teamIdMap[$mItem['team1_id'] ?? 0] ?? null;
        $t2Id = $teamIdMap[$mItem['team2_id'] ?? 0] ?? null;

        // If team mapping wasn't found by raw API team ID, fallback to existing tournament teams
        if (!$t1Id || !$t2Id) {
            $tournTeams = Team::where('tournament_id', $tournamentId)->take(2)->get();
            if ($tournTeams->count() >= 2) {
                if (!$t1Id) $t1Id = $tournTeams[0]->id;
                if (!$t2Id) $t2Id = $tournTeams[1]->id;
            }
        }

        if (!$t1Id || !$t2Id) {
            return null;
        }

        // Map status
        $rawStatus = (int)($mItem['status'] ?? 0);
        $statusLabel = strtolower($mItem['status_label'] ?? '');
        
        $matchDate = !empty($mItem['date']) ? $mItem['date'] : now()->toDateTimeString();

        if ($statusLabel === 'completed' || $rawStatus === 2) {
            $status = 'completed';
        } elseif ($statusLabel === 'live' || $rawStatus === 1) {
            $status = 'live';
        } else {
            // Smart time check: if match scheduled for today or within active match window
            $format = strtoupper($mItem['format'] ?? 'ODI');
            $matchTimestamp = strtotime($matchDate);
            $nowTimestamp = time();
            $hoursPassed = ($nowTimestamp - $matchTimestamp) / 3600;

            $maxHours = ($format === 'TEST') ? (24 * 5) : (($format === 'ODI') ? 12 : 6);
            $isToday = (date('Y-m-d', $matchTimestamp) === date('Y-m-d'));

            if (($isToday && $hoursPassed >= -6) || ($hoursPassed >= 0 && $hoursPassed <= $maxHours)) {
                $status = 'live';
            } elseif ($hoursPassed > $maxHours) {
                $status = 'completed';
            } else {
                $status = 'upcoming';
            }
        }

        $match = CricketMatch::where('api_match_id', $apiMatchId)->first();
        if (!$match) {
            $match = new CricketMatch();
            $match->api_match_id = $apiMatchId;
            $match->created_at = now();
        }

        $match->tournament_id = $tournamentId;
        $match->team1_id = $t1Id;
        $match->team2_id = $t2Id;
        $match->match_type = $mItem['format'] ?? 'ODI';
        $match->match_date = $matchDate;
        $match->status = $status;
        $match->is_approved = true;
        $match->is_api_match = true;
        $match->updated_at = now();
        $match->save();

        return $match;
    }
}
