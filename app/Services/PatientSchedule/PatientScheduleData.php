<?php

namespace App\Services\PatientSchedule;

use Illuminate\Support\Collection;

class PatientScheduleData
{
    public function __construct(
        /** @var Collection<int, object{date:\Carbon\CarbonInterface, time:?string, pole:?string}> */
        public Collection $items,
        public ?\Carbon\CarbonInterface $periodStart,
        public ?\Carbon\CarbonInterface $periodEnd,
        public int $totalSessions
    ) {
    }

    public function totalTreatmentDays(): ?int
    {
        if (! $this->periodStart || ! $this->periodEnd) {
            return null;
        }

        return $this->periodStart->diffInDays($this->periodEnd) + 1;
    }
}

