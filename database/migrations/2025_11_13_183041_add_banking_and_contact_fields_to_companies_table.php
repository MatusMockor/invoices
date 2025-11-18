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
        Schema::table('companies', function (Blueprint $table) {
            // Banking information for Slovak invoice compliance (§ 74 ods. 1)
            $table->string('iban')->nullable()->after('country');
            $table->string('swift')->nullable()->after('iban');
            $table->string('bank_name')->nullable()->after('swift');

            // Contact information
            $table->string('phone')->nullable()->after('bank_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');

            // Company type (s.r.o., a.s., živnosť, etc.) - rename from 'type'
            $table->string('company_type')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'iban',
                'swift',
                'bank_name',
                'phone',
                'email',
                'website',
                'company_type',
            ]);
        });
    }
};
