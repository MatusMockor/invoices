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
            $table->boolean('reverse_charge')->default(false)->after('specific_symbol');
            $table->string('tax_exemption_reason', 200)->nullable()->after('reverse_charge');
            $table->string('special_text', 200)->nullable()->after('tax_exemption_reason');
            $table->text('notes')->nullable()->after('special_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Safety check: Only drop columns if they exist
            if (Schema::hasColumn('invoices', 'reverse_charge')) {
                $table->dropColumn('reverse_charge');
            }
            if (Schema::hasColumn('invoices', 'tax_exemption_reason')) {
                $table->dropColumn('tax_exemption_reason');
            }
            if (Schema::hasColumn('invoices', 'special_text')) {
                $table->dropColumn('special_text');
            }
            if (Schema::hasColumn('invoices', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
