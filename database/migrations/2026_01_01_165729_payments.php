<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade');

            $table->foreignId('payer_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->foreignId('payee_id')
                  ->constrained('authusers')
                  ->onDelete('cascade');

            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
