<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau pôle</h2>
            <a href="{{ route('poles.index') }}" class="text-indigo-600 hover:underline">← Retour</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside"><li>{{ $errors->first() }}</li></ul>
                </div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('poles.store') }}" method="POST">
                    @csrf
                    @include('poles._form')
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Créer</button>
                        <a href="{{ route('poles.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
