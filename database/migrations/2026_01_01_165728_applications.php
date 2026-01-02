<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade');

            $table->foreignId('freelancer_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('proposal')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
