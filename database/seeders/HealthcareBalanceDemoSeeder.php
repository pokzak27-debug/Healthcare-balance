<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Conforme à https://healthcarebalance.ma/
 * - Crée salles, machines (Medley 5D, HydraBeauty PRO, Ultraslim, etc.), prestations
 * - Génère 6 à 8 rendez-vous par jour sur tout le mois (courant + suivant) pour tester
 */
class HealthcareBalanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureRooms();
        $roomSoins = Room::where('name', 'Salle soins')->first();
        $roomEsthetique = Room::where('name', 'Salle esthétique / appareils')->first();
        if (! $roomSoins || ! $roomEsthetique) {
            $this->command?->error('Rooms non trouvées après ensureRooms.');
            return;
        }

        $this->ensureMachines($roomSoins, $roomEsthetique);
        $this->ensurePrestations();
        $this->linkPrestationsMachines();
    }

    protected function ensureRooms(): void
    {
        Room::firstOrCreate(
            ['name' => 'Salle soins'],
            ['slug' => 'salle-soins', 'is_active' => true, 'sort_order' => 1]
        );
        Room::firstOrCreate(
            ['name' => 'Salle esthétique / appareils'],
            ['slug' => 'salle-esthetique', 'is_active' => true, 'sort_order' => 2]
        );
    }

    protected function ensureMachines(Room $roomSoins, Room $roomEsthetique): void
    {
        $machines = [
            ['name' => 'Medley 5D', 'room' => $roomEsthetique, 'description' => 'Plateforme multi-technologies', 'sort_order' => 1],
            ['name' => 'HydraBeauty PRO', 'room' => $roomEsthetique, 'description' => 'Soins hydratation et éclat', 'sort_order' => 2],
            ['name' => 'Ultraslim PRO', 'room' => $roomEsthetique, 'description' => 'Amincissement et remodelage', 'sort_order' => 3],
            ['name' => 'Ultraslim Lift', 'room' => $roomEsthetique, 'description' => 'Lifting non chirurgical', 'sort_order' => 4],
            ['name' => 'IMOOS', 'room' => $roomEsthetique, 'description' => 'Stimulation musculaire', 'sort_order' => 5],
            ['name' => 'Sculpt PRO (version noire)', 'room' => $roomEsthetique, 'description' => 'Sculpture corporelle', 'sort_order' => 6],
            ['name' => 'MedCryo PRO', 'room' => $roomEsthetique, 'description' => 'Cryothérapie médicale', 'sort_order' => 7],
            ['name' => 'Ultraslim Max', 'room' => $roomEsthetique, 'description' => 'Amincissement intensif', 'sort_order' => 8],
            ['name' => 'Electa Air', 'room' => $roomEsthetique, 'description' => 'Électrostimulation', 'sort_order' => 9],
            ['name' => 'InBody (balance + logiciel)', 'room' => $roomSoins, 'description' => 'Analyse composition corporelle — conforme healthcarebalance.ma', 'sort_order' => 10],
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
    }

    protected function ensurePrestations(): void
    {
        $poles = [
            'osteopathie' => Pole::where('slug', 'osteopathie')->first(),
            'kinesitherapie' => Pole::where('slug', 'kinesitherapie')->first(),
            'esthetique-medicale' => Pole::where('slug', 'esthetique-medicale')->first(),
            'massage-medical' => Pole::where('slug', 'massage-medical')->first(),
        ];

        $roomSoins = Room::where('name', 'Salle soins')->first();
        $roomEsthetique = Room::where('name', 'Salle esthétique / appareils')->first();

        $prestations = [
            ['name' => 'Ostéopathie', 'duration_minutes' => 45, 'pole' => 'osteopathie', 'room' => $roomSoins, 'sort_order' => 1],
            ['name' => 'Kinésithérapie / Physiothérapie', 'duration_minutes' => 45, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 2],
            ['name' => 'Thérapie manuelle', 'duration_minutes' => 45, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 3],
            ['name' => 'Dry Needling', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 4],
            ['name' => 'Cupping therapy médicale', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 5],
            ['name' => 'Crochetage myo-aponévrotique', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 6],
            ['name' => 'Mulligan MWM (A-B et C sports)', 'duration_minutes' => 45, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 7],
            ['name' => 'Técar thérapie', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 8],
            ['name' => 'Onde de choc', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 9],
            ['name' => 'Drainage lymphatique', 'duration_minutes' => 45, 'pole' => 'massage-medical', 'room' => $roomSoins, 'sort_order' => 10],
            ['name' => 'Rééducation périnéale et périnatalité', 'duration_minutes' => 45, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 11],
            ['name' => 'Kiné respiratoire', 'duration_minutes' => 30, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 12],
            ['name' => 'Nutrition / Amincissement', 'duration_minutes' => 45, 'pole' => 'esthetique-medicale', 'room' => $roomSoins, 'sort_order' => 13],
            ['name' => 'Coaching sportif personnalisé', 'duration_minutes' => 60, 'pole' => 'kinesitherapie', 'room' => $roomSoins, 'sort_order' => 14],
            ['name' => 'Yoga / Pilates', 'duration_minutes' => 60, 'pole' => 'massage-medical', 'room' => $roomSoins, 'sort_order' => 15],
            ['name' => 'Soins médico-esthétiques (Hydrafacial)', 'duration_minutes' => 60, 'pole' => 'esthetique-medicale', 'room' => $roomEsthetique, 'sort_order' => 16],
            ['name' => 'Amincissement (Ultraslim / MedCryo)', 'duration_minutes' => 45, 'pole' => 'esthetique-medicale', 'room' => $roomEsthetique, 'sort_order' => 17],
            ['name' => 'Bilan InBody', 'duration_minutes' => 30, 'pole' => 'esthetique-medicale', 'room' => $roomSoins, 'sort_order' => 18],
        ];

        foreach ($prestations as $p) {
            $pole = $poles[$p['pole']] ?? null;
            $room = $p['room'];
            Prestation::updateOrCreate(
                ['name' => $p['name']],
                [
                    'duration_minutes' => $p['duration_minutes'],
                    'pole_id' => $pole?->id,
                    'room_id' => $room?->id,
                    'is_active' => true,
                    'sort_order' => $p['sort_order'],
                ]
            );
        }
    }

    protected function linkPrestationsMachines(): void
    {
        $links = [
            'Soins médico-esthétiques (Hydrafacial)' => ['HydraBeauty PRO'],
            'Amincissement (Ultraslim / MedCryo)' => ['Ultraslim PRO', 'Ultraslim Lift', 'Ultraslim Max', 'MedCryo PRO'],
            'Bilan InBody' => ['InBody (balance + logiciel)'],
            'Técar thérapie' => ['Medley 5D'],
            'Coaching sportif personnalisé' => ['IMOOS', 'Sculpt PRO (version noire)', 'Electa Air'],
        ];

        foreach ($links as $prestationName => $machineNames) {
            $prestation = Prestation::where('name', $prestationName)->first();
            if (! $prestation) {
                continue;
            }
            $ids = Machine::whereIn('name', $machineNames)->pluck('id')->toArray();
            if (! empty($ids)) {
                $prestation->machines()->syncWithoutDetaching($ids);
            }
        }
    }
}
