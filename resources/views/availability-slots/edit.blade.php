<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier le créneau</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('availability-slots.update', $availabilitySlot) }}">
                    @csrf
                    @method('PUT')
                    @include('availability-slots._form', ['availabilitySlot' => $availabilitySlot])
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                        <a href="{{ route('availability-slots.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
