<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Patients') }}
            </h2>
            <a href="{{ route('patients.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Nouveau patient
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-4 bg-white rounded-lg shadow-sm p-4">
                <form action="{{ route('patients.index') }}" method="GET" class="space-y-3">
                    <div class="flex flex-wrap gap-2 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label for="search" class="sr-only">Recherche</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Rechercher par nom, prénom, téléphone, email..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="sort" class="block text-xs text-gray-500 mb-0.5">Trier par</label>
                            <select name="sort" id="sort" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="last_name" {{ request('sort', 'last_name') == 'last_name' ? 'selected' : '' }}>Nom</option>
                                <option value="first_name" {{ request('sort') == 'first_name' ? 'selected' : '' }}>Prénom</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                            </select>
                        </div>
                        <div>
                            <label for="direction" class="block text-xs text-gray-500 mb-0.5">Ordre</label>
                            <select name="direction" id="direction" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="asc" {{ request('direction', 'asc') == 'asc' ? 'selected' : '' }}>A → Z</option>
                                <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Z → A</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Appliquer</button>
                            @if(request('search') || request('sort', 'last_name') !== 'last_name' || request('direction', 'asc') !== 'asc')
                                <a href="{{ route('patients.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Effacer</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($patients->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="mt-4 text-gray-600 font-medium">
                                {{ request('search') ? 'Aucun patient trouvé pour votre recherche.' : 'Aucun patient enregistré.' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ request('search') ? 'Essayez d\'autres termes ou effacez les filtres.' : 'Commencez par créer votre premier patient.' }}
                            </p>
                            @if(!request('search'))
                                <a href="{{ route('patients.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Nouveau patient
                                </a>
                            @else
                                <a href="{{ route('patients.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                    Effacer les filtres
                                </a>
                            @endif
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($patients as $patient)
                                    <tr class="hover:bg-gray-50 transition">
                                        @php $patientRoute = can_access_dossier() ? route('patients.show', $patient) : route('patients.fiche', $patient); @endphp
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ $patientRoute }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                {{ $patient->first_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ $patientRoute }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                {{ $patient->last_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $patient->phone ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $patient->email ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded text-sm hover:bg-indigo-100">Modifier</a>
                                                @if(can_access_dossier())
                                                    <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce patient ? Cette action est irréversible.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded text-sm hover:bg-red-100">Supprimer</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            {{ $patients->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
