<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Modifier</a>
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm">← Retour</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-sm text-gray-500">Email</dt><dd class="text-gray-900">{{ $user->email }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Rôle</dt><dd class="text-gray-900">{{ $user->role?->name ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Actif</dt><dd class="text-gray-900">{{ $user->is_active ? 'Oui' : 'Non' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
