<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Factures</h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.export-csv', request()->query()) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Export CSV</a>
                <a href="{{ route('invoices.export-excel', request()->query()) }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm">Export Excel</a>
                @if(can_access_dossier())
                    <a href="{{ route('invoices.ca-by-practitioner') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Chiffre d'affaires</a>
                @endif
                <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle facture</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            <form method="GET" class="mb-4 flex gap-2">
                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Invoice::STATUSES as $k => $v)
                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </form>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="px-6 py-4 font-medium"><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">{{ $inv->number }}</a></td>
                                <td class="px-6 py-4">{{ $inv->patient?->fullName() }}</td>
                                <td class="px-6 py-4">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">{{ format_dh($inv->amount) }}</td>
                                <td class="px-6 py-4">{{ \App\Models\Invoice::STATUSES[$inv->status] ?? $inv->status }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('invoices.export-pdf', $inv) }}" class="text-indigo-600 hover:underline text-sm">PDF</a>
                                    @if(can_access_dossier())
                                        <a href="{{ route('invoices.edit', $inv) }}" class="ml-2 text-indigo-600 hover:underline text-sm">Modifier</a>
                                        <form action="{{ route('invoices.destroy', $inv) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                        </form>
                                    @else
                                        @if(!$inv->isConfirmed())
                                            <a href="{{ route('invoices.edit', $inv) }}" class="ml-2 text-indigo-600 hover:underline text-sm">Modifier</a>
                                            <form action="{{ route('invoices.confirm', $inv) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Confirmer cette facture ? Vous ne pourrez plus la modifier ni la supprimer.');">
                                                @csrf
                                                <button type="submit" class="text-emerald-600 hover:underline text-sm">Confirmer</button>
                                            </form>
                                        @else
                                            <span class="ml-2 text-xs text-gray-400">Confirmée</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucune facture.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($invoices->hasPages())
                    <div class="px-6 py-3 border-t">{{ $invoices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
