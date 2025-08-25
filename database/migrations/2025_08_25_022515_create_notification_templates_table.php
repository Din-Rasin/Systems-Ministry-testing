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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->unique();
            $table->string('template_type')->comment('REQUEST_SUBMITTED, APPROVAL_NEEDED, APPROVED, REJECTED, etc.');
            $table->string('subject_template');
            $table->text('message_template');
            $table->json('placeholders')->comment('Available variables for substitution');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
