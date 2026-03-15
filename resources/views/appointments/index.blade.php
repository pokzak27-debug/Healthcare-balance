<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 flex-wrap">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rendez-vous
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('appointments.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                    Exporter CSV
                </a>
                <a href="{{ route('appointments.export-pdf', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                    Exporter PDF
                </a>
                <a href="{{ route('appointments.calendar') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                    Calendrier
                </a>
                <a href="{{ route('appointments.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Nouveau rendez-vous
                </a>
            </div>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('appointments.index') }}" class="mb-6 space-y-3">
                        <div class="flex flex-wrap gap-2 items-end">
                            <div class="flex-1 min-w-[200px]">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Patient, motif, notes..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            @if(isset($rooms) && $rooms->isNotEmpty())
                            <div class="min-w-[140px]">
                                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">Salle</label>
                                <select name="room_id" id="room_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Toutes</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="min-w-[140px]">
                                <label for="pole_id" class="block text-sm font-medium text-gray-700 mb-1">Pôle</label>
                                <select name="pole_id" id="pole_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Tous</option>
                                    @foreach($poles as $pole)
                                        <option value="{{ $pole->id }}" {{ request('pole_id') == $pole->id ? 'selected' : '' }}>
                                            {{ $pole->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-[130px]">
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Du</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div class="min-w-[130px]">
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Au</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div class="min-w-[140px]">
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Praticien</label>
                                <select name="user_id" id="user_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Tous</option>
                                    @foreach($practitioners ?? [] as $practitioner)
                                        <option value="{{ $practitioner->id }}" {{ request('user_id') == $practitioner->id ? 'selected' : '' }}>
                                            {{ $practitioner->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-[140px]">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                <select name="status" id="status"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Tous</option>
                                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Prévu</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Honoré</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>Absent</option>
                                </select>
                            </div>
                            <div class="min-w-[120px]">
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
                                <select name="sort" id="sort"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="appointment_date" {{ request('sort', 'appointment_date') === 'appointment_date' ? 'selected' : '' }}>Date</option>
                                    <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Statut</option>
                                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Création</option>
                                </select>
                            </div>
                            <div class="min-w-[100px]">
                                <label for="direction" class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                                <select name="direction" id="direction"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>Récent → Ancien</option>
                                    <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Ancien → Récent</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Appliquer</button>
                                @if(request()->hasAny(['search', 'pole_id', 'status', 'date_from', 'date_to', 'user_id', 'sort', 'direction']))
                                    <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Effacer</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($appointments->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-600 font-medium">
                                {{ request()->hasAny(['search', 'pole_id', 'status', 'date_from', 'date_to', 'user_id']) ? 'Aucun rendez-vous trouvé pour vos filtres.' : 'Aucun rendez-vous enregistré.' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ request()->hasAny(['search', 'pole_id', 'status', 'date_from', 'date_to', 'user_id']) ? 'Essayez d\'autres critères ou effacez les filtres.' : 'Commencez par créer un rendez-vous.' }}
                            </p>
                            @if(!request()->hasAny(['search', 'pole_id', 'status', 'date_from', 'date_to', 'user_id']))
                                <a href="{{ route('appointments.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Nouveau rendez-vous
                                </a>
                            @else
                                <a href="{{ route('appointments.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                    Effacer les filtres
                                </a>
                            @endif
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pôle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praticien</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($appointments as $appointment)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                            <span class="text-gray-400">({{ $appointment->duration_minutes ?? 30 }} min)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('appointments.show', $appointment) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                {{ $appointment->patient->fullName() }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $appointment->pole?->name ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $appointment->user?->name ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent'];
                                            @endphp
                                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                                @if($appointment->status === 'scheduled') bg-blue-100 text-blue-800
                                                @elseif($appointment->status === 'completed') bg-green-100 text-green-800
                                                @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 max-w-xs truncate" title="{{ $appointment->reason ?? '' }}">{{ $appointment->reason ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('appointments.edit', $appointment) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded text-sm hover:bg-indigo-100">Modifier</a>
                                                <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded text-sm hover:bg-red-100">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            {{ $appointments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
