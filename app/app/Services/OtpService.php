<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\BannedPhone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OTP Service — Handles phone verification via Termii.
 *
 * Supports SMS (primary) and WhatsApp (fallback).
 * Rate-limited: max 3 OTP requests per phone/IP per hour.
 */
class OtpService
{
    private string $apiKey;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.termii.api_key', '');
        $this->senderId = config('services.termii.sender_id', 'SCASH');
        $this->baseUrl = config('services.termii.base_url', 'https://api.ng.termii.com');
    }

    /**
     * Send an OTP code to the given phone number.
     *
     * @throws \App\Exceptions\OtpRateLimitException
     * @throws \App\Exceptions\BannedPhoneException
     */
    public function send(string $phone, string $ipAddress, string $channel = 'sms'): OtpVerification
    {
        // 1. Check if phone is burned (banned)
        if (BannedPhone::isBanned($phone)) {
            throw new \App\Exceptions\BannedPhoneException(
                'This phone number has been permanently suspended.'
            );
        }

        // 2. Rate limit: max 3 per phone per hour
        if (OtpVerification::recentCountForPhone($phone) >= 3) {
            throw new \App\Exceptions\OtpRateLimitException(
                'Too many OTP requests. Please try again in one hour.'
            );
        }

        // 3. Rate limit: max 3 per IP per hour
        if (OtpVerification::recentCountForIp($ipAddress) >= 3) {
            throw new \App\Exceptions\OtpRateLimitException(
                'Too many OTP requests from this network. Please try again in one hour.'
            );
        }

        // 4. Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 5. Store in database
        $otp = OtpVerification::create([
            'phone'      => $phone,
            'code'       => $code,
            'ip_address' => $ipAddress,
            'channel'    => $channel,
            'expires_at' => now()->addMinutes(10),
        ]);

        // 6. Send via Termii API
        $sent = $this->sendViaTermii($phone, $code, $channel);

        // 7. If SMS fails, fallback to WhatsApp
        if (!$sent && $channel === 'sms') {
            Log::warning("SMS OTP failed for {$phone}, falling back to WhatsApp");
            $otp->update(['channel' => 'whatsapp']);
            $this->sendViaTermii($phone, $code, 'whatsapp');
        }

        return $otp;
    }

    /**
     * Verify an OTP code.
     */
    public function verify(string $phone, string $code): bool
    {
        $otp = OtpVerification::where('phone', $phone)
            ->where('code', $code)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['is_verified' => true]);

        return true;
    }

    /**
     * Send OTP via Termii API.
     */
    private function sendViaTermii(string $phone, string $code, string $channel): bool
    {
        // In local/testing: log instead of calling API
        if (app()->environment('local', 'testing') || empty($this->apiKey)) {
            Log::info("OTP [{$channel}] for {$phone}: {$code}");
            return true;
        }

        try {
            $endpoint = $channel === 'whatsapp'
                ? "{$this->baseUrl}/api/sms/otp/send"
                : "{$this->baseUrl}/api/sms/otp/send";

            $response = Http::post($endpoint, [
                'api_key'         => $this->apiKey,
                'message_type'    => 'NUMERIC',
                'to'              => $phone,
                'from'            => $this->senderId,
                'channel'         => $channel === 'whatsapp' ? 'whatsapp' : 'generic',
                'pin_attempts'    => 3,
                'pin_time_to_live' => 10,
                'pin_length'      => 6,
                'pin_placeholder' => '< 1234 >',
                'message_text'    => "Your SCASH verification code is < 1234 >. Valid for 10 minutes. Do not share this code.",
                'pin_type'        => 'NUMERIC',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Termii OTP send failed: {$e->getMessage()}", [
                'phone'   => $phone,
                'channel' => $channel,
            ]);
            return false;
        }
    }
}
