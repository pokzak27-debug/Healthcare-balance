<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedTestPatients extends Command
{
    protected $signature = 'patients:seed-test {--fresh : Supprimer les patients existants avant}';

    protected $description = 'Ajoute 10 patients de test pour valider l\'application';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            \App\Models\Patient::truncate();
            $this->info('Patients existants supprimés.');
        }

        $this->call('db:seed', ['--class' => \Database\Seeders\PatientSeeder::class]);

        $count = \App\Models\Patient::count();
        $this->info("{$count} patients en base. Vous pouvez tester l'application.");

        return self::SUCCESS;
    }
}
