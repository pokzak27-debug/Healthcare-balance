<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /** 10 patients de test pour valider l'application. */
    public function run(): void
    {
        $patients = [
            ['first_name' => 'Fatima', 'last_name' => 'Alami', 'email' => 'fatima.alami@example.com', 'phone' => '0612345601'],
            ['first_name' => 'Mohammed', 'last_name' => 'Benali', 'email' => 'mohammed.benali@example.com', 'phone' => '0612345602'],
            ['first_name' => 'Amina', 'last_name' => 'El Idrissi', 'email' => 'amina.elidrissi@example.com', 'phone' => '0612345603'],
            ['first_name' => 'Omar', 'last_name' => 'Zouhri', 'email' => 'omar.zouhri@example.com', 'phone' => '0612345604'],
            ['first_name' => 'Laila', 'last_name' => 'Tazi', 'email' => 'laila.tazi@example.com', 'phone' => '0612345605'],
            ['first_name' => 'Hassan', 'last_name' => 'Chraibi', 'email' => 'hassan.chraibi@example.com', 'phone' => '0612345606'],
            ['first_name' => 'Samira', 'last_name' => 'Bennis', 'email' => 'samira.bennis@example.com', 'phone' => '0612345607'],
            ['first_name' => 'Karim', 'last_name' => 'Fassi', 'email' => 'karim.fassi@example.com', 'phone' => '0612345608'],
            ['first_name' => 'Nadia', 'last_name' => 'Lamrani', 'email' => 'nadia.lamrani@example.com', 'phone' => '0612345609'],
            ['first_name' => 'Youssef', 'last_name' => 'Ouardi', 'email' => 'youssef.ouardi@example.com', 'phone' => '0612345610'],
        ];

        foreach ($patients as $data) {
            Patient::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['birth_date' => now()->subYears(rand(25, 70))->format('Y-m-d')])
            );
        }
    }
}
