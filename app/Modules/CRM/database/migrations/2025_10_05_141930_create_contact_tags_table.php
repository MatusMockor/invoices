<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_tags', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#3B82F6');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_tag_assignments', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('contact_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['contact_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tag_assignments');
        Schema::dropIfExists('contact_tags');
    }
};
