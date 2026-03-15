<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvelle consigne pour le staff</h2>
            <a href="{{ route('staff-instructions.index') }}" class="text-indigo-600 hover:underline">← Retour</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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
                <form action="{{ route('staff-instructions.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="to_user_id" class="block text-sm font-medium text-gray-700">Destinataire</label>
                            <select name="to_user_id" id="to_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tout le staff</option>
                                @foreach($staffUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('to_user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                            @error('to_user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="instruction" class="block text-sm font-medium text-gray-700">Consigne *</label>
                            <textarea name="instruction" id="instruction" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Décrivez la consigne ou instruction pour le staff...">{{ old('instruction') }}</textarea>
                            @error('instruction')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Envoyer</button>
                        <a href="{{ route('staff-instructions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
