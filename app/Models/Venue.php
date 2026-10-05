<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Venue extends Model 
{ 
    use HasFormattedImage;
    public $timestamps = false; 
    protected $guarded = []; 

    public function getImageUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->name ?? ('venue-' . $this->id));
    }

    public function getUrlAttribute()
    {
        $slug = !empty($this->slug) ? $this->slug : \Illuminate\Support\Str::slug($this->name ?: 'venue');
        return route('venue.show.slug', ['slug' => $slug, 'id' => $this->id]);
    }
}
