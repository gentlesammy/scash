<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores phone numbers of permanently banned users.
     * Checked during registration to prevent re-signup.
     */
    public function up(): void
    {
        Schema::create('banned_phones', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique()->comment('Phone number of banned user');
            $table->unsignedBigInteger('banned_user_id')->nullable()->comment('Original user ID');
            $table->string('reason')->nullable();
            $table->timestamp('banned_at');
            $table->timestamps();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_phones');
    }
};
