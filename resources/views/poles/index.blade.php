<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pôles</h2>
            <a href="{{ route('poles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouveau pôle</a>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actif</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($poles as $pole)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('poles.show', $pole) }}" class="text-indigo-600 hover:underline font-medium">{{ $pole->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $pole->sort_order }}</td>
                                <td class="px-6 py-4">{{ $pole->is_active ? 'Oui' : 'Non' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('poles.edit', $pole) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('poles.destroy', $pole) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ce pôle ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucun pôle.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
