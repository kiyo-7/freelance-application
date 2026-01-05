<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id(); // numeric auto-increment primary key
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            $table->string('full_name');
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->text('avatar_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
