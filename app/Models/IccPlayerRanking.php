<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IccPlayerRanking extends Model
{
    protected $table = 'icc_player_rankings';
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
