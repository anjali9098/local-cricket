<?php

namespace App\Services;

use App\Models\HeadToHead;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HeadToHeadScraperService
{
    protected Client $client;

    protected array $formatUrls = [
        'TEST' => 'https://www.mykhel.com/cricket/head-to-head-records-in-test/',
        'ODI' => 'https://www.mykhel.com/cricket/head-to-head-records-in-odi/',
        'T20' => 'https://www.mykhel.com/cricket/head-to-head-records-in-t20/',
    ];

    protected array $defaultFlags = [
        'india' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/IND-CR1@2x.png',
        'australia' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/AUS-CR1@2x.png',
        'england' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/ENG-CR1@2x.png',
        'south africa' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/RSA-CR1@2x.png',
        'new zealand' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/NZ-CR1@2x.png',
        'pakistan' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/PAK-CR1@2x.png',
        'sri lanka' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SRI-CR1@2x.png',
        'west indies' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/WIN-CR3@2x.png',
        'bangladesh' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/BAN-CR1@2x.png',
        'afghanistan' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/AFGH-CR1@2x.png',
        'zimbabwe' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/ZIM-CR1@2x.png',
        'ireland' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/IRE-CR1@2x.png',
        'netherlands' => 'https://flagcdn.com/w80/nl.png',
        'scotland' => 'https://flagcdn.com/w80/gb-sct.png',
        'nepal' => 'https://flagcdn.com/w80/np.png',
        'usa' => 'https://flagcdn.com/w80/us.png',
        'united states' => 'https://flagcdn.com/w80/us.png',
        'uae' => 'https://flagcdn.com/w80/ae.png',
        'namibia' => 'https://flagcdn.com/w80/na.png',
        'canada' => 'https://flagcdn.com/w80/ca.png',
        'oman' => 'https://flagcdn.com/w80/om.png',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]
        ]);
    }

    /**
     * Alias for syncFromMyKhel to support crawlAndSync callers.
     */
    public function crawlAndSync(?string $requestedFormat = 'all', int $limitPerFormat = 0, ?callable $onProgress = null): array
    {
        return $this->syncFromMyKhel($requestedFormat, $limitPerFormat, $onProgress);
    }

    /**
     * Sync Head to Head records from MyKhel across formats using loop engineering.
     */
    public function syncFromMyKhel(?string $requestedFormat = 'all', int $limitPerFormat = 0, ?callable $onProgress = null): array
    {
        $formats = [];
        $reqUpper = strtoupper(trim((string)$requestedFormat));
        if ($reqUpper === 'ALL' || empty($reqUpper)) {
            $formats = ['TEST', 'ODI', 'T20'];
        } elseif (isset($this->formatUrls[$reqUpper])) {
            $formats = [$reqUpper];
        } else {
            $formats = ['TEST'];
        }

        $totalSynced = 0;
        $totalErrors = 0;
        $details = [];

        foreach ($formats as $fmt) {
            $url = $this->formatUrls[$fmt];
            if ($onProgress) {
                $onProgress("Starting crawl for format {$fmt} from {$url}...");
            }

            try {
                $response = $this->client->get($url);
                $html = (string) $response->getBody();

                $matchupLinks = $this->extractMatchupLinks($html, $fmt);
                if ($onProgress) {
                    $onProgress("Found " . count($matchupLinks) . " unique matchup pairs for {$fmt}.");
                }

                $count = 0;
                foreach ($matchupLinks as $matchupPath) {
                    if ($limitPerFormat > 0 && $count >= $limitPerFormat) {
                        break;
                    }

                    $fullUrl = 'https://www.mykhel.com' . $matchupPath;
                    
                    try {
                        $matchupRes = $this->client->get($fullUrl);
                        $matchupHtml = (string) $matchupRes->getBody();

                        $record = $this->parseAndSaveMatchup($matchupHtml, $fmt, $matchupPath);
                        if ($record) {
                            $totalSynced++;
                            $count++;
                            $details[] = "✓ [{$fmt}] {$record->team1_name} vs {$record->team2_name}: Total {$record->total_matches} (T1: {$record->team1_won}, T2: {$record->team2_won}, D: {$record->drawn}, T: {$record->tied})";
                            if ($onProgress) {
                                $onProgress("✓ [{$fmt}] ({$count}) {$record->team1_name} vs {$record->team2_name} synced.");
                            }
                        }
                    } catch (\Throwable $ex) {
                        $totalErrors++;
                        Log::warning("Failed to sync MyKhel H2H matchup [{$fullUrl}]: " . $ex->getMessage());
                        if ($onProgress) {
                            $onProgress("✗ Error scraping {$matchupPath}: " . $ex->getMessage());
                        }
                    }

                    // Polite crawling delay
                    usleep(120000); // 0.12s
                }
            } catch (\Throwable $e) {
                $totalErrors++;
                Log::error("Failed to fetch format index [{$url}]: " . $e->getMessage());
                if ($onProgress) {
                    $onProgress("✗ Failed to load format index {$fmt}: " . $e->getMessage());
                }
            }
        }

        return [
            'synced' => $totalSynced,
            'errors' => $totalErrors,
            'details' => $details,
        ];
    }

    /**
     * Extract deduplicated matchup links from a format page.
     */
    protected function extractMatchupLinks(string $html, string $fmt): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        $links = $xpath->query('//a[contains(@href, "-head-to-head-in-")]');
        $seenPairs = [];
        $uniquePaths = [];

        foreach ($links as $link) {
            $href = trim($link->getAttribute('href'));
            $path = parse_url($href, PHP_URL_PATH);
            if (!$path) continue;

            // Extract pair slug to deduplicate reciprocal links (e.g. aus-vs-ind and ind-vs-aus)
            if (preg_match('#/cricket/([a-z0-9\-]+)-vs-([a-z0-9\-]+)-head-to-head-in-#i', $path, $m)) {
                $t1Slug = strtolower(trim($m[1]));
                $t2Slug = strtolower(trim($m[2]));
                $pairKey = [$t1Slug, $t2Slug];
                sort($pairKey);
                $pairId = implode('_vs_', $pairKey);

                if (!isset($seenPairs[$pairId])) {
                    $seenPairs[$pairId] = true;
                    $uniquePaths[] = $path;
                }
            } else {
                if (!in_array($path, $uniquePaths)) {
                    $uniquePaths[] = $path;
                }
            }
        }

        return $uniquePaths;
    }

    /**
     * Parse an individual matchup page and save to HeadToHead table.
     */
    public function parseAndSaveMatchup(string $html, string $format, string $sourceUrl = ''): ?HeadToHead
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        // 1. Identify Team 1 and Team 2 from H1 or URL
        $h1Node = $xpath->query('//h1')->item(0);
        $h1Text = $h1Node ? trim($h1Node->textContent) : '';

        $team1Name = '';
        $team2Name = '';

        if (preg_match('/^(.*?)\s+vs\s+(.*?)\s+Head\s+to\s+Head/i', $h1Text, $m)) {
            $team1Name = trim($m[1]);
            $team2Name = trim($m[2]);
        } elseif (preg_match('#/cricket/([a-z0-9\-]+)-vs-([a-z0-9\-]+)-head-to-head-in-#i', $sourceUrl, $m)) {
            $team1Name = ucwords(str_replace('-', ' ', $m[1]));
            $team2Name = ucwords(str_replace('-', ' ', $m[2]));
        }

        if (empty($team1Name) || empty($team2Name)) {
            return null;
        }

        // 2. Parse Total Matches
        $totalMatches = 0;
        $tmNodes = $xpath->query('//*[contains(text(), "Matches")]/ancestor::div[1] | //div[contains(@class, "total-match")]');
        foreach ($tmNodes as $tm) {
            if (preg_match('/Matches\s*([0-9]+)/i', $tm->textContent, $matches)) {
                $totalMatches = (int) $matches[1];
                break;
            }
        }

        // 3. Parse Row Status Blocks (Won, Lost, Drawn, Tied, Home Won, Away Won, Neutral Won)
        $rows = $xpath->query('//div[contains(@class, "row")]');
        $t1Won = 0;
        $t2Won = 0;
        $drawn = 0;
        $tied = 0;
        $t1HomeWon = 0;
        $t2HomeWon = 0;
        $t1AwayWon = 0;
        $t2AwayWon = 0;
        $t1NeutralWon = 0;
        $t2NeutralWon = 0;

        foreach ($rows as $r) {
            $titleNode = $xpath->query('.//div[contains(@class, "title-center")]', $r)->item(0);
            $leftNode = $xpath->query('.//div[contains(@class, "head2head-os-left") and contains(@class, "played-box-num")]', $r)->item(0);
            $rightNode = $xpath->query('.//div[contains(@class, "head2head-os-right") and contains(@class, "played-box-num")]', $r)->item(0);

            if ($titleNode && $leftNode && $rightNode) {
                $metric = strtolower(trim($titleNode->textContent));
                $lVal = (int) trim($leftNode->textContent);
                $rVal = (int) trim($rightNode->textContent);

                if ($metric === 'won') {
                    $t1Won = $lVal;
                    $t2Won = $rVal;
                } elseif ($metric === 'drawn') {
                    $drawn = $lVal; // drawn is same for both
                } elseif ($metric === 'tied') {
                    $tied = $lVal;  // tied is same for both
                } elseif ($metric === 'home won') {
                    $t1HomeWon = $lVal;
                    $t2HomeWon = $rVal;
                } elseif ($metric === 'away won') {
                    $t1AwayWon = $lVal;
                    $t2AwayWon = $rVal;
                } elseif ($metric === 'neutral won') {
                    $t1NeutralWon = $lVal;
                    $t2NeutralWon = $rVal;
                }
            }
        }

        if ($totalMatches === 0) {
            $totalMatches = $t1Won + $t2Won + $drawn + $tied;
        }
        $noResult = max(0, $totalMatches - ($t1Won + $t2Won + $drawn + $tied));

        // 4. Parse Narrative description
        $narrNode = $xpath->query('//div[contains(@class, "head2head-content-container")]//div[contains(@class, "Head2head-desc")] | //div[contains(@class, "head2head-narrative")]')->item(0);
        $narrative = $narrNode ? trim(preg_replace('/\s+/', ' ', $narrNode->textContent)) : '';
        if (empty($narrative) && $totalMatches > 0) {
            $narrative = "{$team1Name} and {$team2Name} have faced each other in {$totalMatches} matches in {$format}. Out of these {$totalMatches} games, {$team1Name} have won {$t1Won} whereas {$team2Name} have won {$t2Won} occasions" . ($drawn > 0 ? ", with {$drawn} drawn" : "") . ($tied > 0 ? ", and {$tied} tied" : "") . ($noResult > 0 ? ", with {$noResult} matches ending without a result" : "") . ".";
        }

        // 5. Match or create Team models
        $t1Model = $this->resolveOrCreateTeam($team1Name);
        $t2Model = $this->resolveOrCreateTeam($team2Name);

        // 6. Upsert into database
        return HeadToHead::updateOrCreate(
            [
                'team1_name' => $team1Name,
                'team2_name' => $team2Name,
                'format' => strtoupper($format),
            ],
            [
                'team1_id' => $t1Model?->id,
                'team2_id' => $t2Model?->id,
                'total_matches' => $totalMatches,
                'team1_won' => $t1Won,
                'team2_won' => $t2Won,
                'drawn' => $drawn,
                'tied' => $tied,
                'no_result' => $noResult,
                'team1_home_won' => $t1HomeWon,
                'team2_home_won' => $t2HomeWon,
                'team1_away_won' => $t1AwayWon,
                'team2_away_won' => $t2AwayWon,
                'team1_neutral_won' => $t1NeutralWon,
                'team2_neutral_won' => $t2NeutralWon,
                'summary_narrative' => $narrative,
                'source' => 'official',
            ]
        );
    }

    /**
     * Resolve existing team or create new international team with official flag.
     */
    public function resolveOrCreateTeam(string $name): ?Team
    {
        $cleanName = trim($name);
        if (empty($cleanName)) return null;

        // 1. Exact match
        $team = Team::where('name', $cleanName)->first();
        if ($team) return $team;

        // 2. Name prefix match avoiding Women/Club teams
        $team = Team::where('name', 'like', "{$cleanName}%")
            ->where('name', 'not like', '%Women%')
            ->where('name', 'not like', '%Champions%')
            ->where('name', 'not like', '%Emerging%')
            ->where('name', 'not like', '%Indian%')
            ->first();
        if ($team) return $team;

        // 3. Create if missing
        $lower = strtolower($cleanName);
        $flag = $this->defaultFlags[$lower] ?? 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/IND-CR1@2x.png';
        $short = strtoupper(substr(str_replace(' ', '', $cleanName), 0, 3));

        return Team::create([
            'name' => $cleanName,
            'short_name' => $short,
            'slug' => Str::slug($cleanName),
            'country' => 'International',
            'logo' => $flag,
            'logo_url' => $flag,
            'color_code' => '#2563eb',
        ]);
    }
}
