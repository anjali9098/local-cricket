<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Player extends Model {
    use HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    public function getProfileImageAttribute($value)
    {
        return self::formatImageUrl($value);
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }
}

