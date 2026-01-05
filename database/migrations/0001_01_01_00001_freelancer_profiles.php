<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id(); // numeric primary key
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            $table->string('full_name');
            $table->string('professional_title');
            $table->string('location');
            $table->decimal('hourlyRate', 10, 2)->default(0);
            $table->text('avatar_url')->nullable();

            $table->boolean('is_online')->default(false);
            $table->boolean('is_verified')->default(false);

            $table->decimal('rating', 3, 1)->default(0); // 0.0 to 9.9
            $table->integer('total_reviews')->default(0);
            $table->integer('completed_jobs')->default(0);

            $table->string('response_time')->default('24h');
            $table->text('bio')->nullable();
            $table->integer('years_of_experience')->default(0);

            // JSON columns
            $table->json('languages')->default(json_encode([]));
            $table->json('skills')->default(json_encode([]));
            $table->json('services')->default(json_encode([]));
            $table->json('portfolio')->default(json_encode([]));
            $table->json('reviews')->default(json_encode([]));
            $table->json('rating_distribution')->default(json_encode(new \stdClass()));

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profiles');
    }
};
