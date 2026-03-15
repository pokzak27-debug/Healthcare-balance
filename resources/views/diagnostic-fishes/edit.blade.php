<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Modifier la fiche de diagnostic — {{ $diagnosticFish->patient->fullName() }}
            </h2>
            <a href="{{ route('diagnostic-fishes.show', $diagnosticFish) }}" class="text-indigo-600 hover:underline">← Retour</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('diagnostic-fishes.update', $diagnosticFish) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div>
                        <label for="date_examen" class="block text-sm font-medium text-gray-700">Date de l'examen</label>
                        <input type="date" name="date_examen" id="date_examen" value="{{ old('date_examen', $diagnosticFish->date_examen?->format('Y-m-d') ?? $diagnosticFish->created_at->format('Y-m-d')) }}"
                            class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="examen_clinique" class="block text-sm font-medium text-gray-700">Examen clinique</label>
                        <textarea name="examen_clinique" id="examen_clinique" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('examen_clinique', $diagnosticFish->examen_clinique) }}</textarea>
                    </div>
                    <div>
                        <label for="examen_subjectif" class="block text-sm font-medium text-gray-700">Examen subjectif</label>
                        <textarea name="examen_subjectif" id="examen_subjectif" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('examen_subjectif', $diagnosticFish->examen_subjectif) }}</textarea>
                    </div>
                    <div>
                        <label for="examen_objectif" class="block text-sm font-medium text-gray-700">Examen objectif</label>
                        <textarea name="examen_objectif" id="examen_objectif" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('examen_objectif', $diagnosticFish->examen_objectif) }}</textarea>
                    </div>
                    <div>
                        <label for="examen_dynamique" class="block text-sm font-medium text-gray-700">Examen dynamique</label>
                        <textarea name="examen_dynamique" id="examen_dynamique" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('examen_dynamique', $diagnosticFish->examen_dynamique) }}</textarea>
                    </div>
                    <div>
                        <label for="examen_statique" class="block text-sm font-medium text-gray-700">Examen statique</label>
                        <textarea name="examen_statique" id="examen_statique" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('examen_statique', $diagnosticFish->examen_statique) }}</textarea>
                    </div>
                    <div>
                        <label for="diagnostic" class="block text-sm font-medium text-gray-700">Diagnostic</label>
                        <textarea name="diagnostic" id="diagnostic" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('diagnostic', $diagnosticFish->diagnostic) }}</textarea>
                    </div>
                    <div>
                        <label for="protocole" class="block text-sm font-medium text-gray-700">Protocole</label>
                        <textarea name="protocole" id="protocole" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('protocole', $diagnosticFish->protocole) }}</textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('diagnostic-fishes.show', $diagnosticFish) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
