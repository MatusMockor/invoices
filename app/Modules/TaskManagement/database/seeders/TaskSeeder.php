<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Modules\TaskManagement\Factories\FollowUpFactory;
use App\Modules\TaskManagement\Factories\TaskFactory;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        $users = User::all();

        if ($companies->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No companies or users found. Please seed companies and users first.');

            return;
        }

        $this->command->info('Creating tasks for existing companies and users...');

        foreach ($companies as $company) {
            $companyUsers = $users->random(min(3, $users->count()));

            foreach ($companyUsers as $user) {
                TaskFactory::new()
                    ->count(5)
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create()
                    ->each(function ($task) use ($user) {
                        if (fake()->boolean(60)) {
                            FollowUpFactory::new()
                                ->count(fake()->numberBetween(1, 3))
                                ->state([
                                    'task_id' => $task->id,
                                    'user_id' => $user->id,
                                ])
                                ->create();
                        }
                    });

                TaskFactory::new()
                    ->count(2)
                    ->pending()
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create();

                TaskFactory::new()
                    ->count(2)
                    ->inProgress()
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create();

                TaskFactory::new()
                    ->count(3)
                    ->completed()
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create();

                TaskFactory::new()
                    ->count(1)
                    ->overdue()
                    ->urgent()
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create();

                TaskFactory::new()
                    ->count(2)
                    ->high()
                    ->state([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'assigned_to' => $companyUsers->random()->id,
                    ])
                    ->create();
            }
        }

        $this->command->info('Tasks seeded successfully!');
    }
}
