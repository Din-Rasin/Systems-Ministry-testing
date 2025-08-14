<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // Auto-generated unique identifier
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_id')->constrained()->onDelete('restrict');
            $table->string('type'); // 'leave' or 'mission'
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days')->nullable();
            $table->text('reason');
            $table->string('destination')->nullable(); // For mission requests
            $table->decimal('estimated_cost', 10, 2)->nullable(); // For mission requests
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable(); // File paths as JSON
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
