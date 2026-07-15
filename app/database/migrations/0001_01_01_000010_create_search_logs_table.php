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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            // Cryptographic SHA-256 hash of the query value (email/phone/bank account)
            // Ensures search trends can be counted without storing cleartext PII
            $table->string('query_hash', 64)->index();
            $table->string('search_type', 20)->comment('bank, phone, email');
            $table->unsignedInteger('results_count')->default(0);
            $table->boolean('is_whitelisted')->default(false)->comment('Did it match a whitelisted verified vendor?');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
