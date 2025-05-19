<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_entities', static function (Blueprint $table) {
            $table->id();
            $table->string('ico')->unique();
            $table->string('dic')->nullable();
            $table->string('ic_dph')->nullable();
            $table->string('name');
            $table->string('street');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country')->default('Slovensko');
            $table->string('company_type')->comment('živnosť or s.r.o.');
            $table->string('registration_number')->comment('Registration number in business or trade register');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_entities');
    }
};
