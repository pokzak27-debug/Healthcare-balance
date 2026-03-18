<?php

use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Transférer tous les RDV (et machines, prestations) de "Salle esthétique" vers "Salle esthétique / appareils", puis supprimer "Salle esthétique". */
    public function up(): void
    {
        if (! Schema::hasTable('rooms')) {
            return;
        }

        $source = Room::where('name', 'Salle esthétique')->first();
        $target = Room::where('name', 'Salle esthétique / appareils')->first();

        if (! $source || ! $target || $source->id === $target->id) {
            return;
        }

        $sourceId = $source->id;
        $targetId = $target->id;

        if (Schema::hasTable('appointments')) {
            DB::table('appointments')->where('room_id', $sourceId)->update(['room_id' => $targetId]);
        }
        if (Schema::hasTable('machines')) {
            DB::table('machines')->where('room_id', $sourceId)->update(['room_id' => $targetId]);
        }
        if (Schema::hasTable('prestations')) {
            DB::table('prestations')->where('room_id', $sourceId)->update(['room_id' => $targetId]);
        }
        if (Schema::hasTable('staff_instructions')) {
            DB::table('staff_instructions')->where('room_id', $sourceId)->update(['room_id' => $targetId]);
        }
        if (Schema::hasTable('doctor_patient_consignes')) {
            DB::table('doctor_patient_consignes')->where('room_id', $sourceId)->update(['room_id' => $targetId]);
        }
        if (Schema::hasColumn('rooms', 'current_appointment_id')) {
            DB::table('rooms')->where('id', $sourceId)->update([
                'current_appointment_id' => null,
                'current_patient_id' => null,
                'current_prestation_id' => null,
                'current_machine_id' => null,
                'occupied_started_at' => null,
            ]);
        }

        $source->delete();
    }

    public function down(): void
    {
        // Irreversible : la salle supprimée n'est pas recréée.
    }
};
