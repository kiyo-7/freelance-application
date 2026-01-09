<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade');

            $table->foreignId('freelancer_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Proposal details
            $table->decimal('bid_amount', 10, 2);
            $table->integer('delivery_time'); // in days
            $table->text('cover_letter');

            // Status
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'withdrawn'
            ])->default('pending');

            // Optional fields
            $table->boolean('is_shortlisted')->default(false);
            $table->timestamp('submitted_at')->useCurrent();

            $table->timestamps();

            // Prevent duplicate proposals
            $table->unique(['project_id', 'freelancer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
