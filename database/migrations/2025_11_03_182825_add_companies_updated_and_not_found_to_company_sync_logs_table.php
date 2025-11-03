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
            $table->integer('companies_updated')->default(0)->after('companies_created');
            $table->integer('companies_not_found')->default(0)->after('companies_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_sync_logs', function (Blueprint $table) {
            $table->dropColumn(['companies_updated', 'companies_not_found']);
        });
    }
};
