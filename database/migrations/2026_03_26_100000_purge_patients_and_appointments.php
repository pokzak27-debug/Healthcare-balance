<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Supprime tous les rendez-vous et tous les patients. Conserve salles, machines, prestations. */
    public function up(): void
    {
        if (Schema::hasColumn('rooms', 'current_appointment_id')) {
            DB::table('rooms')->update([
                'current_appointment_id' => null,
                'current_patient_id' => null,
                'current_prestation_id' => null,
                'current_machine_id' => null,
                'occupied_started_at' => null,
            ]);
        }

        if (Schema::hasTable('staff_instructions')) {
            DB::table('staff_instructions')->update(['patient_id' => null, 'appointment_id' => null]);
        }
        if (Schema::hasTable('ai_assistant_logs') && Schema::hasColumn('ai_assistant_logs', 'patient_id')) {
            DB::table('ai_assistant_logs')->update(['patient_id' => null]);
        }

        if (Schema::hasTable('appointments')) {
            DB::table('appointments')->delete();
        }
        if (Schema::hasTable('patients')) {
            DB::table('patients')->delete();
        }
    }

    public function down(): void
    {
        // Irreversible.
    }
};
