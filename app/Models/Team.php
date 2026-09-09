<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Team extends Model
{
    use HasFactory, HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    public function getLogoUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getLogoAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function players() {
        return $this->hasMany(Player::class);
    }

    public function tournament() {
        return $this->belongsTo(Tournament::class);
    }
}

