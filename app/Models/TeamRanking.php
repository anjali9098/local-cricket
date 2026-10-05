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
        return self::formatImageUrl($value);
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->team_name ?? ('ranking-' . $this->id));
    }
}
