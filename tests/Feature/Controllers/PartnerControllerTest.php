<?php

namespace Tests\Feature\Controllers;

use App\Models\Partner;
use App\Models\User;
use App\Services\PartnerDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PartnerControllerTest extends TestCase
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
     * Test the index method displays a list of partners.
     */
    public function test_index_displays_partners_list(): void
    {
        // Create some partners
        $partners = Partner::factory()->count(3)->create();

        // Make a request to the index endpoint
        $response = $this->get(route('partners.index'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the view has the partners variable
        $response->assertViewHas('partners');

        // Assert the partners are displayed in the response
        foreach ($partners as $partner) {
            $response->assertSee($partner->name);
        }
    }

    /**
     * Test the create method displays the create form.
     */
    public function test_create_displays_form(): void
    {
        $response = $this->get(route('partners.create'));

        $response->assertStatus(200);
        $response->assertViewIs('partners.create');
    }

    /**
     * Test the store method creates a new partner.
     */
    public function test_store_creates_new_partner(): void
    {
        $partnerData = [
            'name' => $this->faker->company,
            'ico' => $this->faker->numerify('########'),
            'dic' => $this->faker->numerify('##########'),
            'ic_dph' => 'SK'.$this->faker->numerify('##########'),
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'registration_number' => $this->faker->numerify('######'),
            'company_type' => 'LLC',
        ];

        $response = $this->post(route('partners.store'), $partnerData);

        $response->assertRedirect(route('partners.index'));
        $response->assertSessionHas('success', 'Spoločnosť bola úspešne vytvorená');

        // Assert the partner was created in the database
        $this->assertDatabaseHas('partners', [
            'name' => $partnerData['name'],
            'ico' => $partnerData['ico'],
        ]);
    }

    /**
     * Test the show method response.
     */
    public function test_show_response(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partners.show', $partner));

        $response->assertStatus(200);
        $response->assertViewIs('partners.show');
        $response->assertViewHas('partner', $partner);
        $response->assertSee($partner->name);
    }

    /**
     * Test the edit method displays the edit form.
     */
    public function test_edit_displays_form(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partners.edit', $partner));

        $response->assertStatus(200);
        $response->assertViewIs('partners.edit');
        $response->assertViewHas('partner', $partner);
    }

    /**
     * Test the update method updates a partner.
     */
    public function test_update_updates_partner(): void
    {
        $partner = Partner::factory()->create();

        $updatedData = [
            'name' => 'Updated Company Name',
            'ico' => $partner->ico,
            'dic' => $partner->dic,
            'ic_dph' => $partner->ic_dph,
            'street' => 'Updated Address',
            'city' => $partner->city,
            'postal_code' => $partner->postal_code,
            'country' => $partner->country,
            'email' => $partner->email,
            'phone' => $partner->phone,
            'registration_number' => $partner->registration_number,
            'company_type' => $partner->company_type,
        ];

        $response = $this->put(route('partners.update', $partner), $updatedData);

        $response->assertRedirect(route('partners.index'));
        $response->assertSessionHas('success', 'Údaje spoločnosti boli úspešne aktualizované');

        // Assert the partner was updated in the database
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Updated Company Name',
            'street' => 'Updated Address',
        ]);
    }

    /**
     * Test the destroy method deletes a partner.
     */
    public function test_destroy_deletes_partner(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->delete(route('partners.destroy', $partner));

        $response->assertRedirect(route('partners.index'));
        $response->assertSessionHas('success', 'Spoločnosť bola úspešne vymazaná');

        // Assert the partner was deleted from the database
        $this->assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);
    }

    /**
     * Test the fetchByIco method returns partner data.
     */
    public function test_fetch_by_ico_returns_partner_data(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partners.fetch-by-ico', ['ico' => $partner->ico]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'name', 'ico', 'dic', 'ic_dph', 'street', 'city', 'postal_code', 'country', 'company_type', 'registration_number',
            ],
        ]);
        $response->assertJsonFragment([
            'name' => $partner->name,
            'ico' => $partner->ico,
        ]);
    }

    /**
     * Test the fetchByIco method returns a response for non-existent ICO.
     */
    public function test_fetch_by_ico_returns_response_for_nonexistent_ico(): void
    {
        // Mock the PartnerDataService to return null for a non-existent ICO
        $partnerDataService = $this->mock(PartnerDataService::class);
        $partnerDataService->shouldReceive('findOrCreatePartner')
            ->once()
            ->with('99999999')
            ->andReturn(null);

        $response = $this->getJson(route('partners.fetch-by-ico', ['ico' => '99999999']));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Údaje spoločnosti sa nenašli',
        ]);
    }
}
