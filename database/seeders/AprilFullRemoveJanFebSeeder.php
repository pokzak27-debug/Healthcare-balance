<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Supprime les rendez-vous de janvier et février.
 * Remplit complètement le mois d'avril (tous les créneaux, tous les jours).
 */
class AprilFullRemoveJanFebSeeder extends Seeder
{
    private const APRIL_SLOTS = [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00',
    ];

    public function run(): void
    {
        $year = Carbon::now()->year;

        Appointment::whereYear('appointment_date', $year)
            ->whereMonth('appointment_date', 1)
            ->delete();
        Appointment::whereYear('appointment_date', $year)
            ->whereMonth('appointment_date', 2)
            ->delete();

        $this->command?->info("Rendez-vous de janvier et février {$year} supprimés.");

        $doctor = User::whereHas('role', fn ($q) => $q->where('slug', 'doctor'))->where('is_active', true)->first();
        $assistante = User::whereHas('role', fn ($q) => $q->where('slug', 'staff'))->where('is_active', true)->first();
        $patients = Patient::where('is_active', true)->get();
        $prestations = Prestation::active()->ordered()->get();
        $rooms = Room::active()->ordered()->get();
        $poles = Pole::where('is_active', true)->get();

        if ($patients->isEmpty() || $prestations->isEmpty() || $rooms->isEmpty() || ! $doctor || ! $assistante) {
            $this->command?->error('Patients, prestations, salles ou utilisateurs (docteur/assistante) manquants. Exécutez d\'abord les seeders de base et DemoMedicalDataSeeder.');

            return;
        }

        $aprilStart = Carbon::createFromDate($year, 4, 1);
        $aprilEnd = $aprilStart->copy()->endOfMonth();
        $created = 0;

        for ($date = $aprilStart->copy(); $date->lte($aprilEnd); $date->addDay()) {
            foreach (self::APRIL_SLOTS as $time) {
                $prestation = $prestations->random();
                $room = $prestation->room_id ? Room::find($prestation->room_id) : $rooms->random();
                $pole = $prestation->pole_id ? Pole::find($prestation->pole_id) : $poles->random();
                $user = $this->getUserForPrestation($prestation, $doctor, $assistante);

                Appointment::create([
                    'patient_id' => $patients->random()->id,
                    'pole_id' => $pole?->id,
                    'room_id' => $room?->id,
                    'prestation_id' => $prestation->id,
                    'user_id' => $user->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'appointment_time' => $time,
                    'duration_minutes' => $prestation->duration_minutes ?? 30,
                    'status' => 'completed',
                ]);
                $created++;
            }
        }

        $this->command?->info("Avril {$year} rempli : {$created} rendez-vous créés.");
    }

    private function getUserForPrestation(Prestation $prestation, User $doctor, User $assistante): User
    {
        $name = $prestation->name ?? '';
        $isOsteo = stripos($name, 'ostéopathie') !== false || stripos($name, 'osteopathie') !== false;

        return $isOsteo ? $doctor : $assistante;
    }
}
