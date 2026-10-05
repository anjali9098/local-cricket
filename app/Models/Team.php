<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasFormattedImage;

class Team extends Model
{
    use HasFactory, HasFormattedImage;
    public $timestamps = false;
    protected $guarded = [];

    public function getCountryFlagUrl(): ?string
    {
        $name = strtolower(trim(($this->name ?? '') . ' ' . ($this->country ?? '')));
        
        $countryFlags = [
            'india' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/IND-CR1@2x.png',
            'pakistan' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/PAK-CR1@2x.png',
            'australia' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/AUS-CR1@2x.png',
            'england' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/ENG-CR1@2x.png',
            'south africa' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SA-CR1@2x.png',
            'west indies' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/WI-CR1@2x.png',
            'new zealand' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/NZ-CR1@2x.png',
            'sri lanka' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SL-CR1@2x.png',
            'bangladesh' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/BAN-CR1@2x.png',
            'afghanistan' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/AFG-CR1@2x.png',
            'zimbabwe' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/ZIM-CR1@2x.png',
            'ireland' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/IRE-CR1@2x.png',
            'netherlands' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/NED-CR1@2x.png',
            'scotland' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SCO-CR1@2x.png',
            'nepal' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/NEP-CR1@2x.png',
            'uae' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/UAE-CR1@2x.png',
            'united arab emirates' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/UAE-CR1@2x.png',
            'usa' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/USA-CR1@2x.png',
            'united states' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/USA-CR1@2x.png',
            'canada' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/CAN-CR1@2x.png',
            'namibia' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/NAM-CR1@2x.png',
            'oman' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/OMA-CR1@2x.png',
            'papua new guinea' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/PNG-CR1@2x.png',
            'png' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/PNG-CR1@2x.png',
            'hong kong' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/HK-CR1@2x.png',
            'singapore' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SIN-CR3@2x.png',
            'saudi arabia' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/SAU-CR2@2x.png',
            'germany' => 'https://d13ir53smqqeyp.cloudfront.net/flags/cr-flags/GER-CR1@2x.png',
            'italy' => 'https://flagcdn.com/w80/it.png',
            'spain' => 'https://flagcdn.com/w80/es.png',
        ];

        foreach ($countryFlags as $key => $flagUrl) {
            if (str_contains($name, $key)) {
                return $flagUrl;
            }
        }

        return null;
    }

    public function getLogoUrlAttribute($value)
    {
        $val = $value ?: ($this->attributes['logo'] ?? null);
        if (!empty($val)) {
            return self::formatImageUrl($val);
        }
        return $this->getCountryFlagUrl();
    }

    public function getLogoAttribute($value)
    {
        $val = $value ?: ($this->attributes['logo_url'] ?? null);
        if (!empty($val)) {
            return self::formatImageUrl($val);
        }
        return $this->getCountryFlagUrl();
    }

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'] ?? null)) {
            return $this->attributes['slug'];
        }
        return \Illuminate\Support\Str::slug($this->name ?? ('team-' . $this->id));
    }

    public function players() {
        return $this->hasMany(Player::class);
    }

    public function tournament() {
        return $this->belongsTo(Tournament::class);
    }
}

