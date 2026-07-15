<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OTP verification codes for phone verification.
     * Rate-limited: max 3 per phone/IP per hour.
     */
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('code', 6);
            $table->string('ip_address', 45)->nullable();
            $table->enum('channel', ['sms', 'whatsapp'])->default('sms');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['phone', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
