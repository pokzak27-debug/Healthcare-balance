<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chiffre d'affaires</h2>
            <a href="{{ route('invoices.ca-by-practitioner', ['format' => 'csv', 'from' => $from, 'to' => $to]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Export CSV</a>
            <form action="{{ route('reports.monthly') }}" method="GET" class="inline flex items-center gap-2">
                <input type="month" name="month" value="{{ now()->format('Y-m') }}" class="rounded-md border-gray-300 text-sm">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Rapport PDF</button>
            </form>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" class="mb-4 flex gap-2 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Du</label>
                    <input type="date" name="from" value="{{ $from }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Au</label>
                    <input type="date" name="to" value="{{ $to }}" class="mt-1 rounded-md border-gray-300">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm">Actualiser</button>
            </form>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prestation</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nb factures</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant (DH)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($data as $row)
                            <tr>
                                <td class="px-6 py-4 font-medium">{{ $row['prestation'] }}</td>
                                <td class="px-6 py-4 text-right">{{ $row['count'] }}</td>
                                <td class="px-6 py-4 text-right font-medium">{{ format_dh($row['total']) }}</td>
                            </tr>
                        @endforeach
                        @if($data->isNotEmpty())
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4">TOTAL</td>
                                <td class="px-6 py-4 text-right">{{ $totalInvoices }}</td>
                                <td class="px-6 py-4 text-right">{{ format_dh($totalAmount) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @if($data->isEmpty())
                    <p class="px-6 py-8 text-center text-gray-500">Aucune facture payée sur cette période.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
