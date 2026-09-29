<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Possible11ApiService
{
    protected string $baseUrl = 'https://possible11.com/api/';

    /**
     * Fetch and synchronize series from Possible11 API
     *
     * @param string $status 'live' | 'upcoming' | 'all'
     * @return array
     */
    public function syncSeries(string $status = 'live'): array
    {
        $statusesToFetch = ($status === 'all') ? ['live', 'upcoming'] : [$status];
        $totalFetched = 0;
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];

        $adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first() ?: User::first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        foreach ($statusesToFetch as $st) {
            try {
                $url = $this->baseUrl . '?action=series&sport=Cricket&status=' . urlencode($st);
                
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'application/json'
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    $errors[] = "Failed fetching {$st} series: HTTP " . $response->status();
                    continue;
                }

                $json = $response->json();
                if (!isset($json['data']) || !is_array($json['data'])) {
                    continue;
                }

                $items = $json['data'];
                $totalFetched += count($items);

                foreach ($items as $item) {
                    $res = $this->upsertSeriesItem($item, $st, $adminUserId);
                    if ($res === 'created') {
                        $createdCount++;
                    } elseif ($res === 'updated') {
                        $updatedCount++;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Possible11 API Error [status={$st}]: " . $e->getMessage());
                $errors[] = "Error syncing {$st}: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'total_fetched' => $totalFetched,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'status_synced' => $status,
            'errors' => $errors,
            'message' => "Successfully synced {$totalFetched} series from Possible11 API ({$createdCount} new added, {$updatedCount} updated)."
        ];
    }

    /**
     * Upsert a single Possible11 series data into tournaments table
     */
    protected function upsertSeriesItem(array $item, string $apiStatus, int $adminUserId): string
    {
        $name = trim($item['name'] ?? '');
        if (empty($name)) {
            return 'skipped';
        }

        // Generate clean URL slug
        $urlSlug = '';
        if (!empty($item['url'])) {
            $urlSlug = trim(str_replace(['/series/', '/'], ['', ''], $item['url']));
        }
        if (empty($urlSlug)) {
            $urlSlug = Str::slug($name);
        }

        // Format conversion (2 => T20, 3 => ODI, 4 => TEST)
        $rawFormats = (string)($item['formats'] ?? '');
        $formatList = [];
        $parts = explode(',', $rawFormats);
        foreach ($parts as $p) {
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
        } elseif (stripos($catRaw, 'International') !== false) {
            $category = 'International';
        }

        // Overs estimation
        $overs = 20;
        if ($primaryFormat === 'ODI') {
            $overs = 50;
        } elseif ($primaryFormat === 'TEST') {
            $overs = 90;
        }

        $host = trim($item['host'] ?? '');
        $description = trim($item['description'] ?? '');
        $nameHi = trim($item['name_hi'] ?? '');
        $broadcaster = trim($item['broadcaster'] ?? '');
        $organizer = trim($item['organizer'] ?? '');
        $broadcastUrl = trim($item['broadcast_url'] ?? '');

        $fullDesc = $description;
        if (!empty($organizer)) {
            $fullDesc .= "\n\nOrganized by: " . $organizer;
        }
        if (!empty($broadcaster)) {
            $fullDesc .= "\nOfficial Broadcaster: " . $broadcaster . ($broadcastUrl ? " (" . $broadcastUrl . ")" : "");
        }

        $metaDesc = (!empty($nameHi) ? $nameHi . ' - ' : '') . ($description ?: $name);

        // Find existing tournament by slug, name, or short_name
        $tournament = Tournament::where('slug', $urlSlug)
            ->orWhere('name', $name)
            ->first();

        $statusToSave = ($apiStatus === 'live') ? 'ongoing' : 'upcoming';

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
            'venue' => $host ?: 'International Grounds',
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
            return 'updated';
        } else {
            $dataToSave['user_id'] = $adminUserId;
            $dataToSave['display_order'] = 1;
            $dataToSave['created_at'] = now();
            Tournament::create($dataToSave);
            return 'created';
        }
    }
}
