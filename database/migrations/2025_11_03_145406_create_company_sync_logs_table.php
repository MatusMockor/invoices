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
        Schema::create('company_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->date('sync_date')->unique()->comment('Date for which the sync was performed');
            $table->string('sync_type', 50)->default('batch-init')->comment('Type of sync: batch-init');
            $table->integer('files_processed')->default(0)->comment('Number of files processed');
            $table->integer('companies_created')->default(0)->comment('Number of companies created/updated');
            $table->integer('errors')->default(0)->comment('Number of errors encountered');
            $table->string('status', 20)->comment('Status: pending, processing, completed, failed');
            $table->timestamp('started_at')->nullable()->comment('When sync started');
            $table->timestamp('completed_at')->nullable()->comment('When sync completed');
            $table->timestamps();

            $table->index('sync_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_sync_logs');
    }
};
