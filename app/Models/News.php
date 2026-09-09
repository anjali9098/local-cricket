<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class News extends Model {
    use HasFormattedImage;
    public $timestamps = true;
    protected $guarded = [];

    public function getImageUrlAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

