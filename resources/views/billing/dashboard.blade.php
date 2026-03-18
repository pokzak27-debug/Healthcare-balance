<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Facturation</h2>
            <div class="flex gap-2">
                <a href="{{ route('quotes.create') }}" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm">Nouveau devis</a>
                <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Nouvelle facture</a>
                <a href="{{ route('quotes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Tous les devis</a>
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Toutes les factures</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-6 text-gray-600">Saisissez les données en créant un devis ou une facture, puis exportez en PDF ou Word après finalisation.</p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Devis récents --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-3 bg-amber-50 border-b border-amber-100">
                        <h3 class="font-semibold text-gray-900">Devis récents</h3>
                        <p class="text-sm text-gray-500">Créer, modifier, puis exporter en PDF ou Word</p>
                    </div>
                    <div class="p-4">
                        @if($recentQuotes->isEmpty())
                            <p class="text-gray-500 text-sm py-4">Aucun devis.</p>
                            <a href="{{ route('quotes.create') }}" class="text-amber-600 hover:underline text-sm">Créer un devis</a>
                        @else
                            <ul class="divide-y divide-gray-200">
                                @foreach($recentQuotes as $q)
                                    <li class="py-3 flex flex-wrap items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('quotes.show', $q) }}" class="font-medium text-gray-900 hover:text-amber-600">{{ $q->number }}</a>
                                            <span class="text-gray-500 text-sm block">{{ $q->patient?->fullName() }} · {{ $q->quote_date->format('d/m/Y') }} · {{ format_dh($q->amount) }}</span>
                                        </div>
                                        <div class="flex gap-1 shrink-0">
                                            <a href="{{ route('quotes.export-pdf', $q) }}" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200">PDF</a>
                                            <a href="{{ route('quotes.export-word', $q) }}" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200">Word</a>
                                            <a href="{{ route('quotes.edit', $q) }}" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200">Modifier</a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-3">
                                <a href="{{ route('quotes.index') }}" class="text-sm text-amber-600 hover:underline">Voir tous les devis →</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Factures récentes --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-3 bg-emerald-50 border-b border-emerald-100">
                        <h3 class="font-semibold text-gray-900">Factures récentes</h3>
                        <p class="text-sm text-gray-500">Exporter en PDF ou Word après finalisation</p>
                    </div>
                    <div class="p-4">
                        @if($recentInvoices->isEmpty())
                            <p class="text-gray-500 text-sm py-4">Aucune facture.</p>
                            <a href="{{ route('invoices.create') }}" class="text-emerald-600 hover:underline text-sm">Créer une facture</a>
                        @else
                            <ul class="divide-y divide-gray-200">
                                @foreach($recentInvoices as $inv)
                                    <li class="py-3 flex flex-wrap items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('invoices.show', $inv) }}" class="font-medium text-gray-900 hover:text-emerald-600">{{ $inv->number }}</a>
                                            <span class="text-gray-500 text-sm block">{{ $inv->patient?->fullName() }} · {{ $inv->invoice_date->format('d/m/Y') }} · {{ format_dh($inv->amount) }}</span>
                                        </div>
                                        <div class="flex gap-1 shrink-0">
                                            <a href="{{ route('invoices.export-pdf', $inv) }}" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200">PDF</a>
                                            <a href="{{ route('invoices.export-word', $inv) }}" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200">Word</a>
                                            <a href="{{ route('invoices.edit', $inv) }}" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200">Modifier</a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-3">
                                <a href="{{ route('invoices.index') }}" class="text-sm text-emerald-600 hover:underline">Voir toutes les factures →</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
