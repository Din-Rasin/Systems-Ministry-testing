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
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('request_type')->comment('LEAVE or MISSION');
            $table->string('condition_field');
            $table->string('condition_operator')->comment('=, >, <, >=, <=, IN');
            $table->string('condition_value');
            $table->json('action')->comment('What to do when condition is met');
            $table->integer('priority_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add indexes for better performance
            $table->index('department_id');
            $table->index('request_type');
            $table->index('priority_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
