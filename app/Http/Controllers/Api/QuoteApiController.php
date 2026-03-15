<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Quote::with(['patient', 'appointment'])->orderBy('quote_date', 'desc');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($patientId = $request->get('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        $quotes = $query->paginate((int) $request->get('per_page', 15));

        return response()->json($quotes);
    }

    public function show(Quote $quote): JsonResponse
    {
        $quote->load(['patient', 'appointment', 'invoice']);

        return response()->json($quote);
    }
}
