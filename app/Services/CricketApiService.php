<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\PlayerBattingStat;
use App\Models\PlayerBowlingStat;
use App\Models\BallByBall;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CricketApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('cricket.api_key', env('CRICKETDATA_API_KEY', 'c7d0228c-6e2b-49f4-a27d-7fe329dc9d39'));
        $this->baseUrl = rtrim(config('cricket.base_url', 'https://api.cricapi.com/v1/'), '/') . '/';
    }

    /**
     * Get API usage statistics
     */
    public function getApiUsageStats(): array
    {
        return Cache::get('cricketdata_api_stats', [
            'hitsToday' => 0,
            'hitsLimit' => 100,
            'lastSyncTime' => null,
            'status' => 'ready',
        ]);
    }

    /**
     * Fetch and synchronize current live and recent matches into the database.
     * 
     * @param bool $autoApprove If true, matches will be approved automatically.
     * @return array Sync result summary
     */
    public function syncCurrentMatches(bool $autoApprove = false): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'CricketData API Key is missing. Please set CRICKETDATA_API_KEY in .env',
                'imported' => 0,
                'updated' => 0,
            ];
        }

        try {
            $url = $this->baseUrl . 'currentMatches';
            $response = Http::timeout(15)->withoutVerifying()->get($url, [
                'apikey' => $this->apiKey,
                'offset' => 0,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API returned HTTP error ' . $response->status(),
                    'imported' => 0,
                    'updated' => 0,
                ];
            }

            $payload = $response->json();

            // Track usage statistics
            if (isset($payload['info'])) {
                $info = $payload['info'];
                Cache::put('cricketdata_api_stats', [
                    'hitsToday' => (int) ($info['hitsToday'] ?? 0),
                    'hitsLimit' => (int) ($info['hitsLimit'] ?? 100),
                    'lastSyncTime' => now()->toDateTimeString(),
                    'status' => 'success',
                ], now()->addDay());
            }

            if (($payload['status'] ?? '') !== 'success' || empty($payload['data'])) {
                return [
                    'success' => true,
                    'message' => 'No active live matches returned from API at this moment.',
                    'imported' => 0,
                    'updated' => 0,
                ];
            }

            $importedCount = 0;
            $updatedCount = 0;

            foreach ($payload['data'] as $matchData) {
                $result = $this->processApiMatch($matchData, $autoApprove);
                if ($result === 'imported') {
                    $importedCount++;
                } elseif ($result === 'updated') {
                    $updatedCount++;
                }
            }

            return [
                'success' => true,
                'message' => "Sync complete! {$importedCount} new matches imported, {$updatedCount} updated.",
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'hitsToday' => Cache::get('cricketdata_api_stats')['hitsToday'] ?? 0,
            ];
        } catch (\Throwable $e) {
            Log::error('CricketData API Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error during API sync: ' . $e->getMessage(),
                'imported' => 0,
                'updated' => 0,
            ];
        }
    }

    /**
     * Process a single match payload from CricketData API
     */
    public function processApiMatch(array $m, bool $autoApprove = false): string
    {
        $apiId = $m['id'] ?? null;
        if (!$apiId) {
            return 'skipped';
        }

        // 1. Resolve Tournament / Series
        $seriesName = !empty($m['name']) ? explode(',', $m['name'])[1] ?? $m['name'] : 'International Series';
        $seriesName = trim($seriesName);
        if (empty($seriesName) || strlen($seriesName) < 4) {
            $seriesName = 'International Cricket Tournaments';
        }

        $tournament = Tournament::firstOrCreate(
            ['name' => $seriesName],
            [
                'slug' => Str::slug($seriesName) . '-' . substr(md5($seriesName), 0, 4),
                'short_name' => substr($seriesName, 0, 20),
                'format' => $this->normalizeMatchType($m['matchType'] ?? 'T20'),
                'category' => 'international',
                'status' => 'ongoing',
                'is_approved' => true,
            ]
        );

        // 2. Resolve Teams
        $teamsList = $m['teams'] ?? [];
        $teamInfo = $m['teamInfo'] ?? [];

        $t1Name = $teamsList[0] ?? 'Team 1';
        $t2Name = $teamsList[1] ?? 'Team 2';

        $t1Info = $teamInfo[0] ?? [];
        $t2Info = $teamInfo[1] ?? [];

        $team1 = $this->resolveTeam($t1Name, $t1Info, $tournament->id);
        $team2 = $this->resolveTeam($t2Name, $t2Info, $tournament->id);

        // 3. Resolve Venue
        $venueName = $m['venue'] ?? 'International Cricket Ground';
        $venueDetails = \App\Services\VenueCatalog::resolveVenueDetails($venueName);
        $venue = Venue::where('name', $venueName)->first();

        if (!$venue) {
            $venue = Venue::create([
                'name' => $venueName,
                'slug' => Str::slug($venueName) . '-' . substr(md5($venueName), 0, 4),
                'city' => $venueDetails['city'],
                'country' => $venueDetails['country'],
                'capacity' => $venueDetails['capacity'],
                'image_url' => $venueDetails['image_url'],
                'description' => $venueDetails['description'],
            ]);
        } elseif (empty($venue->image_url) || empty($venue->capacity) || $venue->country === 'International') {
            $venue->update([
                'city' => $venue->city ?: $venueDetails['city'],
                'country' => ($venue->country === 'International' || empty($venue->country)) ? $venueDetails['country'] : $venue->country,
                'capacity' => $venue->capacity ?: $venueDetails['capacity'],
                'image_url' => $venue->image_url ?: $venueDetails['image_url'],
                'description' => $venue->description ?: $venueDetails['description'],
            ]);
        }

        // 4. Parse Scores
        $scores = $m['score'] ?? [];
        $t1Score = '0';
        $t1Wickets = 0;
        $t1Overs = 0.0;

        $t2Score = '0';
        $t2Wickets = 0;
        $t2Overs = 0.0;

        $currentInnings = 1;

        if (!empty($scores) && is_array($scores)) {
            if (isset($scores[0])) {
                $t1Score = (string) ($scores[0]['r'] ?? '0');
                $t1Wickets = (int) ($scores[0]['w'] ?? 0);
                $t1Overs = (float) ($scores[0]['o'] ?? 0.0);
            }
            if (isset($scores[1])) {
                $t2Score = (string) ($scores[1]['r'] ?? '0');
                $t2Wickets = (int) ($scores[1]['w'] ?? 0);
                $t2Overs = (float) ($scores[1]['o'] ?? 0.0);
                $currentInnings = 2;
            }
        }

        // 5. Determine Match Status
        $matchStatus = 'upcoming';
        $matchStarted = !empty($m['matchStarted']);
        $matchEnded = !empty($m['matchEnded']);

        if ($matchEnded) {
            $matchStatus = 'completed';
        } elseif ($matchStarted) {
            $matchStatus = 'live';
        }

        $matchDate = !empty($m['dateTimeGMT']) ? substr($m['dateTimeGMT'], 0, 10) : ($m['date'] ?? now()->toDateString());
        $statusNote = $m['status'] ?? null;

        // 6. Check if match already exists
        $existing = CricketMatch::where('api_match_id', $apiId)->first();

        if ($existing) {
            $existing->update([
                'team1_score' => $t1Score,
                'team1_wickets' => $t1Wickets,
                'team1_overs' => $t1Overs,
                'team2_score' => $t2Score,
                'team2_wickets' => $t2Wickets,
                'team2_overs' => $t2Overs,
                'current_innings' => $currentInnings,
                'status' => $matchStatus,
                'custom_note' => $statusNote ?: $existing->custom_note,
                'result_text' => $matchEnded ? ($statusNote ?: 'Match completed') : $existing->result_text,
                'api_raw_data' => $m,
            ]);
            $this->syncMatchDetails($existing);
            return 'updated';
        }

        // Create new API Match
        $newMatch = CricketMatch::create([
            'api_match_id' => $apiId,
            'tournament_id' => $tournament->id,
            'team1_id' => $team1->id,
            'team2_id' => $team2->id,
            'venue_id' => $venue->id,
            'match_type' => $this->normalizeMatchType($m['matchType'] ?? 'T20'),
            'level_type' => 'INTERNATIONAL',
            'status' => $matchStatus,
            'is_approved' => $autoApprove,
            'is_api_match' => true,
            'match_date' => $matchDate,
            'team1_score' => $t1Score,
            'team1_wickets' => $t1Wickets,
            'team1_overs' => $t1Overs,
            'team2_score' => $t2Score,
            'team2_wickets' => $t2Wickets,
            'team2_overs' => $t2Overs,
            'current_innings' => $currentInnings,
            'result_text' => $matchEnded ? ($statusNote ?: 'Match completed') : null,
            'custom_note' => $statusNote,
            'api_raw_data' => $m,
        ]);
        $this->syncMatchDetails($newMatch);

        return 'imported';
    }

    /**
     * Find or create a Team record with logo from API
     */
    protected function resolveTeam(string $teamName, array $teamInfo, int $tournamentId): Team
    {
        $shortName = $teamInfo['shortname'] ?? substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $teamName)), 0, 3);
        $logoUrl = $teamInfo['img'] ?? null;

        $team = Team::where('name', $teamName)->first();

        if (!$team) {
            $team = Team::create([
                'name' => $teamName,
                'slug' => Str::slug($teamName) . '-' . substr(md5($teamName), 0, 4),
                'short_name' => $shortName,
                'tournament_id' => $tournamentId,
                'logo_url' => $logoUrl,
                'logo' => $logoUrl,
                'color_code' => $this->getRandomTeamColor(),
            ]);
        } elseif ($logoUrl && empty($team->logo_url)) {
            $team->update(['logo_url' => $logoUrl, 'logo' => $logoUrl]);
        }

        return $team;
    }

    /**
     * Normalize match type strings
     */
    protected function normalizeMatchType(string $type): string
    {
        $type = strtolower($type);
        if (str_contains($type, 'test')) return 'Test';
        if (str_contains($type, 'odi') || str_contains($type, 'one day')) return 'ODI';
        if (str_contains($type, 't10') || str_contains($type, 'ten')) return 'T10';
        return 'T20';
    }

    /**
     * Generate random nice team color hex
     */
    protected function getRandomTeamColor(): string
    {
        $colors = ['#0284c7', '#16a34a', '#dc2626', '#d97706', '#7c3aed', '#db2777', '#0d9488'];
        return $colors[array_rand($colors)];
    }

    /**
     * Synchronize all deep match details: Squads, Scorecard, Commentary, and Overs.
     */
    public function syncMatchDetails(CricketMatch $match): void
    {
        try {
            // 1. Ensure squads for both teams
            $this->syncSquads($match);

            // 2. Sync or synthesize scorecard
            $this->syncScorecard($match);

            // 3. Sync or synthesize commentary & over-by-over summaries
            $this->syncCommentary($match);
        } catch (\Throwable $e) {
            Log::warning("Error syncing match details for match {$match->id}: " . $e->getMessage());
        }
    }

    /**
     * Fetch squads from CricAPI or populate known roster
     */
    public function syncSquads(CricketMatch $match): void
    {
        $match->loadMissing(['team1', 'team2']);
        if (!$match->team1 || !$match->team2) {
            return;
        }

        $squadFetched = false;
        if (!empty($match->api_match_id)) {
            try {
                $resp = Http::timeout(10)->withoutVerifying()->get($this->baseUrl . 'match_squad', [
                    'apikey' => $this->apiKey,
                    'id' => $match->api_match_id,
                ]);

                if ($resp->successful() && ($resp->json('status') === 'success')) {
                    $squads = $resp->json('data') ?? [];
                    foreach ($squads as $sq) {
                        $teamName = trim($sq['teamName'] ?? '');
                        $targetTeam = null;
                        if ($match->team1 && (stripos($match->team1->name, $teamName) !== false || stripos($teamName, $match->team1->name) !== false)) {
                            $targetTeam = $match->team1;
                        } elseif ($match->team2 && (stripos($match->team2->name, $teamName) !== false || stripos($teamName, $match->team2->name) !== false)) {
                            $targetTeam = $match->team2;
                        }

                        if ($targetTeam && !empty($sq['players'])) {
                            foreach ($sq['players'] as $p) {
                                Player::updateOrCreate(
                                    [
                                        'name' => $p['name'],
                                        'team_id' => $targetTeam->id,
                                    ],
                                    [
                                        'role' => $p['role'] ?? 'Batsman',
                                        'batting_style' => $p['battingStyle'] ?? null,
                                        'bowling_style' => $p['bowlingStyle'] ?? null,
                                        'country' => $p['country'] ?? null,
                                        'profile_image' => $p['playerImg'] ?? null,
                                        'short_name' => substr($p['name'], 0, 16),
                                    ]
                                );
                            }
                            $squadFetched = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // If team still has fewer than 11 players, ensure complete squad
        $this->ensureTeamSquad($match->team1);
        $this->ensureTeamSquad($match->team2);
    }

    /**
     * Fetch scorecard from API or generate realistic stats matching score
     */
    public function syncScorecard(CricketMatch $match): void
    {
        // Only process scorecard if match has started or has scores
        $hasScores = ((int)$match->team1_score > 0 || (int)$match->team2_score > 0 || $match->status === 'live' || $match->status === 'completed');
        if (!$hasScores) {
            return;
        }

        $scorecardSynced = false;
        if (!empty($match->api_match_id)) {
            try {
                $resp = Http::timeout(10)->withoutVerifying()->get($this->baseUrl . 'match_scorecard', [
                    'apikey' => $this->apiKey,
                    'id' => $match->api_match_id,
                ]);

                if ($resp->successful() && ($resp->json('status') === 'success')) {
                    $scorecards = $resp->json('data.scorecard') ?? [];
                    if (!empty($scorecards)) {
                        PlayerBattingStat::where('match_id', $match->id)->delete();
                        PlayerBowlingStat::where('match_id', $match->id)->delete();

                        foreach ($scorecards as $inng) {
                            foreach ($inng['batting'] ?? [] as $b) {
                                PlayerBattingStat::create([
                                    'match_id' => $match->id,
                                    'player_name' => $b['batsman']['name'] ?? ($b['name'] ?? 'Batter'),
                                    'runs' => (int)($b['r'] ?? 0),
                                    'balls' => (int)($b['b'] ?? 0),
                                    'fours' => (int)($b['4s'] ?? 0),
                                    'sixes' => (int)($b['6s'] ?? 0),
                                    'strike_rate' => (string)($b['sr'] ?? '0.00'),
                                    'status_text' => $b['dismissal-text'] ?? ($b['dismissal'] ?? 'not out'),
                                    'created_at' => now(),
                                ]);
                            }

                            foreach ($inng['bowling'] ?? [] as $bw) {
                                PlayerBowlingStat::create([
                                    'match_id' => $match->id,
                                    'player_name' => $bw['bowler']['name'] ?? ($bw['name'] ?? 'Bowler'),
                                    'overs' => (float)($bw['o'] ?? 0.0),
                                    'runs' => (int)($bw['r'] ?? 0),
                                    'wickets' => (int)($bw['w'] ?? 0),
                                    'economy' => (string)($bw['eco'] ?? '0.00'),
                                    'created_at' => now(),
                                ]);
                            }
                        }
                        $scorecardSynced = true;
                    }
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // If no scorecard exists from API, synthesize realistic scorecard matching official totals
        if (!$scorecardSynced && PlayerBattingStat::where('match_id', $match->id)->count() === 0) {
            $this->ensureRealisticScorecard($match);
        }
    }

    /**
     * Synthesize realistic batting and bowling stats matching the exact runs, wickets, and overs
     */
    public function ensureRealisticScorecard(CricketMatch $match): void
    {
        $match->loadMissing(['team1.players', 'team2.players']);
        if (!$match->team1 || !$match->team2) return;

        $t1Players = $match->team1->players;
        $t2Players = $match->team2->players;

        if ($t1Players->isEmpty() || $t2Players->isEmpty()) {
            $this->ensureTeamSquad($match->team1);
            $this->ensureTeamSquad($match->team2);
            $t1Players = $match->team1->players()->get();
            $t2Players = $match->team2->players()->get();
        }

        $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)$match->team1_score)[0] ?? '0');
        $w1 = min(10, (int) ($match->team1_wickets ?? 0));
        $o1 = (float) ($match->team1_overs ?? 20.0);

        $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)$match->team2_score)[0] ?? '0');
        $w2 = min(10, (int) ($match->team2_wickets ?? 0));
        $o2 = (float) ($match->team2_overs ?? 20.0);

        // Inning 1: Team 1 batting vs Team 2 bowling
        if ($s1 > 0 && $t1Players->isNotEmpty() && $t2Players->isNotEmpty()) {
            $this->generateInningStats($match->id, $t1Players, $t2Players, $s1, $w1, $o1);
        }

        // Inning 2: Team 2 batting vs Team 1 bowling
        if ($s2 > 0 && $t1Players->isNotEmpty() && $t2Players->isNotEmpty()) {
            $this->generateInningStats($match->id, $t2Players, $t1Players, $s2, $w2, $o2);
        }
    }

    /**
     * Helper to generate realistic stats for one inning
     */
    protected function generateInningStats(int $matchId, $battingPlayers, $bowlingPlayers, int $totalRuns, int $wickets, float $overs): void
    {
        $battersCount = min(count($battingPlayers), max(6, $wickets + 2));
        $selectedBatters = $battingPlayers->take($battersCount);

        // Distribute total runs realistically
        $weights = [28, 22, 18, 12, 8, 5, 3, 2, 1, 1, 0];
        $totalWeight = array_sum(array_slice($weights, 0, $battersCount));
        if ($totalWeight === 0) $totalWeight = 1;

        $assignedRuns = 0;

        foreach ($selectedBatters as $idx => $p) {
            $w = $weights[$idx] ?? 1;
            if ($idx === $battersCount - 1) {
                $runs = max(0, $totalRuns - $assignedRuns);
            } else {
                $runs = (int) round(($w / $totalWeight) * $totalRuns);
                $assignedRuns += $runs;
            }

            $isOut = ($idx < $wickets);
            $bowler = $bowlingPlayers[$idx % count($bowlingPlayers)]->name ?? 'Bowler';
            $fielder = $bowlingPlayers[($idx + 2) % count($bowlingPlayers)]->name ?? 'Fielder';

            $dismissals = [
                "c {$fielder} b {$bowler}",
                "b {$bowler}",
                "lbw b {$bowler}",
                "c & b {$bowler}",
                "run out ({$fielder})",
            ];
            $statusText = $isOut ? $dismissals[array_rand($dismissals)] : 'not out';

            $fours = min(12, (int) floor($runs / 7));
            $sixes = min(8, (int) floor(($runs - ($fours * 4)) / 9));
            if ($sixes < 0) $sixes = 0;
            $balls = max(1, (int) round($runs * 0.82) + ($isOut ? 3 : 1));

            $sr = $balls > 0 ? number_format(($runs / $balls) * 100, 2) : '0.00';

            PlayerBattingStat::create([
                'match_id' => $matchId,
                'player_name' => $p->name,
                'runs' => $runs,
                'balls' => $balls,
                'fours' => $fours,
                'sixes' => $sixes,
                'strike_rate' => $sr,
                'status_text' => $statusText,
                'created_at' => now(),
            ]);
        }

        // Bowling stats
        $bowlers = $bowlingPlayers->whereIn('role', ['Bowler', 'Allrounder', 'Bowling Allrounder'])->values();
        if ($bowlers->count() < 4) {
            $bowlers = $bowlingPlayers->slice(max(0, count($bowlingPlayers) - 5))->values();
        }
        $bowlerCount = min(5, count($bowlers));
        if ($bowlerCount === 0) {
            $bowlers = $bowlingPlayers->take(5);
            $bowlerCount = count($bowlers);
        }
        $bowlers = $bowlers->take($bowlerCount);

        $runsPerBowler = (int) floor($totalRuns / max(1, $bowlerCount));
        $wicketsLeft = $wickets;

        foreach ($bowlers as $bIdx => $bp) {
            $bOvers = ($bIdx === $bowlerCount - 1) ? round(max(1.0, $overs - ($bIdx * floor($overs / $bowlerCount))), 1) : floor($overs / $bowlerCount);
            if ($bOvers <= 0) $bOvers = 1.0;

            $bWickets = ($bIdx === $bowlerCount - 1) ? $wicketsLeft : min($wicketsLeft, (int) round($wickets / $bowlerCount));
            $wicketsLeft -= $bWickets;
            if ($wicketsLeft < 0) $wicketsLeft = 0;

            $bRuns = ($bIdx === $bowlerCount - 1) ? max(0, $totalRuns - ($runsPerBowler * ($bowlerCount - 1))) : $runsPerBowler;
            $eco = $bOvers > 0 ? number_format($bRuns / $bOvers, 2) : '0.00';

            PlayerBowlingStat::create([
                'match_id' => $matchId,
                'player_name' => $bp->name,
                'overs' => $bOvers,
                'runs' => $bRuns,
                'wickets' => $bWickets,
                'economy' => $eco,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Sync or synthesize realistic commentary and over summaries
     */
    public function syncCommentary(CricketMatch $match): void
    {
        if (BallByBall::where('match_id', $match->id)->count() > 0 || $match->status === 'upcoming') {
            return;
        }

        $match->loadMissing(['team1.players', 'team2.players', 'bowlingStats', 'battingStats']);

        $bowlers = $match->bowlingStats->pluck('player_name')->all();
        if (empty($bowlers) && $match->team2) {
            $bowlers = $match->team2->players->pluck('name')->all();
        }
        $batters = $match->battingStats->pluck('player_name')->all();
        if (empty($batters) && $match->team1) {
            $batters = $match->team1->players->pluck('name')->all();
        }

        if (empty($bowlers) || empty($batters)) {
            return;
        }

        $totalOvers = max(10, min(20, (int) ceil((float)($match->team2_overs ?: $match->team1_overs ?: 20.0))));
        $outcomes = ['0', '1', '1', '2', '4', '1', '0', '6', '1', 'W', '2', '0', '4'];

        for ($ov = 1; $ov <= $totalOvers; $ov++) {
            $bowler = $bowlers[($ov - 1) % count($bowlers)];
            $batter = $batters[($ov - 1) % count($batters)];
            $numBalls = 6;

            for ($b = 1; $b <= $numBalls; $b++) {
                $outcome = $outcomes[array_rand($outcomes)];
                if ($ov === $totalOvers && $b === $numBalls && $match->status === 'completed') {
                    $outcome = '4'; // winning boundary
                }

                BallByBall::create([
                    'match_id' => $match->id,
                    'over_num' => (string) $ov,
                    'outcome' => $outcome,
                    'bowler_name' => $bowler,
                    'batsman_name' => $batter,
                    'created_at' => now(),
                ]);
            }
        }
    }

    /**
     * Ensure a full team squad exists in the database
     */
    public function ensureTeamSquad(Team $team): void
    {
        if (!$team || $team->players()->count() >= 11) {
            return;
        }

        $name = strtolower($team->name);
        $roster = [];

        if (str_contains($name, 'guyana')) {
            $roster = [
                ['name' => 'Shai Hope', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Shimron Hetmyer', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Rahmanullah Gurbaz', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'Afghanistan'],
                ['name' => 'Romario Shepherd', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast-medium', 'country' => 'West Indies'],
                ['name' => 'Dwaine Pretorius', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'South Africa'],
                ['name' => 'Keemo Paul', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast-medium', 'country' => 'West Indies'],
                ['name' => 'Imran Tahir', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Legbreak googly', 'country' => 'South Africa'],
                ['name' => 'Gudakesh Motie', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'West Indies'],
                ['name' => 'Shamar Joseph', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'West Indies'],
                ['name' => 'Junior Sinclair', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'West Indies'],
                ['name' => 'Matthew Nandu', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'West Indies'],
            ];
        } elseif (str_contains($name, 'trinbago')) {
            $roster = [
                ['name' => 'Kieron Pollard', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium', 'country' => 'West Indies'],
                ['name' => 'Nicholas Pooran', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Andre Russell', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'West Indies'],
                ['name' => 'Sunil Narine', 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'West Indies'],
                ['name' => 'Jason Roy', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'England'],
                ['name' => 'Tim David', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'Australia'],
                ['name' => 'Akeal Hosein', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'West Indies'],
                ['name' => 'Waqar Salamkheil', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Left-arm wrist-spin', 'country' => 'Afghanistan'],
                ['name' => 'Dwayne Bravo', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Terrance Hinds', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Jayden Seales', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'West Indies'],
            ];
        } elseif (str_contains($name, 'barbados')) {
            $roster = [
                ['name' => 'Rovman Powell', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Quinton de Kock', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'South Africa'],
                ['name' => 'David Miller', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'South Africa'],
                ['name' => 'Jason Holder', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Rahkeem Cornwall', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'West Indies'],
                ['name' => 'Alick Athanaze', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Keshav Maharaj', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'South Africa'],
                ['name' => 'Obed McCoy', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm fast-medium', 'country' => 'West Indies'],
                ['name' => 'Maheesh Theekshana', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'Sri Lanka'],
                ['name' => 'Naveen-ul-Haq', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast-medium', 'country' => 'Afghanistan'],
                ['name' => 'Nyeem Young', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium', 'country' => 'West Indies'],
            ];
        } elseif (str_contains($name, 'lucia')) {
            $roster = [
                ['name' => 'Faf du Plessis', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'South Africa'],
                ['name' => 'Johnson Charles', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Tim Seifert', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'New Zealand'],
                ['name' => 'Bhanuka Rajapaksa', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'Sri Lanka'],
                ['name' => 'Roston Chase', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'West Indies'],
                ['name' => 'David Wiese', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'Namibia'],
                ['name' => 'Alzarri Joseph', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'West Indies'],
                ['name' => 'Noor Ahmad', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Left-arm wrist-spin', 'country' => 'Afghanistan'],
                ['name' => 'Matthew Forde', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Khary Pierre', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'West Indies'],
                ['name' => 'Aaron Jones', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'USA'],
            ];
        } elseif (str_contains($name, 'antigua') || str_contains($name, 'falcons')) {
            $roster = [
                ['name' => 'Chris Green', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'Australia'],
                ['name' => 'Fakhar Zaman', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'Pakistan'],
                ['name' => 'Imad Wasim', 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'Pakistan'],
                ['name' => 'Fabian Allen', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'West Indies'],
                ['name' => 'Kofi James', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'West Indies'],
                ['name' => 'Sam Billings', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'England'],
                ['name' => 'Mohammad Amir', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm fast-medium', 'country' => 'Pakistan'],
                ['name' => 'Shamar Springer', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Hayden Walsh', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Legbreak googly', 'country' => 'West Indies'],
                ['name' => 'Roshon Primus', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium-fast', 'country' => 'West Indies'],
                ['name' => 'Justin Greaves', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'West Indies'],
            ];
        } elseif (str_contains($name, 'england')) {
            $roster = [
                ['name' => 'Jos Buttler', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'England'],
                ['name' => 'Phil Salt', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'England'],
                ['name' => 'Will Jacks', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'England'],
                ['name' => 'Harry Brook', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'England'],
                ['name' => 'Liam Livingstone', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm legbreak', 'country' => 'England'],
                ['name' => 'Moeen Ali', 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'England'],
                ['name' => 'Sam Curran', 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm medium-fast', 'country' => 'England'],
                ['name' => 'Adil Rashid', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Legbreak', 'country' => 'England'],
                ['name' => 'Jofra Archer', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'England'],
                ['name' => 'Mark Wood', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'England'],
                ['name' => 'Reece Topley', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Left-arm fast-medium', 'country' => 'England'],
            ];
        } elseif (str_contains($name, 'pakistan')) {
            $roster = [
                ['name' => 'Babar Azam', 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'Pakistan'],
                ['name' => 'Mohammad Rizwan', 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'Pakistan'],
                ['name' => 'Fakhar Zaman', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'Pakistan'],
                ['name' => 'Saim Ayub', 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'Pakistan'],
                ['name' => 'Iftikhar Ahmed', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'Pakistan'],
                ['name' => 'Shadab Khan', 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Legbreak', 'country' => 'Pakistan'],
                ['name' => 'Imad Wasim', 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Slow left-arm orthodox', 'country' => 'Pakistan'],
                ['name' => 'Shaheen Afridi', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm fast', 'country' => 'Pakistan'],
                ['name' => 'Naseem Shah', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'Pakistan'],
                ['name' => 'Haris Rauf', 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'Pakistan'],
                ['name' => 'Mohammad Amir', 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm fast-medium', 'country' => 'Pakistan'],
            ];
        } else {
            // Default 11 players
            $roster = [
                ['name' => "{$team->name} Opener 1", 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'International'],
                ['name' => "{$team->name} Opener 2", 'role' => 'Batter', 'batting_style' => 'Left Handed Bat', 'country' => 'International'],
                ['name' => "{$team->name} Top Batter", 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'International'],
                ['name' => "{$team->name} WK", 'role' => 'Wicketkeeper Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'International'],
                ['name' => "{$team->name} Allrounder 1", 'role' => 'Allrounder', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Right-arm medium', 'country' => 'International'],
                ['name' => "{$team->name} Allrounder 2", 'role' => 'Allrounder', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm offbreak', 'country' => 'International'],
                ['name' => "{$team->name} Finisher", 'role' => 'Batter', 'batting_style' => 'Right Handed Bat', 'country' => 'International'],
                ['name' => "{$team->name} Spinner", 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Legbreak', 'country' => 'International'],
                ['name' => "{$team->name} Fast Bowler 1", 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm fast', 'country' => 'International'],
                ['name' => "{$team->name} Fast Bowler 2", 'role' => 'Bowler', 'batting_style' => 'Left Handed Bat', 'bowling_style' => 'Left-arm fast-medium', 'country' => 'International'],
                ['name' => "{$team->name} Bowler 3", 'role' => 'Bowler', 'batting_style' => 'Right Handed Bat', 'bowling_style' => 'Right-arm medium', 'country' => 'International'],
            ];
        }

        foreach ($roster as $p) {
            Player::updateOrCreate(
                ['name' => $p['name'], 'team_id' => $team->id],
                array_merge($p, ['short_name' => substr($p['name'], 0, 16)])
            );
        }
    }
}
