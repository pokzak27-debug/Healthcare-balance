<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $patient->fullName() }}
            </h2>
            <div class="flex gap-2 items-center flex-wrap">
                <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('patients.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Prénom</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $patient->first_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nom</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $patient->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date de naissance</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $patient->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sexe</dt>
                    <dd class="mt-0.5 text-gray-900">{{ match($patient->sexe) { 'M' => 'Homme', 'F' => 'Femme', 'autre' => 'Autre', default => '—' } }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $patient->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $patient->email ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $patient->address ?? '—' }}</dd>
                </div>
                @if($patient->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-0.5 text-gray-900 whitespace-pre-wrap">{{ $patient->notes }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                    <dd class="mt-0.5 text-gray-500 text-sm">{{ $patient->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Fiches diagnostic</h3>
                    @if(can_access_dossier())
                        <a href="{{ route('diagnostic-fishes.create', $patient) }}" class="px-3 py-1.5 bg-amber-600 text-white rounded-md text-sm hover:bg-amber-700">Nouvelle</a>
                    @endif
                </div>
                @if($patient->diagnosticFishes->isEmpty())
                    <p class="text-gray-500 text-sm">Aucune fiche diagnostic.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($patient->diagnosticFishes as $fish)
                            <li class="p-3 rounded-lg border border-gray-200 hover:border-amber-300">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-gray-500">{{ $fish->date_examen?->format('d/m/Y') ?? $fish->created_at->format('d/m/Y') }}{{ $fish->user ? ' · ' . $fish->user->name : '' }}</p>
                                        <p class="text-sm text-gray-900 mt-0.5">{{ $fish->diagnostic ? Str::limit($fish->diagnostic, 100) : '—' }}</p>
                                    </div>
                                    @if(can_access_dossier())
                                        <a href="{{ route('diagnostic-fishes.edit', $fish) }}" class="text-indigo-600 text-sm shrink-0 hover:underline">Modifier</a>
                                    @endif
                                </div>
                                @if(can_access_dossier())
                                    <a href="{{ route('diagnostic-fishes.show', $fish) }}" class="text-xs text-gray-400 hover:text-indigo-600 mt-1 inline-block">Voir détails</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-lg text-gray-800">Dossier médical</h3>
                    <a href="{{ route('medical-entries.create', ['patient_id' => $patient->id]) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">Ajouter entrée</a>
                </div>
                @if($patient->medicalEntries->isEmpty())
                    <p class="text-gray-500">Aucune entrée médicale.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($patient->medicalEntries as $entry)
                            <li class="py-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">{{ \App\Models\MedicalEntry::TYPES[$entry->type] ?? $entry->type }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $entry->entry_date?->format('d/m/Y') ?? $entry->created_at->format('d/m/Y') }}</span>
                                        @if($entry->user)
                                            <span class="text-xs text-gray-400">— {{ $entry->user->name }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('medical-entries.edit', $entry) }}" class="text-indigo-600 text-sm">Modifier</a>
                                </div>
                                <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $entry->content }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-lg text-gray-800">Rendez-vous</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('patients.export-pdf', $patient) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">PDF fiche</a>
                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                        Nouveau rendez-vous
                        </a>
                    </div>
                </div>
                @if($patient->appointments->isEmpty())
                    <p class="text-gray-600 text-sm">Aucun rendez-vous.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Heure</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pôle</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Praticien</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($patient->appointments as $appointment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-900">{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $appointment->pole?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $appointment->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @php $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent']; @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            @if($appointment->status === 'scheduled') bg-blue-100 text-blue-800
                                            @elseif($appointment->status === 'completed') bg-green-100 text-green-800
                                            @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="text-indigo-600 hover:underline text-sm">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
