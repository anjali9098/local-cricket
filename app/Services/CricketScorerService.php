<?php

namespace App\Services;

use App\Models\CricketMatch;
use App\Models\BallByBall;
use App\Models\PlayerBattingStat;
use App\Models\PlayerBowlingStat;
use App\Models\Player;
use App\Models\Team;

class CricketScorerService
{
    /**
     * Start innings and set up opening players
     */
    public static function startInnings($matchId, $strikerId = null, $nonStrikerId = null, $bowlerId = null, $inningsNum = 1)
    {
        $match = CricketMatch::with(['team1.players', 'team2.players', 'tournament'])->findOrFail($matchId);
        $match->status = 'live';
        $match->current_innings = $inningsNum;

        $battingTeam = $inningsNum == 1 ? $match->team1 : $match->team2;
        $bowlingTeam = $inningsNum == 1 ? $match->team2 : $match->team1;

        // If toss reversed the batting order
        if (!empty($match->result_text) && str_starts_with($match->result_text, 'toss:')) {
            $parts = explode(':', $match->result_text);
            $tossWinnerId = (int)($parts[1] ?? 0);
            $decision = $parts[2] ?? 'bat';
            
            $team1BattedFirst = ($decision === 'bat' && $tossWinnerId == $match->team1_id) || ($decision === 'field' && $tossWinnerId == $match->team2_id);
            if (!$team1BattedFirst) {
                $battingTeam = $inningsNum == 1 ? $match->team2 : $match->team1;
                $bowlingTeam = $inningsNum == 1 ? $match->team1 : $match->team2;
            }
        }

        // 1. Striker
        $striker = null;
        if ($strikerId) {
            $striker = Player::find($strikerId);
        }
        if (!$striker && $battingTeam && $battingTeam->players->isNotEmpty()) {
            $striker = $battingTeam->players->first();
        }

        // 2. Non-striker
        $nonStriker = null;
        if ($nonStrikerId) {
            $nonStriker = Player::find($nonStrikerId);
        }
        if (!$nonStriker && $battingTeam && $battingTeam->players->count() > 1) {
            $nonStriker = $battingTeam->players->skip(1)->first();
        }

        // 3. Bowler
        $bowler = null;
        if ($bowlerId) {
            $bowler = Player::find($bowlerId);
        }
        if (!$bowler && $bowlingTeam && $bowlingTeam->players->isNotEmpty()) {
            $bowler = $bowlingTeam->players->first();
        }

        if ($striker) {
            PlayerBattingStat::updateOrCreate(
                ['match_id' => $match->id, 'player_name' => $striker->name],
                [
                    'runs' => 0,
                    'balls' => 0,
                    'fours' => 0,
                    'sixes' => 0,
                    'strike_rate' => 0.00,
                    'status_text' => 'striker'
                ]
            );
        }

        if ($nonStriker) {
            PlayerBattingStat::updateOrCreate(
                ['match_id' => $match->id, 'player_name' => $nonStriker->name],
                [
                    'runs' => 0,
                    'balls' => 0,
                    'fours' => 0,
                    'sixes' => 0,
                    'strike_rate' => 0.00,
                    'status_text' => 'non-striker'
                ]
            );
        }

        if ($bowler) {
            PlayerBowlingStat::firstOrCreate(
                ['match_id' => $match->id, 'player_name' => $bowler->name],
                [
                    'overs' => 0.0,
                    'runs' => 0,
                    'wickets' => 0,
                    'economy' => 0.00
                ]
            );
        }

        $match->custom_note = $inningsNum == 1 ? "Innings 1 in progress" : "Innings 2 in progress (Target: " . ($match->team1_score + 1) . ")";
        $match->save();

        return $match;
    }

