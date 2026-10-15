<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class TeamRanking extends Model 
{ 
    use HasFormattedImage;
    public $timestamps = false; 
    protected $guarded = []; 

    public function getLogoUrlAttribute($value)
    {
        if (!empty($value)) {
            return self::formatImageUrl($value);
        }
        $name = strtolower(trim((string)$this->team_name));
        $flags = [
            'australia' => 'https://flagcdn.com/w80/au.png',
            'aus' => 'https://flagcdn.com/w80/au.png',
            'india' => 'https://flagcdn.com/w80/in.png',
            'ind' => 'https://flagcdn.com/w80/in.png',
            'england' => 'https://flagcdn.com/w80/gb-eng.png',
            'eng' => 'https://flagcdn.com/w80/gb-eng.png',
            'south africa' => 'https://flagcdn.com/w80/za.png',
            'sa' => 'https://flagcdn.com/w80/za.png',
            'new zealand' => 'https://flagcdn.com/w80/nz.png',
            'nz' => 'https://flagcdn.com/w80/nz.png',
            'pakistan' => 'https://flagcdn.com/w80/pk.png',
            'pak' => 'https://flagcdn.com/w80/pk.png',
            'sri lanka' => 'https://flagcdn.com/w80/lk.png',
            'sl' => 'https://flagcdn.com/w80/lk.png',
            'bangladesh' => 'https://flagcdn.com/w80/bd.png',
            'ban' => 'https://flagcdn.com/w80/bd.png',
            'west indies' => 'https://flagcdn.com/w80/jm.png',
            'wi' => 'https://flagcdn.com/w80/jm.png',
            'afghanistan' => 'https://flagcdn.com/w80/af.png',
            'afg' => 'https://flagcdn.com/w80/af.png',
            'zimbabwe' => 'https://flagcdn.com/w80/zw.png',
            'zim' => 'https://flagcdn.com/w80/zw.png',
            'ireland' => 'https://flagcdn.com/w80/ie.png',
            'ire' => 'https://flagcdn.com/w80/ie.png',
        ];
        return $flags[$name] ?? 'https://flagcdn.com/w80/in.png';
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->team_name ?? ('ranking-' . $this->id));
    }
}
