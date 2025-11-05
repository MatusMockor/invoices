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
        Schema::table('companies', static function (Blueprint $table) {
            $table->string('registration_office', 255)->nullable()->after('ic_dph');
            $table->string('registration_number', 100)->nullable()->after('registration_office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', static function (Blueprint $table) {
            $table->dropColumn(['registration_office', 'registration_number']);
        });
    }
};
