<?php

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
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('fuel_amount', 8, 2)->nullable()->after('driver_name'); // Amount of fuel in liters
            $table->decimal('fuel_cost', 10, 2)->nullable()->after('fuel_amount'); // Cost in EUR
            $table->string('fuel_receipt_number')->nullable()->after('fuel_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('fuel_amount');
            $table->dropColumn('fuel_cost');
            $table->dropColumn('fuel_receipt_number');
        });
    }
};
