<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores system-wide tunable constants so superadmins can adjust
     * algorithm behavior without code deployment.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->string('value', 255);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed the default gravity constant
        DB::table('settings')->insert([
            [
                'key'         => 'ranking_gravity',
                'value'       => '1.8',
                'description' => 'Gravity exponent (G) in ranking formula S = W / (T+2)^G. Higher values decay older reports faster.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'ranking_recalc_window_hours',
                'value'       => '168',
                'description' => 'Only reports created within this many hours are recalculated by the batch job. Default: 168 (7 days).',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'require_email_verification',
                'value'       => '0',
                'description' => 'When enabled (1), users must verify their email address before accessing the dashboard. Default: off until production email infrastructure is ready.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'require_phone_verification',
                'value'       => '0',
                'description' => 'When enabled (1), users must verify their phone number via OTP before accessing the dashboard. Default: off until production SMS infrastructure is ready.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
