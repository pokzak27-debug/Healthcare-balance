<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rendez-vous du {{ $appointment->appointment_date->format('d/m/Y') }} à {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
            </h2>
            <div class="flex gap-2 items-center">
                <a href="{{ route('quotes.create', ['appointment_id' => $appointment->id]) }}" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 text-sm">Créer devis</a>
                <a href="{{ route('invoices.create', ['appointment_id' => $appointment->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">Créer facture</a>
                <a href="{{ route('appointments.edit', $appointment) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Patient</dt>
                        <dd class="mt-0.5">
                            <a href="{{ route('patients.show', $appointment->patient) }}" class="text-indigo-600 hover:underline font-medium">
                                {{ $appointment->patient->fullName() }}
                            </a>
                        </dd>
                    </div>
                    @if($appointment->pole)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pôle</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $appointment->pole->name }}</dd>
                        </div>
                    @endif
                    @if($appointment->prestation)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Prestation</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $appointment->prestation->name }} ({{ $appointment->prestation->duration_minutes }} min)</dd>
                        </div>
                    @endif
                    @if($appointment->room)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Salle</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $appointment->room->name }}</dd>
                        </div>
                    @endif
                    @if($appointment->user)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Praticien</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $appointment->user->name }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $appointment->appointment_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Heure</dt>
                        <dd class="mt-0.5 text-gray-900">
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                            – {{ $appointment->endTime() }}
                            ({{ $appointment->duration_minutes ?? 30 }} min)
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-0.5">
                            @php
                                $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent'];
                            @endphp
                            {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                        </dd>
                    </div>
                    @if($appointment->reason)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Motif</dt>
                            <dd class="mt-0.5 text-gray-900">{{ $appointment->reason }}</dd>
                        </div>
                    @endif
                    @if($appointment->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-0.5 text-gray-900 whitespace-pre-wrap">{{ $appointment->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
