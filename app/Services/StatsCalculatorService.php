<?php

namespace App\Services;

class StatsCalculatorService
{
    /**
     * Convert overs in decimal form (e.g. 3.4 for 3 overs 4 balls) to total balls.
     */
    public static function oversToBalls(float $overs): int
    {
        $wholeOvers = floor($overs);
        $balls = round(($overs - $wholeOvers) * 10);
        return ($wholeOvers * 6) + $balls;
    }

    /**
     * Convert total balls to decimal overs form (e.g. 22 balls to 3.4 overs).
     */
    public static function ballsToOvers(int $balls): float
    {
        $overs = floor($balls / 6);
        $remainingBalls = $balls % 6;
        return $overs + ($remainingBalls / 10.0);
    }

    /**
     * Calculate strike rate.
     */
    public static function calculateStrikeRate(int $runs, int $balls): float
    {
        if ($balls <= 0) {
            return 0.0;
        }
        return round(($runs / $balls) * 100, 2);
    }

    /**
     * Calculate bowling economy rate.
     */
    public static function calculateEconomy(int $runsConceded, float $overs): float
    {
        $balls = self::oversToBalls($overs);
        if ($balls <= 0) {
            return 0.0;
        }
        return round(($runsConceded / $balls) * 6, 2);
    }
}
