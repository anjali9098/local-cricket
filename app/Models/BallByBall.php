<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BallByBall extends Model
{
    protected $table = 'ball_by_ball';
    public $timestamps = false;

    protected $fillable = [
        'match_id',
        'over_num',
        'outcome',
        'bowler_name',
        'batsman_name',
        'created_at'
    ];
}
