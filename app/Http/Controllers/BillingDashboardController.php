<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\View\View;

/**
 * Dashboard facturation pour l'assistante : accès rapide aux devis et factures,
 * création et export PDF / Word après finalisation.
 */
class BillingDashboardController extends Controller
{
    public function __invoke(): View
    {
        $recentQuotes = Quote::with('patient')
            ->orderByDesc('quote_date')
            ->limit(15)
            ->get();

        $recentInvoices = Invoice::with('patient')
            ->orderByDesc('invoice_date')
            ->limit(15)
            ->get();

        return view('billing.dashboard', compact('recentQuotes', 'recentInvoices'));
    }
}
