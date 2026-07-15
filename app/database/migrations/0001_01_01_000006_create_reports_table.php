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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('scam_category_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            
            // Scam identifiers (can be bank account, phone number, email address or a mix)
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_address')->nullable();
            
            $table->text('narrative')->nullable()->comment('User description of the fraud event');
            $table->enum('stage', ['stage_1', 'stage_2'])->default('stage_1')->comment('Progressive disclosure stages');
            
            // Computed variables for feed ranking algorithm
            $table->decimal('weighted_credibility', 12, 4)->default(0.0000)->comment('Sum of (rating * user_credibility_rank)');
            $table->decimal('ranking_score', 16, 6)->default(0.000000)->comment('Algorithm output for sorting');
            
            $table->timestamps();

            // Indexes for fast exact-match lookup on PII vendors
            $table->index('bank_account_number');
            $table->index('phone_number');
            $table->index('email_address');
            
            // Composite/Algorithm index for feed ranking query optimization
            $table->index(['ranking_score', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
