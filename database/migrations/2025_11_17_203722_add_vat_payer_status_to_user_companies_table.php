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
        Schema::table('user_companies', function (Blueprint $table) {
            $table->string('vat_payer_status')->default(\App\Enums\VatPayerStatus::NOT_VAT_PAYER->value)->after('ic_dph');
            $table->string('vat_period')->nullable()->after('vat_payer_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn(['vat_payer_status', 'vat_period']);
        });
    }
};
