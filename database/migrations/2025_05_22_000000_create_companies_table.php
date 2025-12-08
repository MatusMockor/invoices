<?php

declare(strict_types=1);

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
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
        Schema::create('companies', static function (Blueprint $table) {
            $table->id();
            $table->string('ico')->unique();
            $table->string('dic')->nullable();
            $table->string('ic_dph')->nullable();
            $table->string('registration_office', 255)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('type')->default(CompanyType::SOLE_PROPRIETOR->value);
            $table->string('vat_payer_status')->default(VatPayerStatus::NOT_VAT_PAYER->value);
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
