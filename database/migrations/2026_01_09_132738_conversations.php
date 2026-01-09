<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // one client ↔ one freelancer
            $table->foreignId('client_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('freelancer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // last message preview
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_time')->nullable();

            // unread counters
            $table->integer('client_unread')->default(0);
            $table->integer('freelancer_unread')->default(0);

            $table->timestamps();

            $table->unique(['client_id', 'freelancer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
