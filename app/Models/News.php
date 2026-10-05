<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class News extends Model {
    use HasFormattedImage;
    public $timestamps = true;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (empty($model->read_time)) {
                $wordCount = str_word_count(strip_tags($model->content ?? ''));
                $minutes = $wordCount > 50 ? max(1, (int)ceil($wordCount / 200)) : 3;
                $model->read_time = $minutes . ' MIN READ';
            }
        });
    }

    public function getImageUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->title ?? ('news-' . $this->id));
    }

    public function getUrlAttribute()
    {
        $slug = !empty($this->slug) ? $this->slug : \Illuminate\Support\Str::slug($this->title ?: 'cricket-news');
        return route('news.show.slug', ['slug' => $slug, 'id' => $this->id]);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

