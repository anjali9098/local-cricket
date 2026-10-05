<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Prediction extends Model 
{ 
    use HasFormattedImage;
    public $timestamps = false; 
    protected $guarded = []; 

    public function getPosterImageAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->title ?? ('prediction-' . $this->id));
    }

    public function getUrlAttribute()
    {
        $slug = !empty($this->slug) ? $this->slug : \Illuminate\Support\Str::slug($this->title ?: 'prediction');
        if ($this->tag === 'MATCH PREVIEW') {
            return route('preview.show.slug', ['slug' => $slug, 'id' => $this->id]);
        }
        return route('prediction.show.slug', ['slug' => $slug, 'id' => $this->id]);
    }
}
