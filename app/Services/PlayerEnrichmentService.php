<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerBattingStat;
use App\Models\PlayerBowlingStat;
use App\Models\BallByBall;

class PlayerEnrichmentService
{
    /**
     * Enrich or sanitize player instance. Strictly returns player without adding synthetic details.
     */
    public static function enrichPlayer(Player $player): Player
    {
        return $player;
    }

    /**
     * Get multi-format calculated statistics from database for a player
     * Formats: all, ipl, t20i, odi, test, t20, t10
     */
    public static function getPlayerFormatCareerStats(Player $player, array $overallDbStats): array
    {
        $pName = trim($player->name);

        $battingAll = PlayerBattingStat::where(function($q) use ($pName) {
            $q->where('player_name', $pName)->orWhere('player_name', 'LIKE', '%' . $pName . '%');
        })->with('match.tournament')->get();

        $bowlingAll = PlayerBowlingStat::where(function($q) use ($pName) {
            $q->where('player_name', $pName)->orWhere('player_name', 'LIKE', '%' . $pName . '%');
        })->with('match.tournament')->get();

        $formatKeys = ['all', 'ipl', 'test', 'odi', 't20', 't10', 't20i'];
        $result = [];

        foreach ($formatKeys as $fmt) {
            if ($fmt === 'all') {
                $result['all'] = [
                    'matches_count' => (int)($overallDbStats['matches_count'] ?? 0),
                    'total_runs' => (int)($overallDbStats['runs'] ?? ($overallDbStats['total_runs'] ?? 0)),
                    'total_balls' => (int)($overallDbStats['balls'] ?? ($overallDbStats['total_balls'] ?? 0)),
                    'highest_score' => (int)($overallDbStats['highest'] ?? ($overallDbStats['highest_score'] ?? 0)),
                    'strike_rate' => $overallDbStats['strike_rate'] ?? ($overallDbStats['strikeRate'] ?? '0.00'),
                    'batting_avg' => $overallDbStats['average'] ?? ($overallDbStats['batting_avg'] ?? '0.00'),
                    'fours' => (int)($overallDbStats['fours'] ?? 0),
                    'sixes' => (int)($overallDbStats['sixes'] ?? 0),
                    'fifties' => (int)($overallDbStats['fifties'] ?? 0),
                    'hundreds' => (int)($overallDbStats['hundreds'] ?? 0),
                    'total_overs' => $overallDbStats['overs'] ?? ($overallDbStats['total_overs'] ?? '0.0'),
                    'wickets' => (int)($overallDbStats['wickets'] ?? 0),
                    'best_bowling_figures' => $overallDbStats['best_bowling'] ?? ($overallDbStats['best_bowling_figures'] ?? '-'),
                    'bowling_economy' => $overallDbStats['economy'] ?? ($overallDbStats['bowling_economy'] ?? '0.00'),
                    'bowling_avg' => $overallDbStats['bowlingAvg'] ?? ($overallDbStats['bowling_avg'] ?? '-'),
                    'maidens' => (int)($overallDbStats['maidens'] ?? 0)
                ];
                continue;
            }

            // Filter DB records for specific format
            $fmtBatting = $battingAll->filter(function($b) use ($fmt) {
                $tourn = $b->match?->tournament;
                $f = strtolower(trim(($tourn?->format ?? '') . ' ' . ($tourn?->name ?? '') . ' ' . ($tourn?->category ?? '')));
                if ($fmt === 'ipl') {
                    return str_contains($f, 'ipl') || str_contains($f, 'premier league');
                }
                if ($fmt === 't20i') {
                    return str_contains($f, 't20i') || (str_contains($f, 't20') && str_contains($f, 'international'));
                }
                if ($fmt === 't20') {
                    return str_contains($f, 't20') && !str_contains($f, 't20i');
                }
                return str_contains($f, $fmt);
            });

            $fmtBowling = $bowlingAll->filter(function($b) use ($fmt) {
                $tourn = $b->match?->tournament;
                $f = strtolower(trim(($tourn?->format ?? '') . ' ' . ($tourn?->name ?? '') . ' ' . ($tourn?->category ?? '')));
                if ($fmt === 'ipl') {
                    return str_contains($f, 'ipl') || str_contains($f, 'premier league');
                }
                if ($fmt === 't20i') {
                    return str_contains($f, 't20i') || (str_contains($f, 't20') && str_contains($f, 'international'));
                }
                if ($fmt === 't20') {
                    return str_contains($f, 't20') && !str_contains($f, 't20i');
                }
                return str_contains($f, $fmt);
            });

            if ($fmtBatting->isNotEmpty() || $fmtBowling->isNotEmpty()) {
                $totR = (int)$fmtBatting->sum('runs');
                $totB = (int)$fmtBatting->sum('balls');
                $tot4 = (int)$fmtBatting->sum('fours');
                $tot6 = (int)$fmtBatting->sum('sixes');
                $maxH = (int)$fmtBatting->max('runs');
                $tot50 = $fmtBatting->filter(fn($r) => $r->runs >= 50 && $r->runs < 100)->count();
                $tot100 = $fmtBatting->filter(fn($r) => $r->runs >= 100)->count();
                $mCount = $fmtBatting->pluck('match_id')->merge($fmtBowling->pluck('match_id'))->unique()->count();
                $outs = $fmtBatting->filter(fn($r) => !in_array(strtolower(trim($r->status_text ?? '')), ['not out', 'striker', 'non-striker', 'batting', '']))->count();
                $avg = $outs > 0 ? round($totR / $outs, 2) : ($totR > 0 ? $totR : 0.00);
                $sr = $totB > 0 ? round(($totR / $totB) * 100, 2) : 0.00;

                $totW = (int)$fmtBowling->sum('wickets');
                $totRunsBowled = (int)$fmtBowling->sum('runs');
                $totBallsBowled = 0;
                foreach ($fmtBowling as $r) {
                    $parts = explode('.', (string)$r->overs);
                    $totBallsBowled += ((int)($parts[0] ?? 0) * 6) + (int)($parts[1] ?? 0);
                }
                $totOvers = (float)(floor($totBallsBowled / 6) . '.' . ($totBallsBowled % 6));
                $econ = $totBallsBowled > 0 ? round(($totRunsBowled / max(1, ($totBallsBowled / 6))), 2) : 0.00;
                $bestRecord = $fmtBowling->sortByDesc('wickets')->sortBy('runs')->first();
                $bestB = $bestRecord ? "{$bestRecord->wickets}/{$bestRecord->runs}" : '-';

                $result[$fmt] = [
                    'matches_count' => $mCount,
                    'total_runs' => $totR,
                    'total_balls' => $totB,
                    'highest_score' => $maxH,
                    'strike_rate' => $sr,
                    'batting_avg' => $avg,
                    'fours' => $tot4,
                    'sixes' => $tot6,
                    'fifties' => $tot50,
                    'hundreds' => $tot100,
                    'total_overs' => $totOvers,
                    'wickets' => $totW,
                    'best_bowling_figures' => $bestB,
                    'bowling_economy' => $econ,
                    'bowling_avg' => $totW > 0 ? round($totRunsBowled / $totW, 2) : '-',
                    'maidens' => 0
                ];
            } else {
                $result[$fmt] = [
                    'matches_count' => 0,
                    'total_runs' => 0,
                    'total_balls' => 0,
                    'highest_score' => 0,
                    'strike_rate' => '0.00',
                    'batting_avg' => '0.00',
                    'fours' => 0,
                    'sixes' => 0,
                    'fifties' => 0,
                    'hundreds' => 0,
                    'total_overs' => '0.0',
                    'wickets' => 0,
                    'best_bowling_figures' => '-',
                    'bowling_economy' => '0.00',
                    'bowling_avg' => '-',
                    'maidens' => 0
                ];
            }
        }

        return $result;
    }
}
