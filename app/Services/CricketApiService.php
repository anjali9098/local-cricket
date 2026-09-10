<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\Team;
use App\Models\Venue;
use App\Models\Tournament;
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
        $venue = Venue::firstOrCreate(
            ['name' => $venueName],
            [
                'slug' => Str::slug($venueName) . '-' . substr(md5($venueName), 0, 4),
                'city' => explode(',', $venueName)[1] ?? explode(',', $venueName)[0] ?? 'Stadium',
                'country' => 'International',
            ]
        );

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
            return 'updated';
        }

        // Create new API Match
        CricketMatch::create([
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
}
