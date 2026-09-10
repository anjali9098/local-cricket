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

        // If it starts with data:image/ or data:application/ or blob: (valid data URL)
        if (str_starts_with($val, 'data:image/') || str_starts_with($val, 'data:application/') || str_starts_with($val, 'blob:')) {
            return $val;
        }

        // If it starts with http:// or https://
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            // Check if it was previously corrupted by prepending host to a data URI (e.g., http://localhost/data:image/...)
            if (preg_match('#^https?://[^/]+/(data:image/[^"\'\s]+)$#i', $val, $matches)) {
                return $matches[1];
            }

            // If it was stored with a localhost / 127.0.0.1 domain previously
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $val, $matches)) {
                $path = preg_replace('#^score-tracker-laravel/public/#i', '', $matches[3]);
                return asset($path);
            }

            return $val;
        }

        // If it is a relative storage / upload path
        return asset(ltrim($val, '/'));
    }
}
