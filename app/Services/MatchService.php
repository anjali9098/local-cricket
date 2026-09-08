<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\PlayerBattingStat;

class MatchService
{
    /**
     * Get calculated stats for a match.
     */
    public function getCalculatedStats(CricketMatch $match): array
    {
        $crr = 0.0;
        $rrr = 0.0;
        $maxOvers = $match->match_type === 'ODI' ? 50.0 : ($match->match_type === 'Test' ? 90.0 : 20.0);

        // 1. Calculate Current Run Rate (CRR)
        if ($match->current_innings === 1) {
            if ($match->team1_overs > 0) {
                $crr = round($match->team1_score / (float)$match->team1_overs, 2);
            }
        } else {
            if ($match->team2_overs > 0) {
                $crr = round($match->team2_score / (float)$match->team2_overs, 2);
            }
        }

        // 2. Calculate Required Run Rate (RRR)
        if ($match->current_innings === 2 && $match->status === 'live') {
            $target = $match->team1_score + 1;
            $runsNeeded = $target - $match->team2_score;
            $oversRemaining = $maxOvers - (float)$match->team2_overs;

            if ($oversRemaining > 0 && $runsNeeded > 0) {
                $rrr = round($runsNeeded / $oversRemaining, 2);
            }
        }

        // 3. Calculate Win Probability
        $prob = $this->calculateWinProbability($match, $maxOvers);

        // 4. Calculate Active Partnership
        $partnership = $this->calculateActivePartnership($match);

        return [
            'crr' => $crr,
            'rrr' => $rrr,
            'win_probability' => $prob,
            'partnership' => $partnership,
            'overs_left' => $match->status === 'live' ? max(0, round($maxOvers - ($match->current_innings === 1 ? $match->team1_overs : $match->team2_overs), 1)) : 0.0
        ];
    }

    /**
     * Win Probability Algorithm.
     */
    private function calculateWinProbability(CricketMatch $match, float $maxOvers): array
    {
        if ($match->status !== 'live') {
            if ($match->status === 'completed') {
                $winnerId = $match->winner_id;
                if ($winnerId == $match->team1_id) {
                    return ['team1' => 100, 'team2' => 0, 'draw' => 0];
                } elseif ($winnerId == $match->team2_id) {
                    return ['team1' => 0, 'team2' => 100, 'draw' => 0];
                }
            }
            return ['team1' => 50, 'team2' => 50, 'draw' => 0];
        }

        $team1Prob = 50;
        $team2Prob = 50;
        $drawProb = 0;

        if ($match->match_type === 'Test') {
            $drawProb = 20; // Tests can end in draw
        }

        if ($match->current_innings === 1) {
            // Team 1 batting
            $score = $match->team1_score;
            $wickets = $match->team1_wickets;
            $overs = $match->team1_overs;

            if ($overs > 0) {
                $projectedScore = ($score / $overs) * $maxOvers;
                $parScore = $match->match_type === 'ODI' ? 260 : 160;
                
                // Adjustment based on wickets down
                $wicketPenalty = $wickets * 4;
                $strength = $projectedScore - $parScore - $wicketPenalty;

                $team1Prob = 50 + ($strength / 6);
            }
        } else {
            // Team 2 batting (chasing)
            $target = $match->team1_score + 1;
            $score = $match->team2_score;
            $wickets = $match->team2_wickets;
            $overs = $match->team2_overs;

            $runsNeeded = $target - $score;
            $oversFloat = (float)$overs;
            $oversRemaining = max(0.1, $maxOvers - $oversFloat);
            $wicketsRemaining = 10 - $wickets;

            if ($runsNeeded <= 0) {
                return ['team1' => 0, 'team2' => 100, 'draw' => 0];
            }
            if ($wicketsRemaining <= 0 || $oversRemaining <= 0.1) {
                return ['team1' => 100, 'team2' => 0, 'draw' => 0];
            }

            $reqRunRate = $runsNeeded / $oversRemaining;
            $curRunRate = $oversFloat > 0 ? ($score / $oversFloat) : 0.0;

            // Chaser strength formula
            $rrrFactor = ($reqRunRate - 7.0) * 12; // Higher RRR hurts chaser
            $wicketFactor = ($wickets) * 10;       // Wickets down hurts chaser
            
            $chaserStrength = 50 - $rrrFactor - $wicketFactor;
            $team2Prob = max(5, min(95, $chaserStrength));
            $team1Prob = 100 - $team2Prob - $drawProb;
        }

        // Bound check
        $team1Prob = max(5, min(95 - $drawProb, round($team1Prob)));
        $team2Prob = 100 - $team1Prob - $drawProb;

        return [
            'team1' => (int)$team1Prob,
            'team2' => (int)$team2Prob,
            'draw' => (int)$drawProb
        ];
    }

    /**
     * Calculate partnership runs and balls based on currently active (not out) batting stats.
     */
    private function calculateActivePartnership(CricketMatch $match): array
    {
        $activeBatters = PlayerBattingStat::where('match_id', $match->id)
            ->where('status_text', 'not out')
            ->take(2)
            ->get();

        $runs = 0;
        $balls = 0;
        $names = [];

        foreach ($activeBatters as $b) {
            $runs += $b->runs;
            $balls += $b->balls;
            $names[] = $b->player_name;
        }

        return [
            'runs' => $runs,
            'balls' => $balls,
            'batsmen' => implode(' & ', $names) ?: 'No active partnership'
        ];
    }
}
