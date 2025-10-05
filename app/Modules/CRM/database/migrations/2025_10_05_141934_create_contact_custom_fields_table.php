<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_custom_field_definitions', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // text, number, date, boolean, select, multiselect
            $table->json('options')->nullable(); // for select/multiselect fields
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_custom_field_values', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->foreignId('field_definition_id')->constrained('contact_custom_field_definitions')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'field_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_custom_field_values');
        Schema::dropIfExists('contact_custom_field_definitions');
    }
};
