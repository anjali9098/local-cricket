<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Team;
use App\Models\CricketMatch;
use App\Models\Player;
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
            $response = Http::timeout(20)
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
     * Supports sport (Cricket, Football, etc.), limit, and page (0-indexed or 1-indexed)
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
     * 4. Get Squad of any Team from Series
     */
    public function getSeriesSquad(int $seriesId, int $teamId, int $formatId = 2): array
    {
        $json = $this->makeRequest([
            'action' => 'series-squad',
            'id' => $seriesId,
            'teamId' => $teamId,
            'formatId' => $formatId,
            '$formatId' => $formatId
        ]);

        $players = [];
        if (!empty($json['teams'])) {
            foreach ($json['teams'] as $teamObj) {
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
        }

        if (empty($players) && !empty($json['players'])) {
            $players = $json['players'];
        }

        return [
            'seriesId' => $seriesId,
            'teamId' => $teamId,
            'formatId' => $formatId,
            'players' => array_values($players),
            'raw' => $json
        ];
    }

    /**
     * 5. Sync a Single Series by ID
     */
    public function syncSingleSeries(int $seriesId, bool $syncSquads = true): array
    {
        $adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first() ?: User::first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        $detail = $this->getSeriesDetail($seriesId);
        if (!$detail) {
            return [
                'success' => false,
                'message' => "Series with ID {$seriesId} not found on Possible11 API."
            ];
        }

        // Upsert tournament from detail
        $tRes = $this->upsertTournament($detail, $detail['status'] ?? 'ongoing', $adminUserId);
        $tournament = $tRes['tournament'];

        // Teams
        $teamsData = $this->getSeriesTeams($seriesId);
        $teamIdMap = [];
        $teamsCount = 0;
        $playersCount = 0;

        foreach ($teamsData as $tData) {
            $team = $this->upsertTeam($tData, $tournament->id);
            $teamIdMap[$tData['id']] = $team->id;
            $teamsCount++;

            if ($syncSquads) {
                $squadData = $this->getSeriesSquad($seriesId, (int)$tData['id'], 2);
                $playersList = $squadData['players'] ?? [];
                if (empty($playersList)) {
                    $squadData = $this->getSeriesSquad($seriesId, (int)$tData['id'], 3);
                    $playersList = $squadData['players'] ?? [];
                }
                foreach ($playersList as $pData) {
                    $this->upsertPlayer($pData, $team->id);
                    $playersCount++;
                }
            }
        }

        // Matches
        $matchesCount = 0;
        if (!empty($detail['matches'])) {
            foreach ($detail['matches'] as $mItem) {
                $this->upsertMatch($mItem, $tournament->id, $teamIdMap);
                $matchesCount++;
            }
        }

        return [
            'success' => true,
            'message' => "Synced series '{$tournament->name}': {$teamsCount} teams, {$matchesCount} matches, {$playersCount} players."
        ];
    }

    /**
     * 6. Deep Synchronize Series + Teams + Matches + Squads
     *
     * @param string $status 'live' | 'upcoming' | 'completed' | 'all'
     * @param bool $syncSquads whether to also import full team squads
     * @param string $sport 'Cricket' | 'Football' etc.
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function syncSeries(string $status = 'live', bool $syncSquads = false, string $sport = 'Cricket', int $limit = 50, int $page = 0): array
    {
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

                foreach ($seriesList as $seriesItem) {
                    $apiSeriesId = (int)($seriesItem['id'] ?? 0);
                    if (!$apiSeriesId) continue;

                    // 1. Upsert Tournament
                    $tournamentRes = $this->upsertTournament($seriesItem, $st, $adminUserId);
                    $tournament = $tournamentRes['tournament'];
                    if ($tournamentRes['action'] === 'created') $stats['series_created']++;
                    if ($tournamentRes['action'] === 'updated') $stats['series_updated']++;

                    // 2. Fetch and Sync Teams for this Series
                    $teamsData = $this->getSeriesTeams($apiSeriesId);
                    $teamIdMap = []; // Possible11 team ID => Local Team ID

                    foreach ($teamsData as $tData) {
                        $team = $this->upsertTeam($tData, $tournament->id);
                        $teamIdMap[$tData['id']] = $team->id;
                        $stats['teams_synced']++;

                        // 3. Sync Squads if requested
                        if ($syncSquads) {
                            $squadData = $this->getSeriesSquad($apiSeriesId, (int)$tData['id'], 2);
                            $playersList = $squadData['players'] ?? [];
                            if (empty($playersList)) {
                                $squadData = $this->getSeriesSquad($apiSeriesId, (int)$tData['id'], 3);
                                $playersList = $squadData['players'] ?? [];
                            }
                            if (!empty($playersList)) {
                                foreach ($playersList as $pData) {
                                    $this->upsertPlayer($pData, $team->id);
                                    $stats['players_synced']++;
                                }
                            }
                        }
                    }

                    // 4. Fetch Series Detail & Sync Matches
                    $detail = $this->getSeriesDetail($apiSeriesId);
                    if ($detail && !empty($detail['matches'])) {
                        foreach ($detail['matches'] as $mItem) {
                            $this->upsertMatch($mItem, $tournament->id, $teamIdMap);
                            $stats['matches_synced']++;
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
        $name = trim($tData['name'] ?? '');
        $code = trim($tData['code'] ?? Str::limit($name, 6));

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
        $team->logo_url = !empty($tData['flag']) ? $tData['flag'] : '';
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

        $player = Player::where('team_id', $teamId)
            ->where('name', $name)
            ->first();

        if (!$player) {
            $player = new Player();
            $player->team_id = $teamId;
            $player->name = $name;
            $player->slug = Str::slug($name . '-' . $teamId);
            $player->initials = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 2)) ?: 'CR';
            $player->days_left = '0';
            $player->nationality = 'International';
            $player->is_popular = 0;
            $player->display_order = 1;
            $player->created_at = now();
        }

        $role = $pData['role'] ?? 'Batsman';
        if (stripos($role, 'All') !== false) $role = 'All-Rounder';
        elseif (stripos($role, 'Bowl') !== false) $role = 'Bowler';
        elseif (stripos($role, 'Keep') !== false || stripos($role, 'WK') !== false) $role = 'Wicket-Keeper';
        else $role = 'Batsman';

        $player->role = $role;
        $player->short_name = Str::limit($name, 15);
        $player->profile_image = $pData['icon'] ?? '';
        $player->batting_style = (!empty($pData['rh']) && $pData['rh'] === 'Y') ? 'Right-hand bat' : 'Left-hand bat';
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

        $t1Id = $teamIdMap[$mItem['team1_id']] ?? null;
        $t2Id = $teamIdMap[$mItem['team2_id']] ?? null;

        // Map status
        $rawStatus = (int)($mItem['status'] ?? 0);
        $statusLabel = strtolower($mItem['status_label'] ?? '');
        
        $matchDate = !empty($mItem['date']) ? $mItem['date'] : now()->toDateTimeString();

        if ($statusLabel === 'completed' || $rawStatus === 2) {
            $status = 'completed';
        } elseif ($statusLabel === 'live' || $rawStatus === 1) {
            $status = 'live';
        } else {
            $status = 'upcoming';
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
