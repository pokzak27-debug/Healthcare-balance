<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Catalogue production SPECTA : MedCryo PRO, EMSculpt.
 * Crée les salles et pôles nécessaires si absents, puis les machines et prestations liées.
 */
class SpectaCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $roomSoins = Room::firstOrCreate(
            ['name' => 'Salle soins'],
            ['slug' => 'salle-soins', 'is_active' => true, 'sort_order' => 1]
        );
        $roomEsthetique = Room::firstOrCreate(
            ['name' => 'Salle esthétique / appareils'],
            ['slug' => 'salle-esthetique', 'is_active' => true, 'sort_order' => 2]
        );

        $pole = Pole::where('slug', 'esthetique-medicale')->first();
        if (! $pole) {
            Pole::firstOrCreate(
                ['slug' => 'esthetique-medicale'],
                ['name' => 'Esthétique médicale', 'description' => 'Esthétique médicale', 'sort_order' => 1, 'is_active' => true]
            );
            $pole = Pole::where('slug', 'esthetique-medicale')->first();
        }

        $machines = [
            ['name' => 'MedCryo PRO', 'room' => $roomEsthetique, 'description' => 'Cryothérapie médicale', 'sort_order' => 1],
            ['name' => 'EMSculpt', 'room' => $roomEsthetique, 'description' => 'Sculpture corporelle', 'sort_order' => 2],
        ];

        foreach ($machines as $m) {
            $room = $m['room'];
            unset($m['room']);
            Machine::firstOrCreate(
                ['name' => $m['name']],
                [
                    'room_id' => $room->id,
                    'description' => $m['description'] ?? null,
                    'sort_order' => $m['sort_order'] ?? 0,
                    'is_active' => true,
                ]
            );
        }

        $prestation = Prestation::firstOrCreate(
            ['name' => 'MedCryo PRO / EMSculpt'],
            [
                'duration_minutes' => 45,
                'pole_id' => $pole?->id,
                'room_id' => $roomEsthetique->id,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $medcryo = Machine::where('name', 'MedCryo PRO')->first();
        $emsculpt = Machine::where('name', 'EMSculpt')->first();
        if ($prestation && ($medcryo || $emsculpt)) {
            $ids = array_filter([$medcryo?->id, $emsculpt?->id]);
            if (! empty($ids)) {
                $prestation->machines()->sync($ids);
            }
        }
    }
}
