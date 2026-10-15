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

    public function players() {
        return $this->hasMany(Player::class, 'series_id');
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'])) {
            return $this->attributes['slug'];
        }
        $slug = \Illuminate\Support\Str::slug($this->name ?: 'tournament');
        return !empty($slug) ? $slug : 'tournament';
    }

    public function getUrlAttribute()
    {
        return url('/series/' . $this->slug . '-' . $this->id);
    }

    public function getSubnavUrl($tab = 'home')
    {
        $base = url('/series/' . $this->slug . '-' . $this->id);
        $map = [
            'home' => '',
            'stats' => '/stats',
            'fixtures' => '/fixtures',
            'points' => '/points-table',
            'points-table' => '/points-table',
            'players' => '/players',
            'teams' => '/teams',
            'news' => '/news',
            'articles' => '/news',
        ];
        return $base . ($map[$tab] ?? ('/' . $tab));
    }

    public function getManageUrlAttribute()
    {
        return route('local.manage-tournament.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }

    public function getAdminManageUrlAttribute()
    {
        return route('admin.manage-tournament.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }

    public function getPreviewUrlAttribute()
    {
        return route('local.tournament.preview.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }

    public function getAdminPreviewUrlAttribute()
    {
        return route('admin.tournament.preview.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }
}

