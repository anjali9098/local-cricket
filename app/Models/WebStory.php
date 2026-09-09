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
        return self::formatImageUrl($value);
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

