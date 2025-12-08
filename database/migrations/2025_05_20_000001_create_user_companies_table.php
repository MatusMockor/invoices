<?php

declare(strict_types=1);

use App\Enums\CompanyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_companies', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ico');
            $table->string('dic')->nullable();
            $table->string('ic_dph')->nullable();
            $table->string('name');
            $table->string('city');
            $table->string('street');
            $table->string('postal_code');
            $table->string('country');
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('type')->default(CompanyType::SOLE_PROPRIETOR->value)->comment('Company type enum value');
            $table->string('registration_number')->comment('Registration number in business or trade register');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_companies');
    }
};
