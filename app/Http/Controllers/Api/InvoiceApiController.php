<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['patient', 'appointment'])->orderBy('invoice_date', 'desc');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($patientId = $request->get('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        $invoices = $query->paginate((int) $request->get('per_page', 15));

        return response()->json($invoices);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['patient', 'appointment']);

        return response()->json($invoice);
    }
}
