<?php

/**
 * Propriété de ZAKARIAE BOUKHROUF - Projet HB Smart Clinic.
 * Tous droits d'utilisation réservés à ZAKARIAE BOUKHROUF pour le cabinet Healthcare Balance.
 */

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CabinetExpense;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tableau de bord.
     */
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $todayAppointments = Appointment::with(['patient', 'pole', 'room', 'prestation', 'user'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        $statsByStatus = Appointment::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statsByPole = Appointment::query()
            ->join('poles', 'appointments.pole_id', '=', 'poles.id')
            ->select('poles.name', DB::raw('count(*) as total'))
            ->groupBy('poles.id', 'poles.name')
            ->orderByRaw('count(*) desc')
            ->get();

        $statsByPractitioner = Appointment::query()
            ->join('users', 'appointments.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByRaw('count(*) desc')
            ->get();

        $totalPatients = Patient::count();

        $caThisMonth = (float) Invoice::where('status', 'paid')->whereMonth('invoice_date', now()->month)->whereYear('invoice_date', now()->year)->sum('amount');
        $caLastMonth = (float) Invoice::where('status', 'paid')->whereMonth('invoice_date', now()->subMonth()->month)->whereYear('invoice_date', now()->subMonth()->year)->sum('amount');
        $caEvolution = $caLastMonth > 0 ? round((($caThisMonth - $caLastMonth) / $caLastMonth) * 100, 1) : ($caThisMonth > 0 ? 100 : 0);

        $suppliesThisMonth = (float) CabinetExpense::where('type', CabinetExpense::TYPE_SUPPLY)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $chargesToPay = (float) CabinetExpense::where('type', CabinetExpense::TYPE_CHARGE)->whereNull('paid_at')->sum('amount');

        return view('dashboard', compact(
            'todayAppointments',
            'statsByStatus',
            'statsByPole',
            'statsByPractitioner',
            'totalPatients',
            'caThisMonth',
            'caLastMonth',
            'caEvolution',
            'suppliesThisMonth',
            'chargesToPay'
        ));
    }
}
