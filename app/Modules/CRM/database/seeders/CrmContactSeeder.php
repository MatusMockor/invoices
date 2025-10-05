<?php

declare(strict_types=1);

namespace App\Modules\CRM\Database\Seeders;

use App\Modules\CRM\Models\Company;
use App\Modules\CRM\Models\ContactActivity;
use App\Modules\CRM\Models\ContactAddress;
use App\Modules\CRM\Models\ContactCustomFieldDefinition;
use App\Modules\CRM\Models\ContactCustomFieldValue;
use App\Modules\CRM\Models\ContactEmail;
use App\Modules\CRM\Models\ContactPhone;
use App\Modules\CRM\Models\ContactTag;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Models\User;
use Illuminate\Database\Seeder;

class CrmContactSeeder extends Seeder
{
    public function run(): void
    {
        // Create custom field definitions
        $customFields = [
            [
                'name' => 'Lead Source',
                'slug' => 'lead_source',
                'type' => 'select',
                'options' => ['website', 'referral', 'cold_call', 'event', 'social_media'],
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'How did this contact find us?',
            ],
            [
                'name' => 'Lead Score',
                'slug' => 'lead_score',
                'type' => 'number',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Lead scoring from 1-100',
            ],
            [
                'name' => 'Industry',
                'slug' => 'industry',
                'type' => 'select',
                'options' => ['technology', 'finance', 'healthcare', 'education', 'retail', 'manufacturing'],
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 3,
                'description' => 'Contact industry',
            ],
            [
                'name' => 'Budget Range',
                'slug' => 'budget_range',
                'type' => 'select',
                'options' => ['< 10k', '10k-50k', '50k-100k', '100k-500k', '> 500k'],
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 4,
                'description' => 'Estimated budget range',
            ],
        ];

        foreach ($customFields as $field) {
            ContactCustomFieldDefinition::create($field);
        }

        // Create tags
        $tags = [
            ['name' => 'Hot Lead', 'color' => '#EF4444', 'description' => 'High priority leads'],
            ['name' => 'Cold Lead', 'color' => '#6B7280', 'description' => 'Low priority leads'],
            ['name' => 'Customer', 'color' => '#10B981', 'description' => 'Existing customers'],
            ['name' => 'Prospect', 'color' => '#3B82F6', 'description' => 'Potential customers'],
            ['name' => 'VIP', 'color' => '#F59E0B', 'description' => 'VIP customers'],
            ['name' => 'Partner', 'color' => '#8B5CF6', 'description' => 'Business partners'],
        ];

        foreach ($tags as $tag) {
            ContactTag::create($tag);
        }

        // Get existing companies and users
        $companies = Company::all();
        $users = User::all();
        $tagModels = ContactTag::all();
        $customFieldModels = ContactCustomFieldDefinition::all();

        if ($companies->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No companies or users found. Please run CompanySeeder and UserSeeder first.');

            return;
        }

        // Create contacts
        $contacts = CrmContact::factory()
            ->count(50)
            ->create([
                'company_id' => $companies->random()->id,
                'user_id' => $users->random()->id,
            ]);

        foreach ($contacts as $contact) {
            // Add additional emails
            ContactEmail::factory()
                ->count(fake()->numberBetween(0, 2))
                ->create(['contact_id' => $contact->id]);

            // Add additional phones
            ContactPhone::factory()
                ->count(fake()->numberBetween(0, 2))
                ->create(['contact_id' => $contact->id]);

            // Add addresses
            ContactAddress::factory()
                ->count(fake()->numberBetween(1, 2))
                ->create(['contact_id' => $contact->id]);

            // Assign random tags
            $randomTags = $tagModels->random(fake()->numberBetween(1, 3));
            $contact->tags()->attach($randomTags->pluck('id'));

            // Add custom field values
            foreach ($customFieldModels as $field) {
                if (fake()->boolean(70)) {
                    $value = match ($field->type) {
                        'select' => fake()->randomElement($field->options),
                        'number' => fake()->numberBetween(1, 100),
                        'text' => fake()->sentence(),
                        'boolean' => fake()->boolean(),
                        default => fake()->word(),
                    };

                    ContactCustomFieldValue::create([
                        'contact_id' => $contact->id,
                        'field_definition_id' => $field->id,
                        'value' => (string) $value,
                    ]);
                }
            }

            // Add some activities
            ContactActivity::factory()
                ->count(fake()->numberBetween(1, 5))
                ->create([
                    'contact_id' => $contact->id,
                    'user_id' => $users->random()->id,
                ]);
        }

        $this->command->info('CRM Contacts seeded successfully!');
        $this->command->info("Created {$contacts->count()} contacts with related data.");
    }
}
