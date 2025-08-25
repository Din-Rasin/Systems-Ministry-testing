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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('request_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('event_type')->comment('LEAVE, MISSION, MEETING');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('status')->default('CONFIRMED')->comment('CONFIRMED, TENTATIVE, CANCELLED');
            $table->timestamps();

            // Add indexes for better performance
            $table->index('user_id');
            $table->index('request_id');
            $table->index('event_type');
            $table->index('start_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
