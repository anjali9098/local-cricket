<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Tournament extends Model
{
    use HasFactory, HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    public function getPosterImageAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getBannerUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

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

