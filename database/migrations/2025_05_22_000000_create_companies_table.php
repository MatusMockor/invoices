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
        Schema::create('companies', static function (Blueprint $table) {
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
            $table->string('iban')->nullable()->after('ico');
            $table->string('swift')->nullable()->after('iban');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('company_type')->comment('živnosť or s.r.o.');
            $table->string('registration_number')->comment('Registration number in business or trade register');
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
