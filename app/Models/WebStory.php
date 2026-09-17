<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class WebStory extends Model { 
    use HasFormattedImage;

    public $timestamps = true; 
    protected $guarded = []; 
    protected $casts = [
        'slides' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getImageUrlAttribute($value)
    {
        $formatted = self::formatImageUrl($value);
        
        // If image_url is broken, empty, or points to missing uploads, fallback to first slide which exists
        $parsedPath = parse_url($formatted ?? '', PHP_URL_PATH);
        $exists = $parsedPath ? file_exists(public_path(ltrim($parsedPath, '/\\'))) : false;
        
        if (!$exists || empty($value) || str_contains($value, 'uploads/web_stories')) {
            $slides = $this->slides;
            if (!empty($slides) && is_array($slides) && !empty($slides[0])) {
                return $slides[0];
            }
        }

        return $formatted ?: 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=400&h=600&q=80';
    }

    public function getSlidesAttribute($value)
    {
        $slides = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($slides)) {
            return [];
        }
        return array_map(function($slide) {
            return self::formatImageUrl($slide);
        }, $slides);
    }
}

