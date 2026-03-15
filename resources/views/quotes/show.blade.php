<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Devis {{ $quote->number }}</h2>
            <div class="flex gap-2 items-center">
                <form action="{{ route('quotes.duplicate', $quote) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Dupliquer</button>
                </form>
                <a href="{{ route('quotes.export-pdf', $quote) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Export PDF</a>
                @if($quote->canBeAccepted())
                    <form action="{{ route('quotes.accept', $quote) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">Accepter</button>
                    </form>
                    <form action="{{ route('quotes.reject', $quote) }}" method="POST" class="inline" onsubmit="return confirm('Refuser ce devis ?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">Refuser</button>
                    </form>
                @endif
                @if($quote->canBeConvertedToInvoice())
                    <form action="{{ route('quotes.convert-to-invoice', $quote) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Convertir en facture</button>
                    </form>
                @endif
                @if($quote->invoice)
                    <a href="{{ route('invoices.show', $quote->invoice) }}" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Voir facture</a>
                @endif
                <a href="{{ route('quotes.edit', $quote) }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm">Modifier</a>
                <a href="{{ route('quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Patient</dt>
                        <dd class="mt-0.5"><a href="{{ route('patients.show', $quote->patient) }}" class="text-indigo-600 hover:underline">{{ $quote->patient->fullName() }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $quote->quote_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Valide jusqu'au</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Montant</dt>
                        <dd class="mt-0.5 text-gray-900 font-medium">{{ format_dh($quote->amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-0.5">{{ \App\Models\Quote::STATUSES[$quote->status] ?? $quote->status }}</dd>
                    </div>
                    @if($quote->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $quote->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
