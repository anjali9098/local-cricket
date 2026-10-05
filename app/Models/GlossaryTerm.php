<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class GlossaryTerm extends Model 
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
        return \Illuminate\Support\Str::slug($this->term ?? ('term-' . $this->id));
    }

    public function getUrlAttribute()
    {
        $slug = !empty($this->slug) ? $this->slug : \Illuminate\Support\Str::slug($this->term ?: 'term');
        return route('glossary.show.slug', ['slug' => $slug, 'id' => $this->id]);
    }
}
