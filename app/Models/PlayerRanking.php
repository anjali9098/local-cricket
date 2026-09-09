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
}
