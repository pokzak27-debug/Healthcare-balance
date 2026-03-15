<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prestation;
use App\Models\Room;
use Illuminate\View\View;

class LiveCabinetController extends Controller
{
    /** API JSON pour le polling du cabinet live. */
    public function data(): \Illuminate\Http\JsonResponse
    {
        $today = now()->toDateString();
        $rooms = \App\Models\Room::active()->ordered()->withCount('machines')
            ->with(['machines' => fn ($q) => $q->ordered(), 'currentAppointment.patient', 'currentAppointment.prestation', 'currentPatient', 'currentPrestation', 'currentMachine'])
            ->get()->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->status ?? 'free',
                'machines_count' => $r->machines_count ?? 0,
                'machines' => $r->machines->map(fn ($m) => ['id' => $m->id, 'name' => $m->name]),
                'current_machine_id' => $r->current_machine_id,
                'patient' => ($r->currentPatient ?? $r->currentAppointment?->patient)?->fullName(),
                'prestation' => ($r->currentPrestation ?? $r->currentAppointment?->prestation)?->name,
                'machine' => $r->currentMachine?->name,
                'occupied_started_at' => $r->occupied_started_at?->timestamp,
            ]);
        $appointments = \App\Models\Appointment::with(['patient', 'pole', 'room', 'prestation', 'user'])
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderBy('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'time' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                'patient' => $a->patient->fullName(),
                'patient_id' => $a->patient_id,
                'status' => $a->status,
                'arrived_at' => $a->arrived_at?->format('H:i'),
                'prestation' => $a->prestation?->name ?? $a->pole?->name ?? '—',
                'prestation_id' => $a->prestation_id,
                'room' => $a->room?->name,
                'room_id' => $a->room_id,
                'user' => $a->user?->name,
                'url' => route('appointments.show', $a),
            ]);
        $patientsAtReception = \App\Models\Appointment::with(['patient', 'room', 'prestation'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'scheduled')
            ->whereNotNull('arrived_at')
            ->orderBy('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'patient_id' => $a->patient_id,
                'time' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                'patient' => $a->patient->fullName(),
                'room_id' => $a->room_id,
                'prestation_id' => $a->prestation_id,
            ]);
        return response()->json([
            'rooms' => $rooms,
            'appointments' => $appointments,
            'patientsAtReception' => $patientsAtReception,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /** Vue live du cabinet : salles, RDV en cours, activités récentes. */
    public function index(): View
    {
        $today = now()->toDateString();
        $rooms = Room::active()->ordered()->withCount('machines')
            ->with(['machines' => fn ($q) => $q->active()->ordered(), 'currentAppointment.patient', 'currentAppointment.prestation', 'currentPatient', 'currentPrestation', 'currentMachine'])
            ->get();
        $todayAppointments = Appointment::with(['patient', 'pole', 'room', 'prestation', 'user'])
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderBy('appointment_time')
            ->get();
        $roomsJson = $rooms->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'status' => $r->status ?? 'free',
            'machines_count' => $r->machines_count ?? 0,
            'machines' => $r->machines->map(fn ($m) => ['id' => $m->id, 'name' => $m->name]),
            'current_machine_id' => $r->current_machine_id,
            'patient' => ($r->currentPatient ?? $r->currentAppointment?->patient)?->fullName(),
            'prestation' => ($r->currentPrestation ?? $r->currentAppointment?->prestation)?->name,
            'machine' => $r->currentMachine?->name,
            'occupied_started_at' => $r->occupied_started_at?->timestamp,
        ]);
        $patientsAtReceptionList = $todayAppointments->filter(fn ($a) => $a->status === 'scheduled' && $a->arrived_at !== null);
        $appointmentsJson = $todayAppointments->map(fn ($a) => [
            'id' => $a->id,
            'time' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
            'patient' => $a->patient->fullName(),
            'patient_id' => $a->patient_id,
            'status' => $a->status,
            'arrived_at' => $a->arrived_at?->format('H:i'),
            'prestation' => $a->prestation?->name ?? $a->pole?->name ?? '—',
            'room' => $a->room?->name,
            'user' => $a->user?->name,
            'url' => route('appointments.show', $a),
        ]);
        $accueilJson = $patientsAtReceptionList->map(fn ($a) => [
            'id' => $a->id,
            'patient_id' => $a->patient_id,
            'time' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
            'patient' => $a->patient->fullName(),
            'room_id' => $a->room_id,
            'prestation_id' => $a->prestation_id,
        ]);
        $appointmentsForRooms = Appointment::with(['patient', 'prestation', 'pole'])
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('room_id')
            ->orderBy('appointment_time')
            ->get();
        $appointmentsByRoom = $appointmentsForRooms->groupBy('room_id');
        $patients = Patient::where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $prestations = Prestation::active()->ordered()->get(['id', 'name']);
        $isDoctor = can_access_dossier();

        return view('live-cabinet.index', compact(
            'rooms', 'roomsJson', 'appointmentsJson', 'accueilJson', 'appointmentsByRoom', 'patients', 'prestations', 'isDoctor'
        ));
    }
}
