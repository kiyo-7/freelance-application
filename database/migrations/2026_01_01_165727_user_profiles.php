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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->string('name');
            $table->string('profile_image')->nullable(); // image path or URL

            // ✅ Added fields
            $table->string('professional_title')->nullable();
            $table->string('city')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->boolean('is_profile_complete')->default(false);

            // Existing fields
            $table->text('skills')->nullable(); // comma-separated or JSON
            $table->text('portfolio')->nullable();
            $table->decimal('rating', 2, 1)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
