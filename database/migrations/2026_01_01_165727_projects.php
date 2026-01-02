<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 10, 2);
            $table->enum('status', ['open', 'in_progress', 'completed'])->default('open');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
