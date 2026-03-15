<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\PractitionerAbsence;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailableSlotsService
{
    /**
     * Retourne les créneaux horaires disponibles pour une date, pôle et optionnellement un praticien.
     *
     * @return array<string> Heures au format H:i
     */
    public function getAvailableTimes(string $date, int $poleId, ?int $userId, int $durationMinutes = 30): array
    {
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeekIso; // 1=lundi, 7=dimanche

        $slots = AvailabilitySlot::where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($poleId, $userId) {
                $q->where('pole_id', $poleId);
                if ($userId) {
                    $q->where(function ($sq) use ($userId) {
                        $sq->where('user_id', $userId)->orWhereNull('user_id');
                    });
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->get();

        $allTimes = new Collection;

        foreach ($slots as $slot) {
            $times = $this->generateTimesFromSlot($slot);
            $allTimes = $allTimes->merge($times)->unique()->sort()->values();
        }

        if ($userId && $this->isPractitionerAbsent($date, $userId)) {
            return [];
        }

        $busy = $this->getBusyTimes($date, $poleId, $userId);

        return $allTimes->filter(function ($time) use ($date, $durationMinutes, $busy) {
            $end = Carbon::parse("{$date} {$time}")->addMinutes($durationMinutes)->format('H:i');
            return ! $this->overlapsBusy($time, $end, $busy);
        })->values()->all();
    }

    protected function generateTimesFromSlot(AvailabilitySlot $slot): Collection
    {
        $times = collect();
        $start = Carbon::parse($slot->start_time);
        $end = Carbon::parse($slot->end_time);
        $step = $slot->slot_minutes ?? 30;

        $current = $start->copy();
        while ($current->copy()->addMinutes($step)->lte($end)) {
            $times->push($current->format('H:i'));
            $current->addMinutes($step);
        }

        return $times;
    }

    protected function getBusyTimes(string $date, int $poleId, ?int $userId): array
    {
        $query = Appointment::whereDate('appointment_date', $date)
            ->where('pole_id', $poleId)
            ->whereIn('status', ['scheduled']);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id');
        }

        return $query->get()->map(function ($apt) {
            $end = Carbon::parse($apt->appointment_time)->addMinutes($apt->duration_minutes ?? 30)->format('H:i');
            return ['start' => $apt->appointment_time, 'end' => $end];
        })->all();
    }

    protected function isPractitionerAbsent(string $date, int $userId): bool
    {
        return PractitionerAbsence::where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    protected function overlapsBusy(string $start, string $end, array $busy): bool
    {
        foreach ($busy as $b) {
            if (! ($end <= $b['start'] || $start >= $b['end'])) {
                return true;
            }
        }
        return false;
    }
}
