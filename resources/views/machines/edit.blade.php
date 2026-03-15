<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier {{ $machine->name }}</h2>
            <a href="{{ route('machines.index') }}" class="text-indigo-600 hover:underline">← Retour</a>
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
                <form action="{{ route('machines.update', $machine) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700">Salle *</label>
                        <select name="room_id" id="room_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}" {{ old('room_id', $machine->room_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $machine->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <input type="text" name="description" id="description" value="{{ old('description', $machine->description) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $machine->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Actif</span>
                        </label>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                        <a href="{{ route('machines.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
