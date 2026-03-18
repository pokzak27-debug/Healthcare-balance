<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vide les tables patients et toutes les données liées (consultations/rendez-vous,
 * devis, factures, entrées médicales, etc.) en conservant la structure.
 * À utiliser pour préparer une base production propre.
 */
class DataWipeCommand extends Command
{
    protected $signature = 'data:wipe
                            {--force : Exécuter sans confirmation}';

    protected $description = 'Vide les tables patients, rendez-vous, devis, factures et données liées (structure conservée)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Vider toutes les données patients, rendez-vous, devis et factures ?', false)) {
            return self::SUCCESS;
        }

        $this->info('Nettoyage des données…');

        try {
            DB::beginTransaction();

            // Nullifier les références optionnelles avant suppression
            if (Schema::hasTable('staff_instructions')) {
                DB::table('staff_instructions')->update(['patient_id' => null, 'appointment_id' => null]);
            }
            if (Schema::hasTable('ai_assistant_logs') && Schema::hasColumn('ai_assistant_logs', 'patient_id')) {
                DB::table('ai_assistant_logs')->update(['patient_id' => null]);
            }
            if (Schema::hasTable('rooms')) {
                $nullable = ['current_appointment_id', 'current_patient_id', 'current_prestation_id', 'current_machine_id'];
                foreach ($nullable as $col) {
                    if (Schema::hasColumn('rooms', $col)) {
                        DB::table('rooms')->update([$col => null]);
                    }
                }
            }

            // Supprimer dans l’ordre des clés étrangères (enfants puis parents)
            $tables = [
                'patient_schedule_items',
                'patient_schedule_entries',
                'quotes',
                'invoices',
                'doctor_patient_consignes',
                'diagnostic_fishes',
                'medical_entries',
                'appointments',
                'patients',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->delete();
                    $this->line("  {$table}: {$count} ligne(s) supprimée(s).");
                }
            }

            DB::commit();
            $this->info('Data wipe terminé. La table patients et les données liées sont vides.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
