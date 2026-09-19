<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Player extends Model {
    use HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'icc_rankings' => 'array',
    ];

    protected $appends = ['initials', 'normalized_icc_rankings'];

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

    public function getNormalizedIccRankingsAttribute()
    {
        $raw = $this->icc_rankings;
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        } elseif (!is_array($raw)) {
            $raw = [];
        }

        $categories = ['batting', 'bowling', 'all_rounder'];
        $formats = ['test', 'odi', 't20i'];
        $matrix = [];

        foreach ($categories as $cat) {
            $matrix[$cat] = [];
            foreach ($formats as $fmt) {
                $curr = isset($raw[$cat][$fmt]['current']) && trim((string)$raw[$cat][$fmt]['current']) !== ''
                    ? trim((string)$raw[$cat][$fmt]['current'])
                    : '--';
                $best = isset($raw[$cat][$fmt]['best']) && trim((string)$raw[$cat][$fmt]['best']) !== ''
                    ? trim((string)$raw[$cat][$fmt]['best'])
                    : '--';

                $matrix[$cat][$fmt] = [
                    'current' => $curr,
                    'best' => $best,
                ];
            }
        }

        return $matrix;
    }

    public function hasIccRankings()
    {
        $raw = $this->icc_rankings;
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (!is_array($raw) || empty($raw)) {
            return false;
        }

        foreach ($raw as $cat => $formats) {
            if (is_array($formats)) {
                foreach ($formats as $fmt => $ranks) {
                    $curr = isset($ranks['current']) ? trim((string)$ranks['current']) : '';
                    $best = isset($ranks['best']) ? trim((string)$ranks['best']) : '';
                    if (($curr !== '' && $curr !== '--') || ($best !== '' && $best !== '--')) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }
}

