<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Machines</h2>
            <a href="{{ route('machines.create', request('room_id') ? ['room_id' => request('room_id')] : []) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle machine</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if($rooms->isNotEmpty())
                <div class="mb-4 flex gap-2 flex-wrap">
                    <a href="{{ route('machines.index') }}" class="px-3 py-1.5 rounded {{ !request('room_id') ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} text-sm">Toutes</a>
                    @foreach($rooms as $r)
                        <a href="{{ route('machines.index', ['room_id' => $r->id]) }}" class="px-3 py-1.5 rounded {{ request('room_id') == $r->id ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} text-sm">{{ $r->name }}</a>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salle</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($machines as $m)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-medium">{{ $m->name }}</span>
                                    @if($m->description)
                                        <p class="text-sm text-gray-500">{{ Str::limit($m->description, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $m->room?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('machines.edit', $m) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('machines.destroy', $m) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer cette machine ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">Aucune machine. <a href="{{ route('machines.create') }}" class="text-indigo-600 hover:underline">Ajouter</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
