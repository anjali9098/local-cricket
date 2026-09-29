<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Get Google reCAPTCHA Site Key (Public)
     */
    public static function getSiteKey(): string
    {
        return config('services.recaptcha.site_key') 
            ?: env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
    }

    /**
     * Get Google reCAPTCHA Secret Key (Private)
     */
    public static function getSecretKey(): string
    {
        return config('services.recaptcha.secret_key') 
            ?: env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
    }

    /**
     * Verify Google reCAPTCHA Response Token
     */
    public static function verify(?string $response, ?string $ip = null): bool
    {
        // If empty response token
        if (empty($response)) {
            return false;
        }

        $secret = self::getSecretKey();

        // Google official test secret key always passes
        if ($secret === '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe') {
            return true;
        }

        try {
            $verifyResponse = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $response,
                'remoteip' => $ip,
            ]);

            if ($verifyResponse->successful()) {
                $body = $verifyResponse->json();
                return isset($body['success']) && $body['success'] === true;
            }
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification error: ' . $e->getMessage());
            // In case of network timeout on local dev, fallback to true if test mode
            if (app()->environment('local')) {
                return true;
            }
        }

        return false;
    }
}
