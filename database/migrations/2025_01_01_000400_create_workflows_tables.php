<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('workflows', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->enum('request_type', ['leave', 'mission']);
			$table->timestamps();
		});

		Schema::create('workflow_steps', function (Blueprint $table) {
			$table->id();
			$table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
			$table->unsignedInteger('order_index');
			$table->string('approver_role_slug');
			$table->timestamps();
			$table->unique(['workflow_id', 'order_index']);
		});

		Schema::create('department_workflow', function (Blueprint $table) {
			$table->id();
			$table->foreignId('department_id')->constrained()->cascadeOnDelete();
			$table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
			$table->timestamps();
			$table->unique(['department_id', 'workflow_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('department_workflow');
		Schema::dropIfExists('workflow_steps');
		Schema::dropIfExists('workflows');
	}
};