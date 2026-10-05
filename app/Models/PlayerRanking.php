<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class PlayerRanking extends Model 
{ 
    use HasFormattedImage;
    public $timestamps = false; 
    protected $guarded = []; 

    public function getPhotoUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->player_name ?? ('ranking-' . $this->id));
    }
}
