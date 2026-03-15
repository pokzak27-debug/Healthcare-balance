<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvelle facture</h2></x-slot>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patient *</label>
                            <select name="patient_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ ($appointment && $appointment->patient_id == $p->id) ? 'selected' : '' }}>{{ $p->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant (DH) *</label>
                            <input type="number" name="amount" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="description" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Consultation, soins...">
                        </div>
                        @if($appointment)
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        @endif
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Créer</button>
                        <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
