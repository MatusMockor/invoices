<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        foreach (Roles::values() as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Optionally assign the 'admin' role to the first (test) user if exists
        $user = User::first();
        if ($user) {
            $user->assignRole(Roles::ADMIN->value);
        }
    }
}
