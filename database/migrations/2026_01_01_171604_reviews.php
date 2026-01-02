<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade');

            $table->foreignId('reviewer_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->foreignId('reviewee_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();

            $table->timestamps();

            // Optional: prevent duplicate reviews per project
            $table->unique(['project_id', 'reviewer_id', 'reviewee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
