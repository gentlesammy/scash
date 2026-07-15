<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify the reCAPTCHA token with Google siteverify API.
     */
    public function verify(?string $token, string $action): bool
    {
        // Bypass verification in local/testing environments if token is empty/mocked
        if (app()->environment('local', 'testing') && (empty($token) || $token === 'mock-token')) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $secret = config('services.recaptcha.secret_key');
        if (empty($secret)) {
            Log::warning('Google reCAPTCHA secret key is missing in config. Allowing bypass.');
            return true;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // For reCAPTCHA v3, verify success, score threshold (>= 0.5), and action match
                return ($data['success'] ?? false) 
                    && ($data['score'] ?? 0.0) >= 0.5 
                    && ($data['action'] ?? '') === $action;
            }
        } catch (\Throwable $e) {
            Log::error('Google reCAPTCHA verification request failed: ' . $e->getMessage());
        }

        return false;
    }
}
