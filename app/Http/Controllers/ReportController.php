<?php

namespace App\Http\Controllers;

use App\Exports\RoomPrestationReportExport;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Prestation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /** Rapport mensuel détaillé (pour commande et notification). */
    public static function generateDetailedReportPdf(string $month): string
    {
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $ca = (float) Invoice::where('status', 'paid')->whereBetween('invoice_date', [$start, $end])->sum('amount');
        $invoices = Invoice::with(['appointment.prestation', 'appointment.pole'])
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->get();
        $invoicesCount = $invoices->count();
        $caByPrestation = $invoices->groupBy(fn ($i) => $i->appointment?->prestation_id ?? 0)
            ->map(fn ($group, $prestationId) => [
                'prestation' => $prestationId
                    ? ($group->first()->appointment?->prestation?->name ?? $group->first()->appointment?->pole?->name ?? '—')
                    : 'Sans prestation',
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();

        $statsByStatus = Appointment::whereBetween('appointment_date', [$start, $end])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $appointmentsTotal = array_sum($statsByStatus);
        $appointmentsCompleted = $statsByStatus['completed'] ?? 0;

        $byPole = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereNotNull('pole_id')
            ->join('poles', 'appointments.pole_id', '=', 'poles.id')
            ->select('poles.name', DB::raw('count(*) as total'))
            ->groupBy('poles.id', 'poles.name')
            ->orderByRaw('count(*) desc')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'total' => $r->total])
            ->toArray();

        $byRoom = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('room_id')
            ->select('room_id', DB::raw('count(*) as total'))
            ->groupBy('room_id')
            ->with('room')
            ->get()
            ->map(fn ($r) => ['name' => $r->room?->name ?? 'Salle #' . $r->room_id, 'total' => $r->total])
            ->toArray();

        $byPrestation = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('prestation_id')
            ->select('prestation_id', DB::raw('count(*) as total'))
            ->groupBy('prestation_id')
            ->with('prestation')
            ->get()
            ->map(fn ($r) => ['name' => $r->prestation?->name ?? 'Prestation #' . $r->prestation_id, 'total' => $r->total])
            ->toArray();

        $byPractitioner = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw("sum(case when status = 'completed' then 1 else 0 end) as completed"))
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->map(function ($r) use ($start, $end) {
                $caUser = (float) Invoice::where('status', 'paid')
                    ->whereBetween('invoice_date', [$start, $end])
                    ->whereHas('appointment', fn ($q) => $q->where('user_id', $r->user_id))
                    ->sum('amount');
                return ['name' => $r->user?->name ?? '—', 'completed' => $r->completed ?? 0, 'ca' => $caUser];
            })
            ->filter(fn ($r) => $r['name'] !== '—')
            ->toArray();

        $data = compact('month', 'start', 'end', 'ca', 'invoicesCount', 'appointmentsTotal', 'appointmentsCompleted', 'statsByStatus', 'caByPrestation', 'byRoom', 'byPrestation', 'byPractitioner', 'byPole');
        $pdf = Pdf::loadView('pdf.monthly-report-detailed', $data);
        $dir = storage_path('app/monthly-reports');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/' . $month . '.pdf';
        $pdf->save($path);

        return $path;
    }

    public function downloadMonthlyReport(string $month)
    {
        if (! can_access_dossier()) {
            abort(403);
        }
        $path = storage_path('app/monthly-reports/' . $month . '.pdf');
        if (! file_exists($path)) {
            abort(404, 'Rapport non trouvé.');
        }

        return response()->download($path, 'rapport-mensuel-' . $month . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $ca = (float) Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->sum('amount');

        $appointmentsCompleted = Appointment::where('status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->count();

        $appointmentsTotal = Appointment::whereBetween('appointment_date', [$start, $end])
            ->count();

        $invoicesCount = Invoice::whereBetween('invoice_date', [$start, $end])->count();

        $data = compact('month', 'start', 'end', 'ca', 'appointmentsCompleted', 'appointmentsTotal', 'invoicesCount');

        $pdf = Pdf::loadView('pdf.monthly-report', $data);

        return $pdf->download('rapport-mensuel-' . $month . '.pdf');
    }

    /**
     * Répartition par statut, par pôle, par praticien — pour un MOIS donné.
     * Utilisé comme vue rapide pour le docteur / super_admin.
     */
    public function stats(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = array_map('intval', explode('-', $month));
        $start = sprintf('%04d-%02d-01', $year, $monthNum);
        $end = date('Y-m-t', strtotime($start));
        $label = \Carbon\Carbon::createFromDate($year, $monthNum, 1)->translatedFormat('F Y');

        $byStatus = Appointment::whereBetween('appointment_date', [$start, $end])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $byPole = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereNotNull('pole_id')
            ->join('poles', 'appointments.pole_id', '=', 'poles.id')
            ->select('poles.name', DB::raw('count(*) as total'))
            ->groupBy('poles.id', 'poles.name')
            ->orderByRaw('count(*) desc')
            ->get();

        $byPractitioner = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereNotNull('user_id')
            ->join('users', 'appointments.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByRaw('count(*) desc')
            ->get();

        $totalRdvs = array_sum($byStatus);
        $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent'];

        return view('reports.stats', compact(
            'month', 'start', 'end', 'label',
            'byStatus', 'byPole', 'byPractitioner', 'totalRdvs', 'statusLabels'
        ));
    }

    /** Rapport par prestation (page web). */
    public function byRoomAndPrestation(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $byPrestation = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('prestation_id')
            ->select('prestation_id', DB::raw('count(*) as total'))
            ->groupBy('prestation_id')
            ->with('prestation')
            ->get();

        return view('reports.by-room-prestation', compact('byPrestation', 'month', 'start', 'end'));
    }

    /** Export PDF des rapports par salle et prestation */
    public function exportRoomPrestationPdf(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";
        $end = date('Y-m-t', strtotime($start));

        $byRoom = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('room_id')
            ->select('room_id', DB::raw('count(*) as total'))
            ->groupBy('room_id')
            ->with('room')
            ->get()
            ->map(fn ($r) => ['name' => $r->room?->name ?? 'Salle #'.$r->room_id, 'total' => $r->total])
            ->toArray();

        $byPrestation = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('prestation_id')
            ->select('prestation_id', DB::raw('count(*) as total'))
            ->groupBy('prestation_id')
            ->with('prestation')
            ->get()
            ->map(fn ($r) => ['name' => $r->prestation?->name ?? 'Prestation #'.$r->prestation_id, 'total' => $r->total])
            ->toArray();

        $pdf = Pdf::loadView('pdf.room-prestation-report', compact('byRoom', 'byPrestation', 'month', 'start', 'end'));

        return $pdf->download('rapport-salles-prestations-'.$month.'.pdf');
    }

    /** Export Excel du rapport par prestation */
    public function exportRoomPrestationExcel(Request $request): BinaryFileResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";

        $byPrestation = Appointment::whereBetween('appointment_date', [$start, date('Y-m-t', strtotime($start))])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('prestation_id')
            ->select('prestation_id', DB::raw('count(*) as total'))
            ->groupBy('prestation_id')
            ->with('prestation')
            ->get()
            ->map(fn ($r) => [$r->prestation?->name ?? 'Prestation #'.$r->prestation_id, $r->total])
            ->toArray();

        return Excel::download(
            new RoomPrestationReportExport($byPrestation, $month),
            'rapport-prestations-'.$month.'.xlsx'
        );
    }
}
