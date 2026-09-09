<?php

namespace App\Models\Traits;

trait HasFormattedImage
{
    public static function formatImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $val = trim($url);

        // If it was stored with a localhost / 127.0.0.1 domain previously
        if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $val, $matches)) {
            $path = preg_replace('#^score-tracker-laravel/public/#i', '', $matches[3]);
            return asset($path);
        }

        // If it is a full external URL (http:// or https://)
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }

        // If it is a relative storage / upload path
        return asset(ltrim($val, '/'));
    }
}
