<?php

namespace Tests\Unit\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Repositories\InvoiceItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InvoiceItemRepository();
    }

    public function test_delete_items_not_in_ids_with_empty_array_deletes_all_items(): void
    {
        // Create an invoice
        $invoice = Invoice::factory()->create();
        
        // Create some invoice items
        $item1 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item2 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item3 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        
        // Call the method with an empty array
        $result = $this->repository->deleteItemsNotInIds($invoice->id, []);
        
        // Assert that all items were deleted
        $this->assertTrue($result);
        $this->assertDatabaseMissing(InvoiceItem::class, ['id' => $item1->id]);
        $this->assertDatabaseMissing(InvoiceItem::class, ['id' => $item2->id]);
        $this->assertDatabaseMissing(InvoiceItem::class, ['id' => $item3->id]);
    }

    public function test_delete_items_not_in_ids_with_some_ids_deletes_only_those_items(): void
    {
        // Create an invoice
        $invoice = Invoice::factory()->create();
        
        // Create some invoice items
        $item1 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item2 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item3 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        
        // Call the method with some IDs
        $result = $this->repository->deleteItemsNotInIds($invoice->id, [$item1->id, $item3->id]);
        
        // Assert that only the items not in the list were deleted
        $this->assertTrue($result);
        $this->assertDatabaseHas(InvoiceItem::class, ['id' => $item1->id]);
        $this->assertDatabaseMissing(InvoiceItem::class, ['id' => $item2->id]);
        $this->assertDatabaseHas(InvoiceItem::class, ['id' => $item3->id]);
    }
}