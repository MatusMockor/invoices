<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\User;
use App\Modules\CRM\Models\CrmContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_contact_from_same_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['current_company_id' => $company->id]);
        $contact = CrmContact::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/crm/contacts/{$contact->id}", [
                'first_name' => 'Updated Name',
                'last_name' => 'Updated Last Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('crm_contacts', [
            'id' => $contact->id,
            'first_name' => 'Updated Name',
            'last_name' => 'Updated Last Name',
        ]);
    }

    public function test_user_cannot_update_contact_from_different_company(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->create(['current_company_id' => $company1->id]);
        $contact = CrmContact::factory()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/crm/contacts/{$contact->id}", [
                'first_name' => 'Updated Name',
                'last_name' => 'Updated Last Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_update_contact_assigned_to_them(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->create(['current_company_id' => $company1->id]);
        $contact = CrmContact::factory()->create([
            'company_id' => $company2->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/crm/contacts/{$contact->id}", [
                'first_name' => 'Updated Name',
                'last_name' => 'Updated Last Name',
            ]);

        $response->assertStatus(200);
    }
}
