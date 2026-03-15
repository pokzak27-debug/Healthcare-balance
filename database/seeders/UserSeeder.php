<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Crée un compte pour chaque rôle : super_admin, doctor, staff.
     * Mot de passe par défaut : password
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@healthcare.local',
                'role_slug' => 'super_admin',
            ],
            [
                'name' => 'Docteur',
                'email' => 'doctor@healthcare.local',
                'role_slug' => 'doctor',
            ],
            [
                'name' => 'Assistante',
                'email' => 'staff@healthcare.local',
                'role_slug' => 'staff',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::where('slug', $data['role_slug'])->first();
            if (!$role) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'role_id' => $role->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
