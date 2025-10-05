<?php

declare(strict_types=1);

namespace App\Modules\CRM\Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Modules\CRM\Models\CrmContact;
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
        $contacts = CrmContact::factory()->count(5)->create([
            'user_id' => auth()->id(),
        ]);

        // Make a request to the API index endpoint
        $response = $this->get(route('api.crm.contacts.index'));

        // Assert the response is successful
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'primary_email',
                    'primary_phone',
                    'job_title',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
        ]);

        // Assert we have the correct number of contacts
        $this->assertGreaterThanOrEqual(5, $response->json('data'));
    }

    /**
     * Test the store method validates required fields.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson(route('api.crm.contacts.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    /**
     * Test the bulkDelete method deletes multiple contacts.
     */
    public function test_bulk_delete_deletes_multiple_contacts(): void
    {
        $contacts = CrmContact::factory()->count(3)->create([
            'user_id' => auth()->id(),
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $response = $this->postJson(route('api.crm.contacts.bulk-delete'), [
            'contact_ids' => $contactIds,
        ]);

        $response->assertStatus(204);

        // Assert all contacts were soft deleted (deleted_at is not null)
        foreach ($contactIds as $contactId) {
            $this->assertDatabaseHas(CrmContact::class, [
                'id' => $contactId,
            ]);

            $contact = CrmContact::withTrashed()->find($contactId);
            $this->assertNotNull($contact->deleted_at);
        }
    }

    /**
     * Test the bulkUpdate method updates multiple contacts.
     */
    public function test_bulk_update_updates_multiple_contacts(): void
    {
        $contacts = CrmContact::factory()->count(3)->create([
            'user_id' => auth()->id(),
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $updateData = [
            'contact_ids' => $contactIds,
            'data' => [
                'job_title' => 'Updated Position',
                'is_active' => false,
            ],
        ];

        $response = $this->postJson(route('api.crm.contacts.bulk-update'), $updateData);

        $response->assertStatus(204);

        // Assert all contacts were updated in the database
        foreach ($contactIds as $contactId) {
            $this->assertDatabaseHas(CrmContact::class, [
                'id' => $contactId,
                'job_title' => 'Updated Position',
                'is_active' => false,
            ]);
        }
    }

    /**
     * Test the export method exports contacts.
     * TODO: Implement CSV export functionality
     */
    public function test_export_exports_contacts(): void
    {
        $this->markTestSkipped('CSV export functionality will be implemented later');

        // $contacts = CrmContact::factory()->count(2)->create([
        //     'user_id' => auth()->id(),
        // ]);

        // $contactIds = $contacts->pluck('id')->toArray();

        // $response = $this->postJson(route('api.crm.contacts.export'), [
        //     'contact_ids' => $contactIds,
        // ]);

        // $response->assertStatus(200);
        // $response->assertJsonStructure([
        //     'data' => [
        //         'download_url',
        //     ],
        // ]);
    }
}
