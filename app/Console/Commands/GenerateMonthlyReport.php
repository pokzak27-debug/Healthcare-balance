<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MonthlyReportReadyNotification;
use App\Http\Controllers\ReportController;
use Illuminate\Console\Command;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'reports:monthly {--month= : Mois au format YYYY-MM (défaut: mois précédent)}';

    protected $description = 'Génère le rapport mensuel détaillé et notifie docteurs et super_admin';

    public function handle(): int
    {
        $month = $this->option('month') ?? now()->subMonth()->format('Y-m');

        $this->info("Génération du rapport mensuel pour {$month}...");

        try {
            $path = ReportController::generateDetailedReportPdf($month);
            $this->info("Rapport généré : {$path}");

            $users = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['super_admin', 'doctor']))
                ->where('is_active', true)
                ->get();

            foreach ($users as $user) {
                $user->notify(new MonthlyReportReadyNotification($month));
            }

            $this->info('Notifications envoyées à ' . $users->count() . ' utilisateur(s).');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erreur : ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
