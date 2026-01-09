<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('message');

            // job | message | system
            $table->enum('type', ['job', 'message', 'system'])
                ->default('system');

            $table->boolean('is_read')->default(false);

            $table->timestamps(); // created_at maps to createdAt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
