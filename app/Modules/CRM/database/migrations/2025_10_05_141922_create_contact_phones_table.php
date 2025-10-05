<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_phones', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->string('phone');
            $table->string('type')->default('secondary'); // primary, secondary, work, mobile, home
            $table->string('country_code')->default('+421');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'phone']);
            $table->index('phone');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_phones');
    }
};
