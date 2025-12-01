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
        Schema::table('user_companies', static function (Blueprint $table): void {
            $table->string('registry_office', 255)
                ->nullable()
                ->after('registration_number')
                ->comment('Registration office (e.g., Okresny sud Bratislava I)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', static function (Blueprint $table): void {
            $table->dropColumn('registry_office');
        });
    }
};
