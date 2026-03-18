<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    /**
     * Seed production : rôles, un seul Super Admin, catalogue SPECTA (MedCryo PRO, EMSculpt).
     * Pour données de démo (patients, rendez-vous, etc.) exécuter en plus :
     *   php artisan db:seed --class=UserSeeder
     *   php artisan db:seed --class=PoleSeeder
     *   php artisan db:seed --class=HealthcareBalanceDemoSeeder
     *   php artisan db:seed --class=PatientSeeder
     *   php artisan db:seed --class=DemoMedicalDataSeeder
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PoleSeeder::class,
            SuperAdminOnlySeeder::class,
            SpectaCatalogSeeder::class,
        ]);
    }
}
