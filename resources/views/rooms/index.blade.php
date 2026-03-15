<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Salles</h2>
            <a href="{{ route('rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle salle</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machines</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($rooms as $room)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('rooms.show', $room) }}" class="text-indigo-600 hover:underline font-medium">{{ $room->name }}</a>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match($room->status ?? 'free') {
                                            'occupied' => 'bg-amber-100 text-amber-800',
                                            'cleaning' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-green-100 text-green-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
                                        {{ \App\Models\Room::STATUSES[$room->status ?? 'free'] ?? 'Libre' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $room->machines_count ?? 0 }}</td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a href="{{ route('machines.create', ['room_id' => $room->id]) }}" class="text-sm text-gray-600 hover:underline">+ Machine</a>
                                    <a href="{{ route('rooms.edit', $room) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette salle ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucune salle. <a href="{{ route('rooms.create') }}" class="text-indigo-600 hover:underline">Créer une salle</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
