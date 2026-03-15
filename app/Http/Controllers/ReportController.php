<?php

namespace App\Http\Controllers;

use App\Exports\RoomPrestationReportExport;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Prestation;
use App\Models\Room;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
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

    /** Rapports par salle et par prestation (page web). */
    public function byRoomAndPrestation(Request $request): View
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
            ->get();

        $byPrestation = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('prestation_id')
            ->select('prestation_id', DB::raw('count(*) as total'))
            ->groupBy('prestation_id')
            ->with('prestation')
            ->get();

        $rooms = Room::active()->ordered()->get();
        $prestations = Prestation::active()->ordered()->get();

        return view('reports.by-room-prestation', compact('byRoom', 'byPrestation', 'month', 'start', 'end', 'rooms', 'prestations'));
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

    /** Export Excel des rapports par salle et prestation */
    public function exportRoomPrestationExcel(Request $request): BinaryFileResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);
        $start = "{$year}-{$monthNum}-01";

        $byRoom = Appointment::whereBetween('appointment_date', [$start, date('Y-m-t', strtotime($start))])
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('room_id')
            ->select('room_id', DB::raw('count(*) as total'))
            ->groupBy('room_id')
            ->with('room')
            ->get()
            ->map(fn ($r) => [$r->room?->name ?? 'Salle #'.$r->room_id, $r->total])
            ->toArray();

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
            new RoomPrestationReportExport($byRoom, $byPrestation, $month),
            'rapport-salles-prestations-'.$month.'.xlsx'
        );
    }
}
