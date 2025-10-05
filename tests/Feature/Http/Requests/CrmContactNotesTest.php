<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests;

use App\Models\Company;
use App\Models\User;
use App\Modules\CRM\Http\Requests\CrmContactCreateRequest;
use App\Modules\CRM\Http\Requests\CrmContactUpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CrmContactNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_have_multiple_notes_on_creation(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => [
                ['content' => 'First note about the contact'],
                ['content' => 'Second note with more details'],
                ['content' => 'Third note with additional information'],
            ],
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_contact_can_have_multiple_notes_on_update(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactUpdateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => [
                ['id' => 1, 'content' => 'Updated first note'],
                ['content' => 'New note added'],
                ['id' => 2, 'content' => 'Updated second note'],
            ],
        ], $request->rules(), $request->messages());

        if ($validator->fails()) {
            $this->fail('Validation failed: '.json_encode($validator->errors()->toArray()));
        }
        $this->assertFalse($validator->fails());
    }

    public function test_note_content_is_required_when_notes_are_provided(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => [
                ['content' => 'Valid note'],
                ['content' => ''], // Empty content should fail
                ['content' => 'Another valid note'],
            ],
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('notes.1.content', $validator->errors()->toArray());
    }

    public function test_note_content_cannot_exceed_maximum_length(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $longContent = str_repeat('a', 1001); // 1001 characters

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => [
                ['content' => $longContent],
            ],
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('notes.0.content', $validator->errors()->toArray());
    }

    public function test_notes_can_be_empty_array(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => [],
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_notes_can_be_null(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
            'notes' => null,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}
