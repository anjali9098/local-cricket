<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerBattingStat extends Model
{
    protected $table = 'player_batting_stats';
    public $timestamps = false;

    protected $fillable = [
        'match_id',
        'player_name',
        'runs',
        'balls',
        'fours',
        'sixes',
        'strike_rate',
        'status_text',
        'created_at'
    ];

    public function match()
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }
}
