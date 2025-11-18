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
        Schema::table('invoices', function (Blueprint $table) {
            // VAT fields for Slovak invoice compliance (§ 74 ods. 1)
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('tax_rate', 5, 2)->default(20.00)->after('tax_amount');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('tax_rate');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'tax_amount',
                'tax_rate',
                'discount_amount',
                'discount_percentage',
            ]);
        });
    }
};
