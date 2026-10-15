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

    protected $appends = ['initials', 'normalized_icc_rankings', 'default_avatar', 'is_female'];

    public function getIsFemaleAttribute(): bool
    {
        $combined = strtolower(
            ($this->name ?? '') . ' ' .
            ($this->played_teams ?? '') . ' ' .
            ($this->role ?? '') . ' ' .
            ($this->bio ?? '') . ' ' .
            ($this->meta_description ?? '') . ' ' .
            ($this->keywords ?? '') . ' ' .
            ($this->team ? $this->team->name : '')
        );

        return str_contains($combined, 'women') ||
            str_contains($combined, 'wpl') ||
            str_contains($combined, 'wbbl') ||
            str_contains($combined, 'female') ||
            str_contains($combined, 'girls') ||
            str_contains($combined, 'she/her') ||
            str_contains($combined, 'her career') ||
            str_contains($combined, 'her debut');
    }

    public function getDefaultAvatarAttribute(): string
    {
        return $this->is_female 
            ? asset('images/default-player-girl.svg') 
            : asset('images/default-player-boy.svg');
    }

    public function getHasCustomImageAttribute(): bool
    {
        $val = $this->attributes['profile_image'] ?? null;
        if (empty($val)) return false;
        $valLower = strtolower($val);
        return !(
            str_contains($valLower, 'default-player-image') ||
            str_contains($valLower, 'default-player') ||
            str_contains($valLower, 'default_player') ||
            str_contains($valLower, 'default-avatar') ||
            str_contains($valLower, 'default_avatar') ||
            str_contains($valLower, 'icon512.png') ||
            str_contains($valLower, 'placeholder') ||
            str_contains($valLower, 'default.png') ||
            str_contains($valLower, 'default-player-boy') ||
            str_contains($valLower, 'default-player-girl')
        );
    }

    public function getRealProfileImageAttribute(): ?string
    {
        return $this->has_custom_image ? self::formatImageUrl($this->attributes['profile_image']) : null;
    }

    public function getProfileImageAttribute($value)
    {
        if (!empty($value)) {
            $valLower = strtolower($value);
            if (
                str_contains($valLower, 'default-player-image') ||
                str_contains($valLower, 'default-player') ||
                str_contains($valLower, 'default_player') ||
                str_contains($valLower, 'default-avatar') ||
                str_contains($valLower, 'default_avatar') ||
                str_contains($valLower, 'icon512.png') ||
                str_contains($valLower, 'placeholder') ||
                str_contains($valLower, 'default.png')
            ) {
                return $this->default_avatar;
            }
            return self::formatImageUrl($value);
        }
        return $this->default_avatar;
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->name ?? ('player-' . $this->id));
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

    public function iccRankings()
    {
        return $this->hasMany(IccPlayerRanking::class, 'player_id');
    }

    public function getFormatSquadsDataAttribute()
    {
        $raw = $this->seriesid_captionid_formateid ?? $this->format_squads ?? null;
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // If stored as seriesid-captainid-formatid string format: e.g. "140-46-ODI"
            if (preg_match('/^(\d+)-(\d+)-([A-Za-z0-9_]+)$/', trim($raw), $m)) {
                return [
                    strtoupper($m[3]) => [
                        'series_id' => (int)$m[1],
                        'captain_id' => (int)$m[2],
                        'format_id' => strtoupper($m[3]),
                    ]
                ];
            }
        }
        return [];
    }

    public function getSeriesIdAttribute()
    {
        $data = $this->format_squads_data;
        if (!empty($data)) {
            $first = reset($data);
            return $first['series_id'] ?? null;
        }
        return null;
    }

    public function getFormatIdAttribute()
    {
        $data = $this->format_squads_data;
        if (!empty($data)) {
            $firstKey = key($data);
            return $firstKey ?: ($data[$firstKey]['format_id'] ?? null);
        }
        return null;
    }

    public function getCaptainIdAttribute()
    {
        $data = $this->format_squads_data;
        if (!empty($data)) {
            $first = reset($data);
            return $first['captain_id'] ?? null;
        }
        return null;
    }

    public function getTournamentAttribute()
    {
        $sid = $this->series_id;
        return $sid ? Tournament::find($sid) : null;
    }

    public function getCaptainAttribute()
    {
        $cid = $this->captain_id;
        return ($cid && $cid != $this->id) ? Player::find($cid) : null;
    }

    public function getFormatSquadDetails(string $format): ?array
    {
        $fmt = strtoupper(trim($format));
        $data = $this->format_squads_data;
        return $data[$fmt] ?? null;
    }

    public function getNormalizedIccRankingsAttribute()
    {
        $dbRanks = $this->relationLoaded('iccRankings') ? $this->iccRankings : $this->iccRankings()->get();
        if ($dbRanks->isEmpty() && !empty($this->name)) {
            $pName = trim($this->name);
            $dbRanks = IccPlayerRanking::where('player_id', $this->id)
                ->orWhere('player_name', $pName)
                ->orWhere('player_name', 'like', '%' . $pName . '%')
                ->get();
        }

        $categories = ['batting', 'bowling', 'all_rounder'];
        $formats = ['test', 'odi', 't20i'];
        $matrix = [];

        foreach ($categories as $cat) {
            $matrix[$cat] = [];
            foreach ($formats as $fmt) {
                $matrix[$cat][$fmt] = [
                    'current' => '--',
                    'best' => '--',
                ];
            }
        }

        foreach ($dbRanks as $r) {
            $cat = strtolower(str_replace('-', '_', $r->category));
            $fmt = strtolower(trim((string)$r->format));
            if ($fmt === 't20') {
                $fmt = 't20i';
            }
            if (isset($matrix[$cat][$fmt])) {
                $curr = !empty($r->current_rank) && $r->current_rank !== '--' ? (string)$r->current_rank : '--';
                $best = !empty($r->best_rank) && $r->best_rank !== '--' ? (string)$r->best_rank : (!empty($r->rating) ? (string)$r->rating : '--');
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
        $matrix = $this->normalized_icc_rankings;
        foreach ($matrix as $cat => $formats) {
            foreach ($formats as $fmt => $ranks) {
                $curr = $ranks['current'] ?? '';
                $best = $ranks['best'] ?? '';
                if (($curr !== '' && $curr !== '--') || ($best !== '' && $best !== '--')) {
                    return true;
                }
            }
        }
        return false;
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }

    public function getUrlAttribute()
    {
        $slug = !empty($this->slug) ? $this->slug : \Illuminate\Support\Str::slug($this->name ?: 'player');
        return route('player.profile.slug', ['slug' => $slug, 'id' => $this->id]);
    }
}

