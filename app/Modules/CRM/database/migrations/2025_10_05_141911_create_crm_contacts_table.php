<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_contacts', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('job_title')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_name', 'last_name']);
            $table->index('primary_email');
            $table->index('is_active');
            $table->index('last_contacted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_contacts');
    }
};
