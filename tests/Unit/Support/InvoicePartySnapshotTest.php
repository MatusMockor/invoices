<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enums\VatPayerStatus;
use App\Support\InvoicePartySnapshot;
use stdClass;
use Tests\TestCase;

final class InvoicePartySnapshotTest extends TestCase
{
    public function test_normalize_rejects_non_stringable_leaf_values(): void
    {
        $snapshot = InvoicePartySnapshot::normalize([
            'supplier' => [
                'name' => [],
                'vat_payer_status' => [],
                'bank' => [
                    'iban' => [],
                    'swift' => new stdClass(),
                ],
            ],
            'customer' => [
                'name' => new stdClass(),
                'country' => ['SK'],
            ],
        ]);

        $this->assertNull($snapshot['supplier']['name']);
        $this->assertNull($snapshot['supplier']['vat_payer_status']);
        $this->assertNull($snapshot['supplier']['bank']['iban']);
        $this->assertNull($snapshot['supplier']['bank']['swift']);
        $this->assertNull($snapshot['customer']['name']);
        $this->assertNull($snapshot['customer']['country']);
    }

    public function test_normalize_still_accepts_backed_enums_and_stringable_values(): void
    {
        $stringable = new class
        {
            public function __toString(): string
            {
                return '  Example Company  ';
            }
        };

        $snapshot = InvoicePartySnapshot::normalize([
            'supplier' => [
                'name' => $stringable,
                'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            ],
        ]);

        $this->assertSame('Example Company', $snapshot['supplier']['name']);
        $this->assertSame(VatPayerStatus::VAT_PAYER->value, $snapshot['supplier']['vat_payer_status']);
    }
}
