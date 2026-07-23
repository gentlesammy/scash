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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 64);           // Machine key: 'report_verified', 'points_awarded', etc.
            $table->string('title');               // Human-readable headline
            $table->text('body')->nullable();      // Optional detail text
            $table->string('icon', 32)->default('bi-bell-fill');  // Bootstrap Icon class
            $table->string('action_url')->nullable();  // Clickable link (e.g. /report/42)
            $table->unsignedBigInteger('related_report_id')->nullable();
            $table->foreign('related_report_id')->references('id')->on('reports')->onDelete('set null');
            $table->timestamp('read_at')->nullable();  // null = unread
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id', 'read_at']);           // Unread count query
            $table->index(['user_id', 'created_at']);         // Paginated list query
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
