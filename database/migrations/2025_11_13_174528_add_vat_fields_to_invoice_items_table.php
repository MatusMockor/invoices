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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Rename unit_price to unit_price_without_tax for clarity
            $table->renameColumn('unit_price', 'unit_price_without_tax');

            // Add VAT fields for Slovak invoice compliance (§ 74 ods. 1)
            $table->decimal('tax_rate', 5, 2)->default(20.00)->after('unit_price_without_tax');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            $table->decimal('subtotal', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate',
                'tax_amount',
                'subtotal',
                'discount_amount',
            ]);

            $table->renameColumn('unit_price_without_tax', 'unit_price');
        });
    }
};
