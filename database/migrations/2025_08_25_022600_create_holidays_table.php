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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('holiday_name');
            $table->date('holiday_date');
            $table->string('holiday_type')->comment('PUBLIC, COMPANY, DEPARTMENT');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade')->comment('NULL for public holidays');
            $table->string('country_code')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('year');
            $table->timestamps();

            // Add indexes for better performance
            $table->index('holiday_date');
            $table->index('department_id');
            $table->index('holiday_type');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
