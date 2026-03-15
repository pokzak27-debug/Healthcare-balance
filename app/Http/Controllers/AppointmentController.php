<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\ActivityLogger;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppointmentController extends Controller
{
    /**
     * Liste des rendez-vous.
     */
    public function index(Request $request): View
    {
        $query = Appointment::with(['patient', 'pole', 'room', 'prestation', 'user']);

        if ($search = $request->get('search')) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($poleId = $request->get('pole_id')) {
            $query->where('pole_id', $poleId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('appointment_date', '<=', $dateTo);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($roomId = $request->get('room_id')) {
            $query->where('room_id', $roomId);
        }

        $allowedSorts = ['appointment_date', 'status', 'created_at'];
        $allowedDirections = ['asc', 'desc'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'appointment_date';
        $direction = in_array($request->get('direction'), $allowedDirections) ? $request->get('direction') : 'desc';

        $query->orderBy($sort, $direction);
        if ($sort === 'appointment_date') {
            $query->orderBy('appointment_time', $direction);
        }
        $query->orderBy('id', 'desc');

        $appointments = $query->paginate(15)->withQueryString();
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::performers()->get();
        $rooms = Room::active()->ordered()->get();

        return view('appointments.index', compact('appointments', 'poles', 'practitioners', 'rooms'));
    }

    /**
     * Formulaire de création.
     */
    public function create(): View
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::performers()->get();
        $rooms = Room::active()->ordered()->get();
        $prestations = Prestation::active()->ordered()->get();
        $appointment = new Appointment();

        return view('appointments.create', compact('patients', 'poles', 'practitioners', 'rooms', 'prestations', 'appointment'));
    }

    /**
     * Enregistrement d'un nouveau rendez-vous.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'pole_id' => ['required', 'exists:poles,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:23:00'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90,120'],
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! empty($validated['prestation_id'])) {
            $prestation = Prestation::find($validated['prestation_id']);
            $validated['duration_minutes'] = $prestation->duration_minutes ?? ($validated['duration_minutes'] ?? 30);
        } else {
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        }
        $validated['duration_minutes'] = (int) ($validated['duration_minutes'] ?? 30);

        if ($this->hasRoomConflict($validated)) {
            return back()->withInput()->withErrors(['room_id' => 'Cette salle est déjà réservée pour ce créneau.']);
        }
        if ($err = $this->validatePrestationRoom($validated)) {
            return back()->withInput()->withErrors($err);
        }

        $appointment = Appointment::create($validated);
        ActivityLogger::log('appointment.created', "RDV créé pour le {$appointment->appointment_date->format('d/m/Y')} à {$appointment->appointment_time}", Appointment::class, $appointment->id, null, $validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous créé avec succès.');
    }

    /**
     * Détail d'un rendez-vous.
     */
    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'pole', 'room', 'prestation', 'user']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Marquer le patient comme arrivé à l'accueil.
     */
    public function markArrived(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status !== 'scheduled') {
            return back()->with('error', 'Seuls les rendez-vous prévus peuvent être marqués comme arrivés.');
        }
        if ($appointment->arrived_at) {
            return back()->with('info', 'Patient déjà marqué comme arrivé.');
        }
        $appointment->update(['arrived_at' => now()]);

        return back()->with('success', 'Patient marqué comme arrivé à l\'accueil.');
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Appointment $appointment): View
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        $poles = Pole::active()->ordered()->get();
        $practitioners = User::performers()->get();
        $rooms = Room::active()->ordered()->get();
        $prestations = Prestation::active()->ordered()->get();

        return view('appointments.edit', compact('appointment', 'patients', 'poles', 'practitioners', 'rooms', 'prestations'));
    }

    /**
     * Mise à jour d'un rendez-vous.
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'pole_id' => ['required', 'exists:poles,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'prestation_id' => ['nullable', 'exists:prestations,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:23:00'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90,120'],
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! empty($validated['prestation_id'])) {
            $prestation = Prestation::find($validated['prestation_id']);
            $validated['duration_minutes'] = $prestation->duration_minutes ?? ($validated['duration_minutes'] ?? 30);
        } else {
            $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        }
        $validated['duration_minutes'] = (int) ($validated['duration_minutes'] ?? 30);

        if ($this->hasRoomConflict($validated, $appointment->id)) {
            return back()->withInput()->withErrors(['room_id' => 'Cette salle est déjà réservée pour ce créneau.']);
        }
        if ($err = $this->validatePrestationRoom($validated)) {
            return back()->withInput()->withErrors($err);
        }

        $old = $appointment->toArray();
        $appointment->update($validated);
        ActivityLogger::log('appointment.updated', "RDV modifié #{$appointment->id}", Appointment::class, $appointment->id, $old, $validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous mis à jour.');
    }

    /**
     * Suppression d'un rendez-vous.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $id = $appointment->id;
        $desc = "RDV #{$id} supprimé";
        $appointment->delete();
        ActivityLogger::log('appointment.deleted', $desc, Appointment::class, $id);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous supprimé.');
    }

    /**
     * Calendrier des rendez-vous (mois, semaine ou jour).
     */
    public function calendar(Request $request): View
    {
        $view = $request->get('view', 'month');
        $roomId = $request->get('room_id');

        if ($view === 'day') {
            $date = \Carbon\Carbon::parse($request->get('date', now()->toDateString()));
            $aptQuery = Appointment::with(['patient', 'pole', 'room', 'user'])
                ->whereDate('appointment_date', $date);
            if ($roomId) {
                $aptQuery->where('room_id', $roomId);
            }
            $dayAppointments = $aptQuery->orderBy('appointment_time')->get();
            $prevDay = $date->copy()->subDay()->toDateString();
            $nextDay = $date->copy()->addDay()->toDateString();
            $rooms = Room::active()->ordered()->get();

            return view('appointments.calendar-day', compact('date', 'dayAppointments', 'prevDay', 'nextDay', 'rooms', 'roomId'));
        }

        $start = $view === 'week'
            ? \Carbon\Carbon::parse($request->get('week', now()->toDateString()))->startOfWeek(\Carbon\Carbon::MONDAY)
            : \Carbon\Carbon::parse($request->get('month', now()->format('Y-m')).'-01')->startOfMonth();
        $end = $view === 'week' ? $start->copy()->endOfWeek(\Carbon\Carbon::SUNDAY) : $start->copy()->endOfMonth();

        $aptQuery = Appointment::with(['patient', 'pole', 'room', 'user'])
            ->whereBetween('appointment_date', [$start, $end]);
        if ($roomId) {
            $aptQuery->where('room_id', $roomId);
        }
        $appointments = $aptQuery
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get()
            ->groupBy(fn ($a) => $a->appointment_date->format('Y-m-d'));

        $weeks = $view === 'week'
            ? [$this->buildWeekRow($start)]
            : $this->buildCalendarWeeks($start);

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');
        $prevWeek = $start->copy()->subWeek()->toDateString();
        $nextWeek = $start->copy()->addWeek()->toDateString();
        $rooms = Room::active()->ordered()->get();

        return view('appointments.calendar', compact('appointments', 'start', 'weeks', 'prevMonth', 'nextMonth', 'prevWeek', 'nextWeek', 'view', 'rooms'));
    }

    protected function buildWeekRow(\Carbon\Carbon $start): array
    {
        $row = [];
        for ($i = 0; $i < 7; $i++) {
            $row[] = $start->copy()->addDays($i);
        }
        return $row;
    }

    /**
     * Construit la grille des semaines pour le calendrier (début semaine = lundi).
     */
    protected function buildCalendarWeeks(\Carbon\Carbon $start): array
    {
        $firstDay = $start->copy()->startOfMonth();
        $lastDay = $start->copy()->endOfMonth();
        $begin = $firstDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $end = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $weeks = [];
        $current = $begin->copy();

        while ($current->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $current->copy();
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * Export PDF des rendez-vous (avec filtres).
     */
    public function exportPdf(Request $request)
    {
        $appointments = $this->buildExportQuery($request)->get();

        $pdf = Pdf::loadView('pdf.appointments', compact('appointments'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rendez-vous-'.now()->format('Y-m-d').'.pdf');
    }

    protected function buildExportQuery(Request $request)
    {
        $query = Appointment::with(['patient', 'pole', 'room', 'prestation', 'user'])
            ->orderBy('appointment_date')->orderBy('appointment_time');

        if ($search = trim($request->get('search') ?? '')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', fn ($sq) => $sq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"));
                $q->orWhere('reason', 'like', "%{$search}%");
            });
        }
        if ($poleId = $request->get('pole_id')) {
            $query->where('pole_id', $poleId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('appointment_date', '<=', $dateTo);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($roomId = $request->get('room_id')) {
            $query->where('room_id', $roomId);
        }

        return $query;
    }

    /**
     * Export CSV des rendez-vous (avec filtres).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildExportQuery($request);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rendez-vous-'.now()->format('Y-m-d').'.csv"',
        ];

        $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent'];

        return response()->stream(function () use ($query, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Date', 'Heure', 'Durée', 'Patient', 'Pôle', 'Salle', 'Prestation', 'Praticien', 'Statut', 'Motif', 'Notes']);

            foreach ($query->cursor() as $apt) {
                fputcsv($handle, [
                    $apt->appointment_date?->format('d/m/Y'),
                    $apt->appointment_time ? \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') : '',
                    ($apt->duration_minutes ?? 30) . ' min',
                    $apt->patient?->fullName() ?? '',
                    $apt->pole?->name ?? '',
                    $apt->room?->name ?? '',
                    $apt->prestation?->name ?? '',
                    $apt->user?->name ?? '',
                    $statusLabels[$apt->status] ?? $apt->status,
                    $apt->reason ?? '',
                    $apt->notes ?? '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /** Vérifie si une salle est déjà réservée sur le même créneau. */
    protected function hasRoomConflict(array $data, ?int $excludeAppointmentId = null): bool
    {
        if (empty($data['room_id'])) {
            return false;
        }
        $newStart = \Carbon\Carbon::parse($data['appointment_date'].' '.$data['appointment_time']);
        $newEnd = $newStart->copy()->addMinutes($data['duration_minutes'] ?? 30);

        $existing = Appointment::where('room_id', $data['room_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereIn('status', ['scheduled', 'completed']);
        if ($excludeAppointmentId) {
            $existing->where('id', '!=', $excludeAppointmentId);
        }
        $existing = $existing->get();

        foreach ($existing as $apt) {
            $aptStart = \Carbon\Carbon::parse($apt->appointment_date->format('Y-m-d').' '.$apt->appointment_time);
            $aptEnd = $aptStart->copy()->addMinutes($apt->duration_minutes ?? 30);
            if ($newStart->lt($aptEnd) && $newEnd->gt($aptStart)) {
                return true;
            }
        }

        return false;
    }

    protected function validatePrestationRoom(array $data): ?array
    {
        if (empty($data['prestation_id'])) {
            return null;
        }
        $prestation = Prestation::find($data['prestation_id']);
        if (! $prestation?->room_required || ! $prestation->room_id) {
            return null;
        }
        if (empty($data['room_id']) || (int) $data['room_id'] !== (int) $prestation->room_id) {
            return ['room_id' => "La prestation \"{$prestation->name}\" impose la salle {$prestation->room->name}."];
        }

        return null;
    }
}
