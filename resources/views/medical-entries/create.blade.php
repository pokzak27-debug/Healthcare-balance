<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nouvelle entrée médicale — {{ $patient->fullName() }}
            </h2>
            <a href="{{ route('patients.show', $patient) }}" class="text-indigo-600 hover:underline">← Retour dossier</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <p class="font-medium">Veuillez corriger les erreurs ci-dessous.</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('medical-entries.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    @if($appointmentId ?? null)
                        <input type="hidden" name="appointment_id" value="{{ $appointmentId }}">
                    @endif
                    <div class="space-y-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                            <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(\App\Models\MedicalEntry::TYPES as $k => $v)
                                    <option value="{{ $k }}" {{ old('type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="entry_date" class="block text-sm font-medium text-gray-700">Date de l'entrée</label>
                            <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', now()->toDateString()) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('entry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Contenu *</label>
                            <textarea name="content" id="content" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content') }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Enregistrer</button>
                        <a href="{{ route('patients.show', $patient) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
