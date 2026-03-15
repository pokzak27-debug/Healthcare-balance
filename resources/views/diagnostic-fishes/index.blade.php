<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Fiches de diagnostic — {{ $patient->fullName() }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('diagnostic-fishes.create', $patient) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle fiche</a>
                <a href="{{ route('patients.show', $patient) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Fiche patient</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if($patient->diagnosticFishes->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <p class="text-gray-500 mb-4">Aucune fiche de diagnostic pour ce patient.</p>
                    <a href="{{ route('diagnostic-fishes.create', $patient) }}" class="inline-flex px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Créer la première fiche</a>
                </div>
            @else
                <ul class="space-y-4">
                    @foreach($patient->diagnosticFishes as $fish)
                        <li class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <a href="{{ route('diagnostic-fishes.show', $fish) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                        Fiche du {{ $fish->date_examen?->format('d/m/Y') ?? $fish->created_at->format('d/m/Y') }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Par {{ $fish->user?->name ?? '—' }}
                                        @if($fish->diagnostic)
                                            · {{ Str::limit($fish->diagnostic, 80) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('diagnostic-fishes.edit', $fish) }}" class="text-sm text-indigo-600 hover:underline">Modifier</a>
                                    <form action="{{ route('diagnostic-fishes.destroy', $fish) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette fiche ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
