<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('sender_role', ['client', 'freelancer', 'admin']);

            $table->enum('type', ['text', 'image', 'file'])->default('text');

            $table->text('content')->nullable();

            // file support
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();

            // message status
            $table->enum('status', ['sending', 'sent', 'delivered', 'read', 'failed'])
                  ->default('sent');

            $table->json('metadata')->nullable();

            $table->timestamp('timestamp')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
