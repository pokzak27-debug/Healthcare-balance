<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Facture {{ $invoice->number }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.export-pdf', $invoice) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Export PDF</a>
                @if(can_access_dossier() || !$invoice->isConfirmed())
                    <a href="{{ route('invoices.edit', $invoice) }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm">Modifier</a>
                @endif
                @if(!can_access_dossier() && !$invoice->isConfirmed())
                    <form action="{{ route('invoices.confirm', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer cette facture ? Vous ne pourrez plus la modifier.');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Confirmer</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <div><span class="text-gray-500">Patient :</span> {{ $invoice->patient->fullName() }}</div>
                <div><span class="text-gray-500">Date :</span> {{ $invoice->invoice_date->format('d/m/Y') }}</div>
                <div><span class="text-gray-500">Montant :</span> {{ format_dh($invoice->amount) }}</div>
                <div><span class="text-gray-500">Statut :</span> {{ \App\Models\Invoice::STATUSES[$invoice->status] ?? $invoice->status }}</div>
                @if($invoice->description)
                    <div><span class="text-gray-500">Description :</span> {{ $invoice->description }}</div>
                @endif
            </div>
            <div class="mt-4">
                <a href="{{ route('invoices.index') }}" class="text-indigo-600 hover:underline">← Retour</a>
            </div>
        </div>
    </div>
</x-app-layout>
