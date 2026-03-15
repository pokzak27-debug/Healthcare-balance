<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $isDoctor ? 'Consignes patients envoyées' : 'Consignes du docteur' }}
            </h2>
            <a href="{{ route('live-cabinet.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Cabinet live</a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($consignes->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        @if($isDoctor)
                            Aucune consigne envoyée. Utilisez le Cabinet live pour donner des consignes aux patients à l'accueil.
                        @else
                            Aucune consigne en attente. Les consignes du docteur s'affichent ici.
                        @endif
                    </div>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($consignes as $c)
                            <li class="p-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap text-sm text-gray-500 mb-2">
                                            <span class="font-medium text-gray-900">{{ $c->patient->fullName() }}</span>
                                            <span>— {{ $c->created_at->format('d/m/Y H:i') }}</span>
                                            @if($isDoctor)
                                                <span>À l'assistante</span>
                                            @else
                                                <span>Du Dr {{ $c->fromUser?->name ?? '—' }}</span>
                                            @endif
                                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                                @if($c->status === 'pending') bg-amber-100 text-amber-800
                                                @elseif($c->status === 'acknowledged') bg-blue-100 text-blue-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                {{ \App\Models\DoctorPatientConsigne::STATUSES[$c->status] ?? $c->status }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm mt-2">
                                            <p><span class="text-gray-500">Salle :</span> <strong>{{ $c->room?->name ?? '—' }}</strong></p>
                                            <p><span class="text-gray-500">Prestation :</span> <strong>{{ $c->prestation?->name ?? '—' }}</strong></p>
                                            <p><span class="text-gray-500">Durée :</span> <strong>{{ $c->duration_minutes }} min</strong></p>
                                            <p><span class="text-gray-500">Voir patient :</span> <strong>{{ $c->doctor_sees_patient ? 'Oui' : 'Non' }}</strong></p>
                                        </div>
                                        @if($c->notes)
                                            <p class="mt-2 text-gray-700 text-sm">{{ $c->notes }}</p>
                                        @endif
                                        @if($c->acknowledged_at)
                                            <p class="text-xs text-gray-400 mt-2">Lu le {{ $c->acknowledged_at->format('d/m/Y H:i') }}{{ $c->acknowledgedByUser ? ' par ' . $c->acknowledgedByUser->name : '' }}</p>
                                        @endif
                                        @if($c->completed_at)
                                            <p class="text-xs text-gray-400 mt-1">Terminé le {{ $c->completed_at->format('d/m/Y H:i') }}{{ $c->completedByUser ? ' par ' . $c->completedByUser->name : '' }}</p>
                                        @endif
                                    </div>
                                    @if(!$isDoctor && $c->status !== 'completed')
                                        <div class="flex flex-col gap-2 shrink-0">
                                            @if($c->status === 'pending')
                                                <form action="{{ route('doctor-patient-consignes.acknowledge', $c) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Confirmer lu</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('doctor-patient-consignes.complete', $c) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700">Marquer terminé</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-4 border-t border-gray-200">
                        {{ $consignes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
