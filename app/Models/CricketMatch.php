<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CricketMatch extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'matches';
    protected $guarded = [];

    protected $appends = ['winning_title', 'effective_status'];

    public function team1() {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2() {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function venue() {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }

    public function balls() {
        return $this->hasMany(BallByBall::class, 'match_id')->orderBy('id', 'desc');
    }

    public function battingStats() {
        return $this->hasMany(PlayerBattingStat::class, 'match_id');
    }

    public function bowlingStats() {
        return $this->hasMany(PlayerBowlingStat::class, 'match_id');
    }

    /**
     * Compute effective status (checks if a match is completed by score chase)
     */
    public function getEffectiveStatusAttribute()
    {
        if ($this->status === 'completed' || (!empty($this->result_text) && !str_starts_with($this->result_text, 'toss:'))) {
            return 'completed';
        }

        // Automatic completion check if Team 2 chased down Team 1's target
        if ($this->team1_score > 0 && $this->team2_score > $this->team1_score) {
            return 'completed';
        }

        // Automatic completion check if Team 2 played full overs or lost all 10 wickets defending target
        if ($this->team1_score > 0 && $this->team2_wickets >= 10 && $this->team2_score < $this->team1_score) {
            return 'completed';
        }

        return $this->status ?: 'upcoming';
    }

    /**
     * Compute winning title / match result dynamically
     */
    public function getWinningTitleAttribute()
    {
        if (!empty($this->result_text) && !str_starts_with($this->result_text, 'toss:')) {
            return $this->result_text;
        }

        $t1Name = $this->team1?->name ?? 'Team 1';
        $t2Name = $this->team2?->name ?? 'Team 2';

        if ($this->effective_status === 'completed') {
            if ($this->team1_score > $this->team2_score) {
                $diff = $this->team1_score - $this->team2_score;
                return "{$t1Name} won by {$diff} runs";
            } elseif ($this->team2_score > $this->team1_score) {
                $wLeft = max(1, 10 - ($this->team2_wickets ?? 0));
                return "{$t2Name} won by {$wLeft} wickets";
            } elseif ($this->team1_score > 0 && $this->team1_score === $this->team2_score) {
                return "Match Tied";
            }
            return "Match Completed";
        }

        if ($this->effective_status === 'live') {
            if (!empty($this->custom_note) && strlen($this->custom_note) < 60 && !str_contains($this->custom_note, '<p>')) {
                return $this->custom_note;
            }
            return 'Match In Progress';
        }

        if (!empty($this->custom_note) && strlen($this->custom_note) < 60 && !str_contains($this->custom_note, '<p>')) {
            return $this->custom_note;
        }

        return 'Match Scheduled';
    }

    /**
     * Auto Status computation
     */
    public function getAutoStatusAttribute()
    {
        return $this->effective_status;
    }
}
