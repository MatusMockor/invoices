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
        Schema::table('company_sync_logs', function (Blueprint $table) {
            // Drop the old unique constraint on just sync_date
            $table->dropUnique('company_sync_logs_sync_date_unique');

            // Add composite unique constraint on sync_date and sync_type
            $table->unique(['sync_date', 'sync_type'], 'company_sync_logs_sync_date_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_sync_logs', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('company_sync_logs_sync_date_type_unique');

            // Restore the old unique constraint on just sync_date
            $table->unique('sync_date', 'company_sync_logs_sync_date_unique');
        });
    }
};
