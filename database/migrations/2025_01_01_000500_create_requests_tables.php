<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('requests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->foreignId('department_id')->constrained()->cascadeOnDelete();
			$table->enum('type', ['leave', 'mission']);
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->string('destination')->nullable();
			$table->text('reason')->nullable();
			$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
			$table->foreignId('workflow_id')->nullable()->constrained()->nullOnDelete();
			$table->unsignedInteger('current_step_index')->default(0);
			$table->timestamps();
		});

		Schema::create('request_approvals', function (Blueprint $table) {
			$table->id();
			$table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
			$table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();
			$table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
			$table->enum('decision', ['pending', 'approved', 'rejected'])->default('pending');
			$table->text('comment')->nullable();
			$table->timestamp('decided_at')->nullable();
			$table->timestamps();
			$table->unique(['request_id', 'workflow_step_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('request_approvals');
		Schema::dropIfExists('requests');
	}
};