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

    public function getAgeAttribute()
    {
        if (!empty($this->date_of_birth)) {
            try {
                return \Carbon\Carbon::parse($this->date_of_birth)->age . ' Years';
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getPlayedTeamsListAttribute()
    {
        if (empty($this->played_teams)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->played_teams))));
    }

    public function hasFamilyDetails()
    {
        return !empty($this->father_name) || !empty($this->mother_name) || !empty($this->spouse_name) || !empty($this->children) || !empty($this->siblings);
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }
}

