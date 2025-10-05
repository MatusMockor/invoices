<?php

declare(strict_types=1);

namespace App\Modules\CRM\Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Modules\CRM\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiContactControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user for the tests
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Test the apiIndex method returns paginated contacts.
     */
    public function test_api_index_returns_paginated_contacts(): void
    {
        // Create some contacts for the authenticated user
        $contacts = Contact::factory()->count(5)->create([
            'user_id' => auth()->id(),
        ]);

        // Make a request to the API index endpoint
        $response = $this->get(route('api.contacts.index'));

        // Assert the response is successful
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'position',
                    'notes',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
        ]);

        // Assert we have the correct number of contacts
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test the store method validates required fields.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson(route('api.contacts.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    /**
     * Test the bulkDelete method deletes multiple contacts.
     */
    public function test_bulk_delete_deletes_multiple_contacts(): void
    {
        $contacts = Contact::factory()->count(3)->create([
            'user_id' => auth()->id(),
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $response = $this->postJson(route('api.contacts.bulk-delete'), [
            'contact_ids' => $contactIds,
        ]);

        $response->assertStatus(200);

        // Assert all contacts were deleted from the database
        foreach ($contactIds as $contactId) {
            $this->assertDatabaseMissing(Contact::class, [
                'id' => $contactId,
            ]);
        }
    }

    /**
     * Test the bulkUpdate method updates multiple contacts.
     */
    public function test_bulk_update_updates_multiple_contacts(): void
    {
        $contacts = Contact::factory()->count(3)->create([
            'user_id' => auth()->id(),
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $updateData = [
            'contact_ids' => $contactIds,
            'data' => [
                'position' => 'Updated Position',
                'notes' => 'Bulk updated notes',
            ],
        ];

        $response = $this->postJson(route('api.contacts.bulk-update'), $updateData);

        $response->assertStatus(200);

        // Assert all contacts were updated in the database
        foreach ($contactIds as $contactId) {
            $this->assertDatabaseHas(Contact::class, [
                'id' => $contactId,
                'position' => 'Updated Position',
                'notes' => 'Bulk updated notes',
            ]);
        }
    }

    /**
     * Test the export method exports contacts.
     */
    public function test_export_exports_contacts(): void
    {
        $contacts = Contact::factory()->count(2)->create([
            'user_id' => auth()->id(),
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $response = $this->postJson(route('api.contacts.export'), [
            'contact_ids' => $contactIds,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'download_url',
        ]);
    }
}
