<?php

declare(strict_types=1);

use App\Support\InvoicePartySnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', static function (Blueprint $table): void {
            $table->json('party_snapshot')->nullable();
        });

        $this->backfillPartySnapshot();

        Schema::table('invoices', static function (Blueprint $table): void {
            $table->dropIndex('invoices_company_ico_index');
            $table->dropColumn([
                'company_ico',
                'company_dic',
                'company_ic_dph',
                'company_name',
                'company_address',
                'company_city',
                'company_zip',
                'company_country',
                'supplier_registry_office',
                'supplier_registry_number',
                'supplier_vat_payer_status',
                'supplier_vat_period',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', static function (Blueprint $table): void {
            $table->string('company_ico')->nullable();
            $table->string('company_dic')->nullable();
            $table->string('company_ic_dph')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_zip')->nullable();
            $table->string('company_country')->nullable()->default('Slovakia');
            $table->string('supplier_registry_office', 255)->nullable();
            $table->string('supplier_registry_number', 100)->nullable();
            $table->string('supplier_vat_payer_status')->nullable();
            $table->string('supplier_vat_period')->nullable();
        });

        $this->restoreLegacySnapshotColumns();

        Schema::table('invoices', static function (Blueprint $table): void {
            $table->index('company_ico');
            $table->dropColumn('party_snapshot');
        });
    }

    private function backfillPartySnapshot(): void
    {
        DB::table('invoices')
            ->leftJoin('user_companies as supplier_companies', 'invoices.supplier_company_id', '=', 'supplier_companies.id')
            ->select([
                'invoices.id',
                'invoices.company_ico',
                'invoices.company_dic',
                'invoices.company_ic_dph',
                'invoices.company_name',
                'invoices.company_address',
                'invoices.company_city',
                'invoices.company_zip',
                'invoices.company_country',
                'invoices.supplier_registry_office',
                'invoices.supplier_registry_number',
                'invoices.supplier_vat_payer_status',
                'invoices.supplier_vat_period',
                'supplier_companies.name as supplier_name',
                'supplier_companies.ico as supplier_ico',
                'supplier_companies.dic as supplier_dic',
                'supplier_companies.ic_dph as supplier_ic_dph',
                'supplier_companies.street as supplier_street',
                'supplier_companies.city as supplier_city',
                'supplier_companies.postal_code as supplier_postal_code',
                'supplier_companies.country as supplier_country',
                'supplier_companies.iban as supplier_iban',
                'supplier_companies.swift as supplier_swift',
            ])
            ->orderBy('invoices.id')
            ->cursor()
            ->each(static function (object $invoice): void {
                $supplierSnapshot = InvoicePartySnapshot::supplierFromArray([
                    'name' => $invoice->supplier_name,
                    'ico' => $invoice->supplier_ico,
                    'dic' => $invoice->supplier_dic,
                    'ic_dph' => $invoice->supplier_ic_dph,
                    'street' => $invoice->supplier_street,
                    'city' => $invoice->supplier_city,
                    'postal_code' => $invoice->supplier_postal_code,
                    'country' => $invoice->supplier_country,
                    'registration_office' => $invoice->supplier_registry_office,
                    'registration_number' => $invoice->supplier_registry_number,
                    'bank' => [
                        'iban' => $invoice->supplier_iban,
                        'swift' => $invoice->supplier_swift,
                        'bank_name' => null,
                    ],
                ], $invoice->supplier_vat_payer_status, $invoice->supplier_vat_period);

                $customerSnapshot = InvoicePartySnapshot::customerFromArray([
                    'name' => $invoice->company_name,
                    'ico' => $invoice->company_ico,
                    'dic' => $invoice->company_dic,
                    'ic_dph' => $invoice->company_ic_dph,
                    'street' => $invoice->company_address,
                    'city' => $invoice->company_city,
                    'postal_code' => $invoice->company_zip,
                    'country' => $invoice->company_country,
                ]);

                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update([
                        'party_snapshot' => json_encode(
                            InvoicePartySnapshot::make($supplierSnapshot, $customerSnapshot)
                        ),
                    ]);
            });
    }

    private function restoreLegacySnapshotColumns(): void
    {
        DB::table('invoices')
            ->select(['id', 'party_snapshot'])
            ->orderBy('id')
            ->cursor()
            ->each(static function (object $invoice): void {
                $snapshot = is_string($invoice->party_snapshot)
                    ? json_decode($invoice->party_snapshot, true)
                    : $invoice->party_snapshot;

                $normalized = InvoicePartySnapshot::normalize(is_array($snapshot) ? $snapshot : null);

                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update([
                        'company_ico' => data_get($normalized, 'customer.ico'),
                        'company_dic' => data_get($normalized, 'customer.dic'),
                        'company_ic_dph' => data_get($normalized, 'customer.ic_dph'),
                        'company_name' => data_get($normalized, 'customer.name'),
                        'company_address' => data_get($normalized, 'customer.street'),
                        'company_city' => data_get($normalized, 'customer.city'),
                        'company_zip' => data_get($normalized, 'customer.postal_code'),
                        'company_country' => data_get($normalized, 'customer.country'),
                        'supplier_registry_office' => data_get($normalized, 'supplier.registration_office'),
                        'supplier_registry_number' => data_get($normalized, 'supplier.registration_number'),
                        'supplier_vat_payer_status' => data_get($normalized, 'supplier.vat_payer_status'),
                        'supplier_vat_period' => data_get($normalized, 'supplier.vat_period'),
                    ]);
            });
    }
};
