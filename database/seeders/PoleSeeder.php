<?php

namespace Database\Seeders;

use App\Models\Pole;
use Illuminate\Database\Seeder;

class PoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $poles = [
            ['name' => 'Ostéopathie', 'slug' => 'osteopathie', 'description' => 'Cabinet ostéopathie', 'sort_order' => 1],
            ['name' => 'Kinésithérapie', 'slug' => 'kinesitherapie', 'description' => 'Cabinet kinésithérapie', 'sort_order' => 2],
            ['name' => 'Esthétique médicale', 'slug' => 'esthetique-medicale', 'description' => 'Esthétique médicale', 'sort_order' => 3],
            ['name' => 'Massage médical', 'slug' => 'massage-medical', 'description' => 'Massage médical', 'sort_order' => 4],
        ];

        foreach ($poles as $pole) {
            Pole::updateOrCreate(
                ['slug' => $pole['slug']],
                array_merge($pole, ['is_active' => true])
            );
        }
    }
}
