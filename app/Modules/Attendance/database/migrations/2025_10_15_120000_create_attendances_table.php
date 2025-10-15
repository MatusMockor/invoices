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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();

            $table->string('work_type')->default('office'); // office, home_office, business_trip
            $table->string('status')->default('pending'); // pending, approved, rejected

            $table->text('note')->nullable();
            $table->string('check_in_ip')->nullable();
            $table->string('check_out_ip')->nullable();

            // GPS location (optional)
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();

            // Total working hours (in minutes)
            $table->integer('total_minutes')->nullable();

            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'user_id', 'check_in']);
            $table->index(['company_id', 'status']);
            $table->index('check_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
