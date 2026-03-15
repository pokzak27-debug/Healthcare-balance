<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier {{ $prestation->name }}</h2>
            <a href="{{ route('prestations.index') }}" class="text-indigo-600 hover:underline">← Retour</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('prestations.update', $prestation) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $prestation->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mt-4">
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Durée (min) *</label>
                        <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $prestation->duration_minutes) }}" min="5" max="240" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="pole_id" class="block text-sm font-medium text-gray-700">Pôle</label>
                            <select name="pole_id" id="pole_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Aucun —</option>
                                @foreach($poles as $pole)
                                    <option value="{{ $pole->id }}" {{ old('pole_id', $prestation->pole_id) == $pole->id ? 'selected' : '' }}>{{ $pole->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="room_id" class="block text-sm font-medium text-gray-700">Salle suggérée</label>
                            <select name="room_id" id="room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Aucune —</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}" {{ old('room_id', $prestation->room_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="room_required" value="1" {{ old('room_required', $prestation->room_required) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Salle obligatoire</span>
                        </label>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Machines requises</label>
                        <div class="mt-2 space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded-md p-3">
                            @foreach(\App\Models\Machine::with('room')->ordered()->get() as $m)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="machine_ids[]" value="{{ $m->id }}" {{ in_array($m->id, old('machine_ids', $prestation->machines->pluck('id')->toArray())) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">{{ $m->name }} <span class="text-gray-500">({{ $m->room?->name }})</span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $prestation->description) }}</textarea>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $prestation->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Actif</span>
                        </label>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                        <a href="{{ route('prestations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
