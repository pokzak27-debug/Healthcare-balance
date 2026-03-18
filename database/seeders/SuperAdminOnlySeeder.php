<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée un unique compte Super Admin pour la production.
 * Mot de passe : depuis APP_SUPER_ADMIN_PASSWORD ou "password" en secours.
 */
class SuperAdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'super_admin')->first();
        if (! $role) {
            $this->command?->warn('SuperAdminOnlySeeder : rôle super_admin absent. Exécutez RoleSeeder avant.');

            return;
        }

        $password = env('APP_SUPER_ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => 'admin@healthcare.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
