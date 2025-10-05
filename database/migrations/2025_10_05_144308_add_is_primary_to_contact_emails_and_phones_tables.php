<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_emails', static function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('type');
        });

        Schema::table('contact_phones', static function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('contact_emails', static function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });

        Schema::table('contact_phones', static function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
