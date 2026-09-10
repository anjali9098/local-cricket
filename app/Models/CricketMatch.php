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

    protected $casts = [
        'is_approved' => 'boolean',
        'is_api_match' => 'boolean',
        'api_raw_data' => 'array',
    ];

    protected $appends = ['winning_title', 'effective_status'];

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

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
        try {
            if ($this->status === 'completed' || (!empty($this->result_text) && !str_starts_with($this->result_text, 'toss:'))) {
                return 'completed';
            }

            $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team1_score ?? '0'))[0] ?? '0');
            $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team2_score ?? '0'))[0] ?? '0');
            $w2 = (int) ($this->team2_wickets ?? 0);

            // Automatic completion check if Team 2 chased down Team 1's target
            if ($s1 > 0 && $s2 > $s1) {
                return 'completed';
            }

            // Automatic completion check if Team 2 played full overs or lost all 10 wickets defending target
            if ($s1 > 0 && $w2 >= 10 && $s2 < $s1) {
                return 'completed';
            }

            return $this->status ?: 'upcoming';
        } catch (\Throwable $e) {
            return $this->status ?: 'upcoming';
        }
    }

    /**
     * Compute winning title / match result dynamically
     */
    public function getWinningTitleAttribute()
    {
        try {
            if (!empty($this->result_text) && !str_starts_with($this->result_text, 'toss:')) {
                return $this->result_text;
            }

            $t1Name = $this->team1?->name ?? 'Team 1';
            $t2Name = $this->team2?->name ?? 'Team 2';

            if ($this->effective_status === 'completed') {
                $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team1_score ?? '0'))[0] ?? '0');
                $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team2_score ?? '0'))[0] ?? '0');
                if ($s1 > $s2) {
                    $diff = $s1 - $s2;
                    return "{$t1Name} won by {$diff} runs";
                } elseif ($s2 > $s1) {
                    $wLeft = max(1, 10 - (int)($this->team2_wickets ?? 0));
                    return "{$t2Name} won by {$wLeft} wickets";
                } elseif ($s1 > 0 && $s1 === $s2) {
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
        } catch (\Throwable $e) {
            return 'Match Scheduled';
        }
    }

    /**
     * Auto Status computation
     */
    public function getAutoStatusAttribute()
    {
        return $this->effective_status;
    }
}
