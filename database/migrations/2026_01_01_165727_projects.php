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
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 10, 2);
            $table->enum('status', ['open', 'in_progress', 'completed'])->default('open');

            // ✅ Added missing fields
            $table->string('client_name');
            $table->string('location')->nullable();
            $table->timestamp('posted_at')->useCurrent(); 
            $table->string('category_badge')->nullable();

            $table->foreignId('freelancer_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
