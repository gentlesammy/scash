<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // The admin/mod who took the action
            $table->string('action'); // e.g., 'merged_report', 'banned_user'
            $table->string('target_type'); // e.g., 'App\Models\Report'
            $table->unsignedBigInteger('target_id'); 
            $table->json('old_values')->nullable(); // Snapshot of data before change
            $table->json('new_values')->nullable(); // Snapshot of data after change
            $table->ipAddress('ip_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
