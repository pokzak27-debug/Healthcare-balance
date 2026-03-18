<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Besoins cabinet & Charges</h2>
            @if(Auth::user()->hasRole('staff') || can_access_dossier())
                <a href="{{ route('cabinet-expenses.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvel enregistrement</a>
            @endif
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3 rounded-lg bg-teal-50 border border-teal-200">
                    <p class="text-sm font-medium text-teal-800">Fournitures ce mois</p>
                    <p class="text-xl font-semibold text-teal-900">{{ format_dh($totalSuppliesThisMonth) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 border border-amber-200">
                    <p class="text-sm font-medium text-amber-800">Charges à payer</p>
                    <p class="text-xl font-semibold text-amber-900">{{ format_dh($chargesToPay) }}</p>
                </div>
            </div>

            <form method="GET" class="mb-4 flex flex-wrap gap-2 items-center">
                <select name="type" onchange="this.form.submit()" class="rounded-md border-gray-300">
                    <option value="">Tous les types</option>
                    <option value="supply" {{ request('type') === 'supply' ? 'selected' : '' }}>Fournitures</option>
                    <option value="charge" {{ request('type') === 'charge' ? 'selected' : '' }}>Charges</option>
                </select>
                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="rounded-md border-gray-300" onchange="this.form.submit()">
            </form>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance / Payé</th>
                            @if(Auth::user()->hasRole('staff') || can_access_dossier())
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenses as $e)
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $e->type === 'supply' ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $e->type === 'supply' ? 'Fourniture' : 'Charge' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $e->category_label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($e->description, 40) ?: '—' }}</td>
                                <td class="px-6 py-4 text-right font-medium">{{ format_dh($e->amount) }}</td>
                                <td class="px-6 py-4">{{ $e->expense_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    @if($e->type === 'charge')
                                        @if($e->paid_at)
                                            <span class="text-green-600 text-sm">Payée le {{ $e->paid_at->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-amber-600 text-sm">{{ $e->due_date?->format('d/m/Y') ?? '—' }}</span>
                                            @if(Auth::user()->hasRole('staff') || can_access_dossier())
                                                <form action="{{ route('cabinet-expenses.mark-paid', $e) }}" method="POST" class="inline ml-1">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:underline text-xs">Marquer payée</button>
                                                </form>
                                            @endif
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                @if(Auth::user()->hasRole('staff') || can_access_dossier())
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('cabinet-expenses.edit', $e) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                        <form action="{{ route('cabinet-expenses.destroy', $e) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ (Auth::user()->hasRole('staff') || can_access_dossier()) ? 8 : 7 }}" class="px-6 py-8 text-center text-gray-500">Aucun enregistrement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($expenses->hasPages())
                    <div class="px-6 py-3 border-t">{{ $expenses->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
