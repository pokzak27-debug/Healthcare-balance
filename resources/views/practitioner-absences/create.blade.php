<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvelle absence</h2></x-slot>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('practitioner-absences.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Praticien *</label>
                            <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach($practitioners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Du</label>
                                <input type="date" name="start_date" required class="mt-1 block w-full rounded-md border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Au</label>
                                <input type="date" name="end_date" required class="mt-1 block w-full rounded-md border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Motif</label>
                            <input type="text" name="reason" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span class="text-sm">Actif</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                        <a href="{{ route('practitioner-absences.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
