<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerBowlingStat extends Model
{
    protected $table = 'player_bowling_stats';
    public $timestamps = false;

    protected $fillable = [
        'match_id',
        'player_name',
        'overs',
        'runs',
        'wickets',
        'economy',
        'created_at'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }
}
