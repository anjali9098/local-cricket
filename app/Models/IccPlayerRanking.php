<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class IccPlayerRanking extends Model
{
    use HasFormattedImage;

    protected $table = 'icc_player_rankings';
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function getPhotoUrlAttribute($value)
    {
        if (!empty($value) && !str_contains($value, 'images.icc-cricket.com') && !str_contains($value, 'default-player')) {
            return self::formatImageUrl($value);
        }

        // Try linked player profile_image
        if ($this->player_id) {
            $p = Player::find($this->player_id);
            if ($p && !empty($p->profile_image) && !str_contains($p->profile_image, 'default-player')) {
                return $p->profile_image;
            }
        }

        // Try matching by name in Player table
        if (!empty($this->player_name)) {
            $p = Player::where('name', 'like', '%' . trim($this->player_name) . '%')->first();
            if ($p && !empty($p->profile_image) && !str_contains($p->profile_image, 'default-player')) {
                return $p->profile_image;
            }
        }

        // Sleek fallback theme avatar with player initials
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->player_name ?: 'CR') . '&background=0b1120&color=38bdf8&bold=true&size=128&rounded=true';
    }

    public function getCountryFlagAttribute($value)
    {
        if (!empty($value)) {
            return self::formatImageUrl($value);
        }
        $code = strtolower(trim((string)$this->country));
        $flags = [
            'ind' => 'https://flagcdn.com/w80/in.png',
            'india' => 'https://flagcdn.com/w80/in.png',
            'aus' => 'https://flagcdn.com/w80/au.png',
            'australia' => 'https://flagcdn.com/w80/au.png',
            'eng' => 'https://flagcdn.com/w80/gb-eng.png',
            'england' => 'https://flagcdn.com/w80/gb-eng.png',
            'sa' => 'https://flagcdn.com/w80/za.png',
            'south africa' => 'https://flagcdn.com/w80/za.png',
            'nz' => 'https://flagcdn.com/w80/nz.png',
            'new zealand' => 'https://flagcdn.com/w80/nz.png',
            'pak' => 'https://flagcdn.com/w80/pk.png',
            'pakistan' => 'https://flagcdn.com/w80/pk.png',
            'ban' => 'https://flagcdn.com/w80/bd.png',
            'bangladesh' => 'https://flagcdn.com/w80/bd.png',
            'sl' => 'https://flagcdn.com/w80/lk.png',
            'sri lanka' => 'https://flagcdn.com/w80/lk.png',
            'wi' => 'https://flagcdn.com/w80/jm.png',
            'west indies' => 'https://flagcdn.com/w80/jm.png',
            'afg' => 'https://flagcdn.com/w80/af.png',
            'afghanistan' => 'https://flagcdn.com/w80/af.png',
            'zim' => 'https://flagcdn.com/w80/zw.png',
            'zimbabwe' => 'https://flagcdn.com/w80/zw.png',
            'ire' => 'https://flagcdn.com/w80/ie.png',
            'ireland' => 'https://flagcdn.com/w80/ie.png',
        ];
        return $flags[$code] ?? 'https://flagcdn.com/w80/in.png';
    }

    public function getSlugAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return \Illuminate\Support\Str::slug($this->player_name ?? ('icc-ranking-' . $this->id));
    }
}
