<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadToHead extends Model
{
    protected $table = 'head_to_heads';
    protected $guarded = [];

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    /**
     * Find head to head record between two teams in a format (handles reciprocal order).
     */
    public static function findMatchup($team1, $team2, string $format = 'TEST'): ?self
    {
        $t1Id = is_object($team1) ? $team1->id : (is_numeric($team1) ? (int)$team1 : null);
        $t2Id = is_object($team2) ? $team2->id : (is_numeric($team2) ? (int)$team2 : null);

        $t1Name = is_object($team1) ? $team1->name : (string)$team1;
        $t2Name = is_object($team2) ? $team2->name : (string)$team2;

        return self::where(function ($q) use ($t1Id, $t2Id, $t1Name, $t2Name) {
            $q->where(function ($sub) use ($t1Id, $t2Id, $t1Name, $t2Name) {
                if ($t1Id && $t2Id) {
                    $sub->where('team1_id', $t1Id)->where('team2_id', $t2Id);
                } else {
                    $sub->where('team1_name', 'like', "%{$t1Name}%")->where('team2_name', 'like', "%{$t2Name}%");
                }
            })->orWhere(function ($sub) use ($t1Id, $t2Id, $t1Name, $t2Name) {
                if ($t1Id && $t2Id) {
                    $sub->where('team1_id', $t2Id)->where('team2_id', $t1Id);
                } else {
                    $sub->where('team1_name', 'like', "%{$t2Name}%")->where('team2_name', 'like', "%{$t1Name}%");
                }
            });
        })
        ->where('format', strtoupper($format))
        ->first();
    }

    /**
     * Normalize stats relative to a given team (Team 1 or Team 2).
     */
    public function getPerspectiveStats($perspectiveTeam): array
    {
        $persId = is_object($perspectiveTeam) ? $perspectiveTeam->id : (is_numeric($perspectiveTeam) ? (int)$perspectiveTeam : null);
        $persName = is_object($perspectiveTeam) ? $perspectiveTeam->name : (string)$perspectiveTeam;

        $isT1 = false;
        if ($persId && $this->team1_id && $this->team1_id == $persId) {
            $isT1 = true;
        } elseif (stripos($this->team1_name, $persName) !== false || stripos($persName, $this->team1_name) !== false) {
            $isT1 = true;
        }

        if ($isT1) {
            return [
                'totalMatches' => $this->total_matches,
                't1Wins' => $this->team1_won,
                't2Wins' => $this->team2_won,
                't1Lost' => $this->team2_won,
                't2Lost' => $this->team1_won,
                'drawn' => $this->drawn,
                'ties' => $this->tied,
                'noResults' => $this->no_result,
                't1HomeWins' => $this->team1_home_won,
                't2HomeWins' => $this->team2_home_won,
                't1AwayWins' => $this->team1_away_won,
                't2AwayWins' => $this->team2_away_won,
                't1NeutralWins' => $this->team1_neutral_won,
                't2NeutralWins' => $this->team2_neutral_won,
                't1Highest' => $this->team1_highest_score,
                't2Highest' => $this->team2_highest_score,
                't1Lowest' => $this->team1_lowest_score,
                't2Lowest' => $this->team2_lowest_score,
                'narrative' => $this->summary_narrative,
            ];
        } else {
            return [
                'totalMatches' => $this->total_matches,
                't1Wins' => $this->team2_won,
                't2Wins' => $this->team1_won,
                't1Lost' => $this->team1_won,
                't2Lost' => $this->team2_won,
                'drawn' => $this->drawn,
                'ties' => $this->tied,
                'noResults' => $this->no_result,
                't1HomeWins' => $this->team2_home_won,
                't2HomeWins' => $this->team1_home_won,
                't1AwayWins' => $this->team2_away_won,
                't2AwayWins' => $this->team1_away_won,
                't1NeutralWins' => $this->team2_neutral_won,
                't2NeutralWins' => $this->team1_neutral_won,
                't1Highest' => $this->team2_highest_score,
                't2Highest' => $this->team1_highest_score,
                't1Lowest' => $this->team2_lowest_score,
                't2Lowest' => $this->team1_lowest_score,
                'narrative' => $this->summary_narrative,
            ];
        }
    }
}
