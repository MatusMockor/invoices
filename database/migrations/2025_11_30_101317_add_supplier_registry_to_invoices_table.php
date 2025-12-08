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
        Schema::table('invoices', static function (Blueprint $table): void {
            $table->string('supplier_registry_office', 255)
                ->nullable()
                ->after('supplier_company_id')
                ->comment('Snapshot of supplier registry office at invoice creation');

            $table->string('supplier_registry_number', 100)
                ->nullable()
                ->after('supplier_registry_office')
                ->comment('Snapshot of supplier registration number at invoice creation');

            $table->string('supplier_vat_payer_status')
                ->nullable()
                ->after('supplier_registry_number')
                ->comment('Snapshot of supplier VAT payer status at invoice creation');

            $table->string('supplier_vat_period')
                ->nullable()
                ->after('supplier_vat_payer_status')
                ->comment('Snapshot of supplier VAT period at invoice creation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', static function (Blueprint $table): void {
            $table->dropColumn([
                'supplier_registry_office',
                'supplier_registry_number',
                'supplier_vat_payer_status',
                'supplier_vat_period',
            ]);
        });
    }
};
