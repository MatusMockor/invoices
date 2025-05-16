<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Partner;
use App\Models\User;
use App\Services\InvoicePdfService;
use Barryvdh\DomPDF\PDF;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class InvoicePdfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->partner = Partner::factory()->create();
        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company->id,
            'partner_id' => $this->partner->id,
            'invoice_number' => 'INV-2025-001',
            'constant_symbol' => '0308',
            'delivery_date' => now(),
        ]);

        // Create some invoice items
        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $this->invoice->id,
        ]);
    }

    public function test_generate_pdf_returns_pdf_instance()
    {
        // Skip the test if we're in a CI environment
        if (getenv('CI')) {
            $this->markTestSkipped('Skipping PDF test in CI environment');
        }

        // Create the service
        $service = app()->make(InvoicePdfService::class);

        // Call the method
        $result = $service->generatePdf($this->invoice);

        // Assert the result is an object with PDF methods
        $this->assertIsObject($result);
        $this->assertTrue(method_exists($result, 'download'), 'PDF object should have download method');
        $this->assertTrue(method_exists($result, 'stream'), 'PDF object should have stream method');
    }

    public function test_download_pdf_returns_download_response()
    {
        // Create a mock of the PDF instance
        $pdfMock = Mockery::mock(PDF::class);
        $pdfMock->shouldReceive('download')
            ->once()
            ->with('invoice-INV-2025-001.pdf')
            ->andReturn(new Response('pdf content'));

        // Create a partial mock of the service
        $service = Mockery::mock(InvoicePdfService::class)->makePartial();
        $service->shouldReceive('generatePdf')
            ->once()
            ->with($this->invoice)
            ->andReturn($pdfMock);

        // Call the method
        $response = $service->downloadPdf($this->invoice);

        // Assert the response is a Response instance
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_stream_pdf_returns_stream_response()
    {
        // Create a mock of the PDF instance
        $pdfMock = Mockery::mock(PDF::class);
        $pdfMock->shouldReceive('stream')
            ->once()
            ->withNoArgs()
            ->andReturn(new Response('pdf content'));

        // Create a partial mock of the service
        $service = Mockery::mock(InvoicePdfService::class)->makePartial();
        $service->shouldReceive('generatePdf')
            ->once()
            ->with($this->invoice)
            ->andReturn($pdfMock);

        // Call the method
        $response = $service->streamPdf($this->invoice);

        // Assert the response is a Response instance
        $this->assertInstanceOf(Response::class, $response);
    }
}
