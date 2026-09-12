<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Player extends Model {
    use HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    protected $appends = ['initials'];

    public function getProfileImageAttribute($value)
    {
        if (!empty($value)) {
            return self::formatImageUrl($value);
        }
        return null;
    }

    public function getInitialsAttribute()
    {
        $words = preg_split("/\s+/", trim($this->name ?? 'PL'));
        $initials = '';
        foreach ($words as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }
        return substr($initials, 0, 3) ?: 'PL';
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }
}

