<?php

declare(strict_types=1);

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
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            $table->timestamp('break_start');
            $table->timestamp('break_end')->nullable();

            $table->string('break_type')->default('lunch'); // lunch, coffee, other
            $table->text('note')->nullable();

            // Duration in minutes (calculated when break ends)
            $table->integer('duration_minutes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
};
