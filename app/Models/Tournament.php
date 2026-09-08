<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function teams() {
        return $this->hasMany(Team::class);
    }

    public function matches() {
        return $this->hasMany(CricketMatch::class, 'tournament_id');
    }
}