    /**
     * Record a single ball with full stats calculation, strike rotation, and match result logic
     */
    public static function recordBall($matchId, $runs = 0, $extras = null, $isWicket = false, $strikerName = null, $nonStrikerName = null, $bowlerName = null, $newBatsmanName = null)
    {
        $match = CricketMatch::with(['team1.players', 'team2.players', 'tournament', 'battingStats', 'bowlingStats'])->findOrFail($matchId);
        
        $maxOvers = (int)($match->tournament->overs ?? ($match->match_type === 'T10' ? 10 : ($match->match_type === 'ODI' ? 50 : 20)));
        if ($maxOvers <= 0) $maxOvers = 20;

        $runs = (int)$runs;
        $isExtra = in_array($extras, ['wide', 'no_ball']);
        $isBye = in_array($extras, ['bye_1', 'leg_bye_1', 'bye', 'leg_bye']);
        
        if ($isExtra) {
            $runsToAdd = $runs + 1; // 1 penalty run for wide/no ball + any runs scored
        } elseif ($isBye) {
            $runsToAdd = max(1, $runs); // Byes/Leg byes total runs
        } else {
            $runsToAdd = $runs;
        }

        // Determine batting and bowling team
        $battingTeam = $match->current_innings == 1 ? $match->team1 : $match->team2;
        $bowlingTeam = $match->current_innings == 1 ? $match->team2 : $match->team1;

        if (!empty($match->result_text) && str_starts_with($match->result_text, 'toss:')) {
            $parts = explode(':', $match->result_text);
            $tossWinnerId = (int)($parts[1] ?? 0);
            $decision = $parts[2] ?? 'bat';
            $team1BattedFirst = ($decision === 'bat' && $tossWinnerId == $match->team1_id) || ($decision === 'field' && $tossWinnerId == $match->team2_id);
            if (!$team1BattedFirst) {
                $battingTeam = $match->current_innings == 1 ? $match->team2 : $match->team1;
                $bowlingTeam = $match->current_innings == 1 ? $match->team1 : $match->team2;
            }
        }

        // 1. Resolve Striker
        $strikerStat = null;
        if (!empty($strikerName)) {
            $strikerStat = PlayerBattingStat::where('match_id', $match->id)->where('player_name', $strikerName)->first();
        }
        if (!$strikerStat) {
            $strikerStat = PlayerBattingStat::where('match_id', $match->id)->where('status_text', 'striker')->first();
        }
        if (!$strikerStat) {
            $strikerStat = PlayerBattingStat::where('match_id', $match->id)->whereIn('status_text', ['not out', 'not out *'])->first();
        }
        if (!$strikerStat && $battingTeam && $battingTeam->players->isNotEmpty()) {
            $pName = $battingTeam->players->first()->name;
            $strikerStat = PlayerBattingStat::firstOrCreate(
                ['match_id' => $match->id, 'player_name' => $pName],
                ['runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'strike_rate' => 0.00, 'status_text' => 'striker']
            );
        }

        // 2. Resolve Non-Striker
        $nonStrikerStat = null;
        if (!empty($nonStrikerName)) {
            $nonStrikerStat = PlayerBattingStat::where('match_id', $match->id)->where('player_name', $nonStrikerName)->first();
        }
        if (!$nonStrikerStat) {
            $nonStrikerStat = PlayerBattingStat::where('match_id', $match->id)->where('status_text', 'non-striker')->first();
        }
        if (!$nonStrikerStat && $strikerStat) {
            $nonStrikerStat = PlayerBattingStat::where('match_id', $match->id)
                ->where('id', '!=', $strikerStat->id)
                ->whereIn('status_text', ['not out', 'not out *', 'non-striker'])
                ->first();
        }
        if (!$nonStrikerStat && $battingTeam && $battingTeam->players->count() > 1) {
            $pName = $battingTeam->players->skip(1)->first()->name;
            $nonStrikerStat = PlayerBattingStat::firstOrCreate(
                ['match_id' => $match->id, 'player_name' => $pName],
                ['runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'strike_rate' => 0.00, 'status_text' => 'non-striker']
            );
        }

        // 3. Resolve Bowler
        $bowlerStat = null;
        if (!empty($bowlerName)) {
            $bowlerStat = PlayerBowlingStat::where('match_id', $match->id)->where('player_name', $bowlerName)->first();
            if (!$bowlerStat) {
                $bowlerStat = PlayerBowlingStat::create([
                    'match_id' => $match->id,
                    'player_name' => $bowlerName,
                    'overs' => 0.0,
                    'runs' => 0,
                    'wickets' => 0,
                    'economy' => 0.00
                ]);
            }
        }
        if (!$bowlerStat) {
            $bowlerStat = PlayerBowlingStat::where('match_id', $match->id)->orderBy('id', 'desc')->first();
        }
        if (!$bowlerStat && $bowlingTeam && $bowlingTeam->players->isNotEmpty()) {
            $pName = $bowlingTeam->players->first()->name;
            $bowlerStat = PlayerBowlingStat::firstOrCreate(
                ['match_id' => $match->id, 'player_name' => $pName],
                ['overs' => 0.0, 'runs' => 0, 'wickets' => 0, 'economy' => 0.00]
            );
        }

        // Advance Match Team Score & Overs
        $isOverComplete = false;
        $deliveryOverNum = '0.1';
        if ($match->current_innings == 1) {
            $match->team1_score += $runsToAdd;
            if ($isWicket) {
                $match->team1_wickets += 1;
            }
            if (!$isExtra) {
                $oversStr = (string)$match->team1_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;
                
                $balls++;
                if ($balls >= 6) {
                    $compOvers++;
                    $balls = 0;
                    $isOverComplete = true;
                    $deliveryOverNum = ($compOvers - 1) . '.6';
                } else {
                    $deliveryOverNum = $compOvers . '.' . $balls;
                }
                $match->team1_overs = (float)($compOvers . '.' . $balls);
            } else {
                $oversStr = (string)$match->team1_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;
                $deliveryOverNum = $compOvers . '.' . max(1, $balls) . ' (wd/nb)';
            }
        } else {
            $match->team2_score += $runsToAdd;
            if ($isWicket) {
                $match->team2_wickets += 1;
            }
            if (!$isExtra) {
                $oversStr = (string)$match->team2_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;
                
                $balls++;
                if ($balls >= 6) {
                    $compOvers++;
                    $balls = 0;
                    $isOverComplete = true;
                    $deliveryOverNum = ($compOvers - 1) . '.6';
                } else {
                    $deliveryOverNum = $compOvers . '.' . $balls;
                }
                $match->team2_overs = (float)($compOvers . '.' . $balls);
            } else {
                $oversStr = (string)$match->team2_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;
                $deliveryOverNum = $compOvers . '.' . max(1, $balls) . ' (wd/nb)';
            }
        }

        // Advance Batsman Stat
        if ($strikerStat) {
            // Legal delivery or No Ball adds to batsman balls faced (Wide does not)
            if ($extras !== 'wide') {
                $strikerStat->balls += 1;
            }

            // Runs off the bat (not byes, not wide)
            if (!$isBye && $extras !== 'wide') {
                $strikerStat->runs += $runs;
                if ($runs == 4) $strikerStat->fours += 1;
                if ($runs == 6) $strikerStat->sixes += 1;
            }

            $strikerStat->strike_rate = $strikerStat->balls > 0 ? round(($strikerStat->runs / $strikerStat->balls) * 100, 2) : 0.00;

            if ($isWicket) {
                $strikerStat->status_text = 'b ' . ($bowlerStat ? $bowlerStat->player_name : 'Bowler');
                $strikerStat->save();

                // Bring in new batsman if available
                $usedPlayerNames = PlayerBattingStat::where('match_id', $match->id)->pluck('player_name')->toArray();
                $nextPlayer = null;
                if (!empty($newBatsmanName)) {
                    $nextPlayerName = $newBatsmanName;
                } else {
                    $nextPlayer = $battingTeam ? $battingTeam->players->whereNotIn('name', $usedPlayerNames)->first() : null;
                    $nextPlayerName = $nextPlayer ? $nextPlayer->name : null;
                }

                if (!empty($nextPlayerName)) {
                    $strikerStat = PlayerBattingStat::create([
                        'match_id' => $match->id,
                        'player_name' => $nextPlayerName,
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'strike_rate' => 0.00,
                        'status_text' => 'striker'
                    ]);
                }
            } else {
                $strikerStat->status_text = 'striker';
                $strikerStat->save();
            }
        }

        // Advance Bowler Stat
        if ($bowlerStat) {
            if (!$isExtra) {
                // Calculate bowler total balls bowled
                $bOversStr = (string)$bowlerStat->overs;
                $bParts = explode('.', $bOversStr);
                $bComp = (int)$bParts[0];
                $bBalls = isset($bParts[1]) ? (int)$bParts[1] : 0;

                $bBalls++;
                if ($bBalls >= 6) {
                    $bComp++;
                    $bBalls = 0;
                }
                $bowlerStat->overs = (float)($bComp . '.' . $bBalls);
            }

            // Bowler runs conceded
            $bowlerRunsConceded = 0;
            if ($isExtra) {
                $bowlerRunsConceded = 1 + $runs;
            } elseif (!$isBye) {
                $bowlerRunsConceded = $runs;
            }
            $bowlerStat->runs += $bowlerRunsConceded;

            if ($isWicket) {
                $bowlerStat->wickets += 1;
            }

            $totalBowlerBalls = floor($bowlerStat->overs) * 6 + round(($bowlerStat->overs - floor($bowlerStat->overs)) * 10);
            $bowlerEconOvers = $totalBowlerBalls / 6.0;
            $bowlerStat->economy = $bowlerEconOvers > 0 ? round($bowlerStat->runs / $bowlerEconOvers, 2) : 0.00;
            $bowlerStat->save();
        }

        // Strike Rotation
        // Rotate strike on odd runs (1, 3, 5) if no wicket
        if (!$isWicket && in_array($runs, [1, 3, 5]) && $strikerStat && $nonStrikerStat) {
            $strikerStat->status_text = 'non-striker';
            $nonStrikerStat->status_text = 'striker';
            $strikerStat->save();
            $nonStrikerStat->save();
        }

        // Rotate strike at end of over (6 balls)
        if ($isOverComplete && $strikerStat && $nonStrikerStat) {
            $curStriker = PlayerBattingStat::where('match_id', $match->id)->where('status_text', 'striker')->first();
            $curNonStriker = PlayerBattingStat::where('match_id', $match->id)->where('status_text', 'non-striker')->first();
            if ($curStriker && $curNonStriker) {
                $curStriker->status_text = 'non-striker';
                $curNonStriker->status_text = 'striker';
                $curStriker->save();
                $curNonStriker->save();
            }
        }

        // Determine outcome string for BallByBall
        if ($isWicket) {
            $outcome = 'W';
        } elseif ($isExtra) {
            $extraName = $extras === 'wide' ? 'Wide' : 'No ball';
            $outcome = $runs > 0 ? ($extraName . '+' . $runs) : $extraName;
        } elseif ($isBye) {
            $byeType = str_contains($extras, 'leg') ? 'Leg Bye' : 'Bye';
            $byeRuns = max(1, $runs);
            $outcome = $byeType . ' ' . $byeRuns;
        } else {
            $outcome = $runs > 0 ? (string)$runs : '0';
        }

        // Save Ball By Ball with exact delivery number
        BallByBall::create([
            'match_id' => $match->id,
            'over_num' => $deliveryOverNum,
            'outcome' => $outcome,
            'bowler_name' => $bowlerStat ? $bowlerStat->player_name : 'Bowler',
            'batsman_name' => $strikerStat ? $strikerStat->player_name : 'Batsman',
            'created_at' => now()
        ]);

        // Innings & Match Completion Evaluation
        $target = $match->team1_score + 1;

        if ($match->current_innings == 1) {
            $currOvers = (float)$match->team1_overs;
            $compOvers = floor($currOvers);
            if ($compOvers >= $maxOvers || $match->team1_wickets >= 10) {
                // Innings 1 is Complete -> Switch to Innings 2!
                $match->current_innings = 2;
                $match->custom_note = "Innings Break: {$match->team2->name} need {$target} runs in {$maxOvers} overs";
            } else {
                $crr = $currOvers > 0 ? round($match->team1_score / ((floor($currOvers) * 6 + round(($currOvers - floor($currOvers)) * 10)) / 6.0), 2) : 0.00;
                $match->custom_note = "{$match->team1->name} batting (CRR: {$crr})";
            }
        } elseif ($match->current_innings == 2) {
            if ($match->team2_score >= $target) {
                // Team 2 Won!
                $remWickets = max(1, 10 - $match->team2_wickets);
                $match->status = 'completed';
                $match->result_text = "{$match->team2->name} won by {$remWickets} wickets";
                $match->custom_note = $match->result_text;
            } else {
                $currOvers = (float)$match->team2_overs;
                $compOvers = floor($currOvers);
                if ($compOvers >= $maxOvers || $match->team2_wickets >= 10) {
                    // Match Over!
                    $match->status = 'completed';
                    if ($match->team2_score < $match->team1_score) {
                        $runDiff = $match->team1_score - $match->team2_score;
                        $match->result_text = "{$match->team1->name} won by {$runDiff} runs";
                    } else {
                        $match->result_text = "Match Tied!";
                    }
                    $match->custom_note = $match->result_text;
                } else {
                    $runsNeeded = max(0, $target - $match->team2_score);
                    $ballsBowled = floor($currOvers) * 6 + round(($currOvers - floor($currOvers)) * 10);
                    $ballsLeft = max(0, ($maxOvers * 6) - $ballsBowled);
                    $rrr = $ballsLeft > 0 ? round(($runsNeeded / $ballsLeft) * 6, 2) : 0.00;
                    $match->custom_note = "{$match->team2->name} need {$runsNeeded} runs in {$ballsLeft} balls (RRR: {$rrr})";
                }
            }
        }

        $match->save();

        return $match;
    }

    /**
     * Undo the last ball and revert all batsman/bowler stats
     */
    public static function undoBall($matchId)
    {
        $match = CricketMatch::with(['battingStats', 'bowlingStats'])->findOrFail($matchId);
        $lastBall = BallByBall::where('match_id', $match->id)->orderBy('id', 'desc')->first();

        if (!$lastBall) {
            return false;
        }

        $outcome = $lastBall->outcome;
        $isWicket = ($outcome === 'W');
        $isExtra = (str_contains($outcome, 'Wide') || str_contains($outcome, 'No ball'));
        $isBye = str_contains($outcome, 'Bye');
        
        $runsToDeduct = 0;
        if ($isExtra) {
            $runsToDeduct = 1;
            if (str_contains($outcome, '+')) {
                $parts = explode('+', $outcome);
                $runsToDeduct += (int)($parts[1] ?? 0);
            }
        } elseif ($isBye) {
            $runsToDeduct = 1;
            if (preg_match('/(\d+)/', $outcome, $matches)) {
                $runsToDeduct = (int)$matches[1];
            }
        } elseif ($outcome === '0' || $outcome === 'Dot ball' || $outcome === 'W') {
            $runsToDeduct = 0;
        } else {
            $runsToDeduct = (int)$outcome;
        }

        // Revert Match Score and Overs
        if ($match->current_innings == 1) {
            $match->team1_score = max(0, $match->team1_score - $runsToDeduct);
            if ($isWicket) $match->team1_wickets = max(0, $match->team1_wickets - 1);

            if (!$isExtra) {
                $oversStr = (string)$match->team1_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;

                if ($balls == 0) {
                    if ($compOvers > 0) {
                        $compOvers--;
                        $balls = 5;
                    }
                } else {
                    $balls--;
                }
                $match->team1_overs = (float)($compOvers . '.' . $balls);
            }
        } else {
            $match->team2_score = max(0, $match->team2_score - $runsToDeduct);
            if ($isWicket) $match->team2_wickets = max(0, $match->team2_wickets - 1);

            if (!$isExtra) {
                $oversStr = (string)$match->team2_overs;
                $parts = explode('.', $oversStr);
                $compOvers = (int)$parts[0];
                $balls = isset($parts[1]) ? (int)$parts[1] : 0;

                if ($balls == 0) {
                    if ($compOvers > 0) {
                        $compOvers--;
                        $balls = 5;
                    }
                } else {
                    $balls--;
                }
                $match->team2_overs = (float)($compOvers . '.' . $balls);
            }
        }

        // Revert Batsman Stat
        $strikerStat = PlayerBattingStat::where('match_id', $match->id)->where('player_name', $lastBall->batsman_name)->first();
        if ($strikerStat) {
            if (!$isExtra || str_contains($outcome, 'No ball')) {
                $strikerStat->balls = max(0, $strikerStat->balls - 1);
            }
            if (!$isBye && !str_contains($outcome, 'Wide')) {
                $batsmanRuns = $runsToDeduct;
                if (str_contains($outcome, 'No ball+')) {
                    $parts = explode('+', $outcome);
                    $batsmanRuns = (int)($parts[1] ?? 0);
                } elseif ($outcome === 'No ball') {
                    $batsmanRuns = 0;
                }
                $strikerStat->runs = max(0, $strikerStat->runs - $batsmanRuns);
                if ($batsmanRuns == 4) $strikerStat->fours = max(0, $strikerStat->fours - 1);
                if ($batsmanRuns == 6) $strikerStat->sixes = max(0, $strikerStat->sixes - 1);
            }
            $strikerStat->strike_rate = $strikerStat->balls > 0 ? round(($strikerStat->runs / $strikerStat->balls) * 100, 2) : 0.00;
            if ($isWicket) {
                $strikerStat->status_text = 'striker';
            }
            $strikerStat->save();
        }

        // Revert Bowler Stat
        $bowlerStat = PlayerBowlingStat::where('match_id', $match->id)->where('player_name', $lastBall->bowler_name)->first();
        if ($bowlerStat) {
            if (!$isExtra) {
                $bOversStr = (string)$bowlerStat->overs;
                $bParts = explode('.', $bOversStr);
                $bComp = (int)$bParts[0];
                $bBalls = isset($bParts[1]) ? (int)$bParts[1] : 0;

                if ($bBalls == 0) {
                    if ($bComp > 0) {
                        $bComp--;
                        $bBalls = 5;
                    }
                } else {
                    $bBalls--;
                }
                $bowlerStat->overs = (float)($bComp . '.' . $bBalls);
            }

            if (!$isBye) {
                $bowlerStat->runs = max(0, $bowlerStat->runs - $runsToDeduct);
            }
            if ($isWicket) {
                $bowlerStat->wickets = max(0, $bowlerStat->wickets - 1);
            }
            $totalBowlerBalls = floor($bowlerStat->overs) * 6 + round(($bowlerStat->overs - floor($bowlerStat->overs)) * 10);
            $bowlerEconOvers = $totalBowlerBalls / 6.0;
            $bowlerStat->economy = $bowlerEconOvers > 0 ? round($bowlerStat->runs / $bowlerEconOvers, 2) : 0.00;
            $bowlerStat->save();
        }

        // If match was completed, bring it back to live
        if ($match->status === 'completed') {
            $match->status = 'live';
            $match->result_text = null;
        }

        $lastBall->delete();
        $match->save();

        return true;
    }
}
