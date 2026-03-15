<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $room->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('machines.create', ['room_id' => $room->id]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">+ Machine</a>
                <a href="{{ route('rooms.edit', $room) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Modifier</a>
                <a href="{{ route('rooms.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Machines dans cette salle</h3>
                @if($room->machines->isEmpty())
                    <p class="text-gray-500">Aucune machine.</p>
                    <a href="{{ route('machines.create', ['room_id' => $room->id]) }}" class="mt-4 inline-block text-indigo-600 hover:underline">Ajouter une machine</a>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($room->machines as $m)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <span class="font-medium">{{ $m->name }}</span>
                                    @if($m->description)
                                        <span class="text-sm text-gray-500">— {{ $m->description }}</span>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('machines.edit', $m) }}" class="text-indigo-600 text-sm hover:underline">Modifier</a>
                                    <form action="{{ route('machines.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette machine ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
