<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CricketMatch extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'matches';
    protected $guarded = [];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_api_match' => 'boolean',
        'api_raw_data' => 'array',
    ];

    protected $appends = ['winning_title', 'effective_status', 'where_to_watch'];

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function team1() {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2() {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function venue() {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }

    public function balls() {
        return $this->hasMany(BallByBall::class, 'match_id')->orderBy('id', 'desc');
    }

    public function battingStats() {
        return $this->hasMany(PlayerBattingStat::class, 'match_id');
    }

    public function bowlingStats() {
        return $this->hasMany(PlayerBowlingStat::class, 'match_id');
    }

    /**
     * Compute effective status (checks live, upcoming, completed dynamically with time-based lifecycle)
     */
    public function getEffectiveStatusAttribute()
    {
        try {
            $st = strtolower(trim($this->status ?? ''));
            if ($st === 'completed') {
                return 'completed';
            }

            $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team1_score ?? '0'))[0] ?? '0');
            $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team2_score ?? '0'))[0] ?? '0');
            $w1 = (int) ($this->team1_wickets ?? 0);
            $w2 = (int) ($this->team2_wickets ?? 0);

            // Automatic completion check if Team 2 chased down Team 1's target or all 10 wickets fell
            if ($s1 > 0 && ($s2 > $s1 || $w2 >= 10)) {
                return 'completed';
            }

            // Time-based lifecycle analysis
            if (!empty($this->match_date)) {
                $matchTime = strtotime($this->match_date);
                $nowTime = time();
                $hoursPassed = ($nowTime - $matchTime) / 3600;

                $format = strtoupper($this->match_type ?: 'T20');
                $maxHours = ($format === 'TEST') ? 120 : (($format === 'ODI') ? 10 : 5);

                // If the scheduled match time has passed beyond the match duration window, it is completed
                if ($hoursPassed > $maxHours) {
                    return 'completed';
                }

                // If match is currently within the active play window
                if ($hoursPassed >= 0 && $hoursPassed <= $maxHours) {
                    return 'live';
                }

                // If match is in the future
                if ($hoursPassed < 0) {
                    return 'upcoming';
                }
            }

            if ($st === 'live') {
                return 'live';
            }

            if ($st === 'upcoming' || $st === 'scheduled') {
                return 'upcoming';
            }

            if ($s1 > 0 || $s2 > 0) {
                return 'live';
            }

            return 'upcoming';
        } catch (\Throwable $e) {
            return $this->status ?: 'upcoming';
        }
    }

    /**
     * Compute winning title / match result dynamically (identifies winner, loser, and margin)
     */
    public function getWinningTitleAttribute()
    {
        try {
            if ($this->effective_status === 'completed') {
                if (!empty($this->result_text) && !in_array(strtolower(trim($this->result_text)), ['match scheduled', 'innings 2 in progress', 'tbd', 'scheduled', 'live', 'upcoming']) && !str_starts_with($this->result_text, 'toss:')) {
                    return $this->result_text;
                }

                $t1Name = $this->team1?->name ?? 'Team 1';
                $t2Name = $this->team2?->name ?? 'Team 2';
                $s1 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team1_score ?? '0'))[0] ?? '0');
                $s2 = (int) preg_replace('/[^0-9]/', '', explode('/', (string)($this->team2_score ?? '0'))[0] ?? '0');

                if ($s1 > $s2) {
                    $diff = $s1 - $s2;
                    return "{$t1Name} won by {$diff} runs";
                } elseif ($s2 > $s1) {
                    $wLeft = max(1, 10 - (int)($this->team2_wickets ?? 0));
                    return "{$t2Name} won by {$wLeft} wickets";
                } elseif ($s1 > 0 && $s1 === $s2) {
                    return "Match Tied";
                }
                return "{$t1Name} won";
            }

            if ($this->effective_status === 'live') {
                if (!empty($this->custom_note) && strlen($this->custom_note) < 60 && !str_contains($this->custom_note, '<p>')) {
                    return $this->custom_note;
                }
                return 'Innings 2 in progress';
            }

            if (!empty($this->custom_note) && strlen($this->custom_note) < 60 && !str_contains($this->custom_note, '<p>')) {
                return $this->custom_note;
            }

            return 'Match Scheduled';
        } catch (\Throwable $e) {
            return 'Match Scheduled';
        }
    }

    /**
     * Auto Status computation
     */
    public function getAutoStatusAttribute()
    {
        return $this->effective_status;
    }

    /**
     * Where to watch broadcaster / stream info
     */
    public function getWhereToWatchAttribute()
    {
        if (!empty($this->attributes['where_to_watch'])) {
            $val = str_ireplace(['CricketKaScore Live Stream', 'CricketKaScore Live', 'CricketKaScore'], '', $this->attributes['where_to_watch']);
            $val = trim($val, " ,");
            if (!empty($val)) {
                return $val;
            }
        }

        if (is_array($this->api_raw_data) && !empty($this->api_raw_data['broadcast'])) {
            $val = str_ireplace(['CricketKaScore Live Stream', 'CricketKaScore Live', 'CricketKaScore'], '', $this->api_raw_data['broadcast']);
            $val = trim($val, " ,");
            if (!empty($val)) {
                return $val;
            }
        }

        $tournamentName = strtolower($this->tournament?->name ?? '');
        $venueName = strtolower($this->venue?->name ?? '');
        $levelType = strtoupper($this->level_type ?? '');
        $t1 = strtolower($this->team1?->name ?? '');
        $t2 = strtolower($this->team2?->name ?? '');
        $allText = $tournamentName . ' ' . $venueName . ' ' . $t1 . ' ' . $t2;

        // 1. English County Championship / Vitality Blast / The Hundred / ECB English Cricket
        $englishCounties = [
            'durham', 'northamptonshire', 'worcestershire', 'lancashire', 'gloucestershire',
            'leicestershire', 'somerset', 'sussex', 'surrey', 'yorkshire', 'essex',
            'nottinghamshire', 'hampshire', 'middlesex', 'warwickshire', 'derbyshire',
            'kent', 'glamorgan', 'chester-le-street', 'headingley', 'trent bridge', 'old trafford',
            'county ground', 'grace road', 'vitality', 'hundred', 'county'
        ];
        foreach ($englishCounties as $county) {
            if (str_contains($allText, $county)) {
                return 'Sony LIV, Sony Sports Ten 5';
            }
        }

        // 2. Caribbean Premier League (CPL) & West Indies
        $cplKeywords = ['trinbago', 'guyana', 'amazon warriors', 'jamaica', 'barbados', 'patriots', 'lucia kings', 'antigua', 'providence', 'kensington', 'bridgetown', 'cpl', 'caribbean'];
        foreach ($cplKeywords as $cpl) {
            if (str_contains($allText, $cpl)) {
                return 'Star Sports Select, JioCinema';
            }
        }

        // 3. Indian Premier League (IPL) & WPL
        $iplClubs = ['chennai super', 'csk', 'mumbai indians', 'royal challengers', 'rcb', 'kolkata knight', 'kkr', 'rajasthan royals', 'sunrisers', 'delhi capitals', 'punjab kings', 'gujarat titans', 'lucknow super', 'ipl', 'wpl'];
        foreach ($iplClubs as $ipl) {
            if (str_contains($allText, $ipl)) {
                return 'JioCinema, Star Sports 1 HD';
            }
        }

        // 4. South Africa (SA20 / CSA T20 Challenge)
        $saTeams = ['titans', 'western province', 'warriors', 'north west', 'lions', 'dolphins', 'supersport park', 'wanderers', 'csa', 'sa20'];
        foreach ($saTeams as $sa) {
            if (str_contains($allText, $sa)) {
                return 'JioCinema, Sports18';
            }
        }

        // 5. Australia (BBL / Big Bash)
        $bblTeams = ['scorchers', 'sixers', 'stars', 'heat', 'renegades', 'hurricanes', 'strikers', 'thunder', 'bbl', 'big bash', 'mcg', 'scg'];
        foreach ($bblTeams as $bbl) {
            if (str_contains($allText, $bbl)) {
                return 'Disney+ Hotstar, Star Sports 2';
            }
        }

        // 6. Pakistan (PSL)
        if (str_contains($allText, 'psl') || str_contains($allText, 'qalandars') || str_contains($allText, 'zalmi') || str_contains($allText, 'karachi kings')) {
            return 'Sony LIV, Sony Sports Ten 5';
        }

        // 7. ICC World Cup / Champions Trophy / Asia Cup
        if (str_contains($allText, 'world cup') || str_contains($allText, 'icc') || str_contains($allText, 'asia cup') || str_contains($allText, 'champions trophy')) {
            return 'Disney+ Hotstar, Star Sports 1 HD';
        }

        // 8. Team India International Matches
        if (str_contains($t1, 'india') || str_contains($t2, 'india')) {
            return 'Disney+ Hotstar, Star Sports 1 HD';
        }

        // 9. General International Matches
        if ($levelType === 'INTERNATIONAL') {
            return 'Disney+ Hotstar, Star Sports Network';
        }

        // 10. Default real TV & streaming network (Never site name)
        return 'Disney+ Hotstar, Star Sports 1';
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'])) {
            return $this->attributes['slug'];
        }
        $t1 = $this->team1?->name ?? 'team1';
        $t2 = $this->team2?->name ?? 'team2';
        $custom = $this->custom_note ? '-' . \Illuminate\Support\Str::slug($this->custom_note) : '';
        $slug = \Illuminate\Support\Str::slug($t1 . '-vs-' . $t2 . $custom);
        return !empty($slug) ? $slug : 'match';
    }

    public function getUrlAttribute()
    {
        return route('matches.detail.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }

    public function getLocalScorerUrlAttribute()
    {
        return route('local.scorer.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }

    public function getAdminScorerUrlAttribute()
    {
        return route('admin.scorer.slug', ['slug' => $this->slug, 'id' => $this->id]);
    }
}

