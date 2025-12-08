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
        Schema::create('vat_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_company_id')
                ->constrained('user_companies')
                ->onDelete('cascade');
            $table->string('vat_status');
            $table->string('vat_period')->nullable();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint: one status per company per start date
            $table->unique(['user_company_id', 'valid_from'], 'unique_company_valid_from');

            // Index for efficient date range queries
            $table->index(['user_company_id', 'valid_from', 'valid_to'], 'idx_company_date_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_status_history');
    }
};
