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
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->enum('type', ['receipt', 'screenshot', 'chat_log'])->default('receipt');
            $table->string('file_path')->comment('Original uploaded evidence (private path)');
            $table->string('redacted_file_path')->nullable()->comment('Redacted evidence version (publicly viewable)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
