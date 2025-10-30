<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_company_id')->nullable()->constrained('user_companies')->nullOnDelete();
            $table->string('invoice_number');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('delivery_date');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_ico')->nullable();
            $table->string('company_dic')->nullable();
            $table->string('company_ic_dph')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_zip')->nullable();
            $table->string('company_country')->nullable()->default('Slovakia');
            $table->decimal('total_amount', 10);
            $table->string('currency')->default(Currency::EUR->value);
            $table->string('constant_symbol')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default(InvoiceStatus::DRAFT->value);

            $table->timestamps();

            // Invoice number must be unique per supplier company
            $table->unique(['supplier_company_id', 'invoice_number']);

            // Index for company ICO lookups
            $table->index('company_ico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
