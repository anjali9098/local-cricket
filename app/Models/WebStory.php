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
        if (!empty($formatted)) {
            return $formatted;
        }

        // If empty, fallback to first slide's image
        $rawSlides = $this->getRawOriginal('slides');
        $slides = is_string($rawSlides) ? json_decode($rawSlides, true) : $rawSlides;
        if (!empty($slides) && is_array($slides)) {
            $first = $slides[0];
            $firstImg = is_array($first) ? ($first['image'] ?? ($first['url'] ?? '')) : $first;
            if (!empty($firstImg)) {
                return self::formatImageUrl($firstImg);
            }
        }

        return null;
    }

    public function getSlidesAttribute($value)
    {
        $slides = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($slides)) {
            return [];
        }

        return array_map(function($slide) {
            if (is_array($slide)) {
                return [
                    'image' => self::formatImageUrl($slide['image'] ?? ($slide['url'] ?? '')),
                    'heading' => $slide['heading'] ?? ($slide['title'] ?? ''),
                    'description' => $slide['description'] ?? ($slide['desc'] ?? ''),
                    'cta_text' => $slide['cta_text'] ?? ($slide['ctaText'] ?? ''),
                    'cta_url' => $slide['cta_url'] ?? ($slide['ctaUrl'] ?? ''),
                ];
            }

            // Legacy string URL format
            return [
                'image' => self::formatImageUrl($slide),
                'heading' => '',
                'description' => '',
                'cta_text' => '',
                'cta_url' => '',
            ];
        }, $slides);
    }

    public function getFirstSlideImageAttribute()
    {
        $slides = $this->slides;
        if (!empty($slides) && is_array($slides) && isset($slides[0])) {
            $first = $slides[0];
            return is_array($first) ? ($first['image'] ?? '') : (string)$first;
        }
        return '';
    }
}

