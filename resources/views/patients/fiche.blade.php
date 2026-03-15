<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fiche patient — {{ $patient->fullName() }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('patients.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-amber-700 mb-4">Fiche informative uniquement — pour le dossier médical complet, contactez le médecin.</p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
                    <div><dt class="text-sm font-medium text-gray-500">Prénom</dt><dd class="mt-0.5 font-medium">{{ $patient->first_name }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Nom</dt><dd class="mt-0.5 font-medium">{{ $patient->last_name }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Date de naissance</dt><dd class="mt-0.5">{{ $patient->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Téléphone</dt><dd class="mt-0.5">{{ $patient->phone ?? '—' }}</dd></div>
                    <div><dt class="text-sm font-medium text-gray-500">Email</dt><dd class="mt-0.5">{{ $patient->email ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Adresse</dt><dd class="mt-0.5">{{ $patient->address ?? '—' }}</dd></div>
                </dl>
            </div>
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Prochains rendez-vous</h3>
                @if($upcomingAppointments->isEmpty())
                    <p class="text-gray-500">Aucun rendez-vous à venir.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($upcomingAppointments as $apt)
                            <li class="py-3 flex justify-between items-center">
                                <span>{{ $apt->appointment_date->format('d/m/Y') }} à {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }} — {{ $apt->pole?->name ?? '—' }}</span>
                                <a href="{{ route('appointments.show', $apt) }}" class="text-indigo-600 text-sm">Voir</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
