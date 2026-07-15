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
        Schema::create('trust_point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->integer('points')->comment('Positive or negative change in Trust Points');
            $table->string('reason')->comment('Why the points were adjusted');
            
            // Set null on report deletion to keep financial log history intact
            $table->unsignedBigInteger('related_report_id')->nullable();
            $table->foreign('related_report_id')->references('id')->on('reports')->onDelete('set null');
            
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trust_point_logs');
    }
};
