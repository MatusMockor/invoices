<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        // Use first user and their current or first company
        $user = User::first();
        if (! $user) {
            return;
        }

        $company = Company::first();
        if (! $company) {
            return;
        }

        // Create a few contacts for the company
        $contacts = [
            ['first_name' => 'Jana', 'last_name' => 'Nováková', 'email' => 'jana.'.Str::random(5).'@example.com', 'phone' => '+421 900 111 222', 'position' => 'Purchasing Manager'],
            ['first_name' => 'Peter', 'last_name' => 'Kováč', 'email' => 'peter.'.Str::random(5).'@example.com', 'phone' => '+421 900 222 333', 'position' => 'CFO'],
            ['first_name' => 'Marek', 'last_name' => 'Horváth', 'email' => 'marek.'.Str::random(5).'@example.com', 'phone' => '+421 900 333 444', 'position' => 'CTO'],
        ];

        foreach ($contacts as $c) {
            $contact = Contact::create(array_merge($c, [
                'company_id' => $company->id,
                'user_id' => $user->id,
            ]));

            // Add a note for each contact
            Note::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'noteable_type' => Contact::class,
                'noteable_id' => $contact->id,
                'body' => 'Initial contact note for '.$contact->first_name,
            ]);
        }

        // Link first invoice to first contact if exists
        $invoice = Invoice::first();
        $firstContact = Contact::first();
        if ($invoice && $firstContact) {
            $invoice->contact()->associate($firstContact);
            $invoice->save();

            Note::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'noteable_type' => Invoice::class,
                'noteable_id' => $invoice->id,
                'body' => 'This invoice is linked to contact '.$firstContact->full_name,
            ]);
        }
    }
}
