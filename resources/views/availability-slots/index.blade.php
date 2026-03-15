<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créneaux de disponibilité</h2>
            <a href="{{ route('availability-slots.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouveau créneau</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Praticien</label>
                    <select name="user_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Tous —</option>
                        @foreach($practitioners as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pôle</label>
                    <select name="pole_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Tous —</option>
                        @foreach($poles as $p)
                            <option value="{{ $p->id }}" {{ request('pole_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actif</label>
                    <select name="is_active" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Tous —</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Non</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Filtrer</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Praticien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pôle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jour</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horaires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créneau</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actif</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($slots as $slot)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900">{{ $slot->user?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $slot->pole?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $slot->day_name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $slot->slot_minutes }} min</td>
                                <td class="px-6 py-4">{{ $slot->is_active ? 'Oui' : 'Non' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('availability-slots.edit', $slot) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('availability-slots.destroy', $slot) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ce créneau ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">Aucun créneau défini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($slots->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">{{ $slots->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
