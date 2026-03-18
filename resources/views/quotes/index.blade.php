<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Devis</h2>
            <a href="{{ route('quotes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouveau devis</a>
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
                    @foreach(\App\Models\Quote::STATUSES as $k => $v)
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
                        @forelse($quotes as $q)
                            <tr>
                                <td class="px-6 py-4 font-medium"><a href="{{ route('quotes.show', $q) }}" class="text-indigo-600 hover:underline">{{ $q->number }}</a></td>
                                <td class="px-6 py-4">{{ $q->patient?->fullName() }}</td>
                                <td class="px-6 py-4">{{ $q->quote_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">{{ format_dh($q->amount) }}</td>
                                <td class="px-6 py-4">{{ \App\Models\Quote::STATUSES[$q->status] ?? $q->status }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('quotes.edit', $q) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('quotes.destroy', $q) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucun devis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($quotes->hasPages())
                    <div class="px-6 py-3 border-t">{{ $quotes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
