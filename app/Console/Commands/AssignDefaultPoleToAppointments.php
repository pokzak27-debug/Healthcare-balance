<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Pole;
use Illuminate\Console\Command;

class AssignDefaultPoleToAppointments extends Command
{
    protected $signature = 'appointments:assign-default-pole {--dry-run : Afficher sans modifier}';

    protected $description = 'Assigne le premier pôle actif aux rendez-vous sans pôle';

    public function handle(): int
    {
        $pole = Pole::active()->ordered()->first();

        if (! $pole) {
            $this->error('Aucun pôle actif trouvé.');

            return 1;
        }

        $query = Appointment::whereNull('pole_id');
        $count = $query->count();

        if ($count === 0) {
            $this->info('Aucun rendez-vous sans pôle.');

            return 0;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry-run : {$count} rendez-vous seraient mis à jour avec le pôle : {$pole->name}");

            return 0;
        }

        $query->update(['pole_id' => $pole->id]);
        $this->info("{$count} rendez-vous mis à jour avec le pôle : {$pole->name}");

        return 0;
    }
}
