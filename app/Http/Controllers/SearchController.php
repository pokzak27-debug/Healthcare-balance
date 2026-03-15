<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Recherche globale (patients + rendez-vous).
     */
    public function __invoke(Request $request): View|JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            if ($request->wantsJson()) {
                return response()->json(['patients' => [], 'appointments' => []]);
            }

            return view('search.index', ['q' => $q, 'patients' => collect(), 'appointments' => collect()]);
        }

        $phoneDigits = preg_replace('/\D/', '', $q);
        $patients = Patient::query()
            ->where(function ($query) use ($q, $phoneDigits) {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
                if (strlen($phoneDigits) >= 4) {
                    $query->orWhere('phone_digits', 'like', "%{$phoneDigits}%");
                }
            })
            ->orderBy('last_name')->orderBy('first_name')
            ->limit(20)
            ->get();

        $appointments = Appointment::with(['patient', 'pole', 'user'])
            ->where(function ($query) use ($q, $phoneDigits) {
                $query->whereHas('patient', function ($sq) use ($q, $phoneDigits) {
                    $sq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                    if (isset($phoneDigits) && strlen($phoneDigits) >= 4) {
                        $sq->orWhere('phone_digits', 'like', "%{$phoneDigits}%");
                    }
                })
                    ->orWhere('reason', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            })
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time')
            ->limit(20)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'patients' => $patients,
                'appointments' => $appointments,
            ]);
        }

        return view('search.index', compact('q', 'patients', 'appointments'));
    }
}
