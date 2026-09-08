<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    public function players() {
        return $this->hasMany(Player::class);
    }

    public function tournament() {
        return $this->belongsTo(Tournament::class);
    }
}
