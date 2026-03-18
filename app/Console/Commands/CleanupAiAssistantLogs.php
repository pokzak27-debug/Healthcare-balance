<?php

namespace App\Console\Commands;

use App\Models\AiAssistantLog;
use Illuminate\Console\Command;

class CleanupAiAssistantLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * - staff (assistante) : conservation 3 jours
     * - doctor (médecin) : conservation 1 mois
     * - super_admin : jamais supprimé automatiquement
     */
    protected $signature = 'ai-assistant:cleanup-logs';

    /**
     * The console command description.
     */
    protected $description = 'Supprime les logs de l’assistant IA selon des durées différentes par rôle.';

    public function handle(): int
    {
        $this->info('Nettoyage des logs Assistant IA…');

        $now = now();
        $staffCutoff = $now->copy()->subDays(3);
        $doctorCutoff = $now->copy()->subMonth();

        // Assistante (staff) : plus de 3 jours
        $this->cleanupForRole('staff', $staffCutoff);

        // Médecin (doctor) : plus de 1 mois
        $this->cleanupForRole('doctor', $doctorCutoff);

        // super_admin : aucune suppression automatique

        $this->info('Nettoyage terminé.');

        return self::SUCCESS;
    }

    protected function cleanupForRole(string $roleSlug, \Carbon\CarbonInterface $cutoff): void
    {
        $this->info(sprintf(
            ' - Suppression des logs pour le rôle %s antérieurs au %s…',
            $roleSlug,
            $cutoff->toDateTimeString()
        ));

        AiAssistantLog::whereHas('user.role', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            })
            ->where('created_at', '<', $cutoff)
            ->chunkById(500, function ($logs) {
                foreach ($logs as $log) {
                    $log->delete();
                }
            });
    }
}

