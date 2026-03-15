<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Fiche de diagnostic — {{ $diagnosticFish->patient->fullName() }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('diagnostic-fishes.edit', $diagnosticFish) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('diagnostic-fishes.index', $diagnosticFish->patient) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Liste des fiches</a>
                <a href="{{ route('patients.show', $diagnosticFish->patient) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Fiche patient</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                <p class="text-sm text-gray-500">
                    Date d'examen : {{ $diagnosticFish->date_examen?->format('d/m/Y') ?? $diagnosticFish->created_at->format('d/m/Y') }}
                    @if($diagnosticFish->user)
                        · Par {{ $diagnosticFish->user->name }}
                    @endif
                </p>
                <dl class="space-y-4">
                    @foreach([
                        'examen_clinique' => 'Examen clinique',
                        'examen_subjectif' => 'Examen subjectif',
                        'examen_objectif' => 'Examen objectif',
                        'examen_dynamique' => 'Examen dynamique',
                        'examen_statique' => 'Examen statique',
                        'diagnostic' => 'Diagnostic',
                        'protocole' => 'Protocole',
                    ] as $field => $label)
                        @if($diagnosticFish->$field)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
                                <dd class="mt-0.5 text-gray-900 whitespace-pre-wrap">{{ $diagnosticFish->$field }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
                @php $hasAny = $diagnosticFish->examen_clinique || $diagnosticFish->examen_subjectif || $diagnosticFish->examen_objectif || $diagnosticFish->examen_dynamique || $diagnosticFish->examen_statique || $diagnosticFish->diagnostic || $diagnosticFish->protocole; @endphp
                @if(!$hasAny)
                    <p class="text-gray-500">Aucune donnée renseignée. <a href="{{ route('diagnostic-fishes.edit', $diagnosticFish) }}" class="text-indigo-600 hover:underline">Compléter la fiche</a></p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
