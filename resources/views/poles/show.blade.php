<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $pole->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('poles.edit', $pole) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('poles.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">Description</dt>
                        <dd class="text-gray-900">{{ $pole->description ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Ordre</dt>
                        <dd class="text-gray-900">{{ $pole->sort_order }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Actif</dt>
                        <dd class="text-gray-900">{{ $pole->is_active ? 'Oui' : 'Non' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Rendez-vous</dt>
                        <dd class="text-gray-900">{{ $pole->appointments_count }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
