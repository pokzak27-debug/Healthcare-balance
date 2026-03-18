<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau devis</h2></x-slot>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('quotes.store') }}">
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
                            <label class="block text-sm font-medium text-gray-700">Statut</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach(\App\Models\Quote::STATUSES as $k => $v)
                                    <option value="{{ $k }}" {{ $k === 'draft' ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" name="quote_date" required value="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valide jusqu'au</label>
                            <input type="date" name="valid_until" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Détails du devis (protocole)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Diagnostic</label>
                                    <input type="text" name="diagnostic" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Médecin traitant</label>
                                    <input type="text" name="treating_doctor" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre de séances</label>
                                    <input type="number" name="sessions_count" min="1" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Rythme des séances</label>
                                    <input type="text" name="sessions_rhythm" placeholder="Ex. 3 fois par semaine" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                                    <input type="date" name="treatment_start" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                                    <input type="date" name="treatment_end" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix unitaire (par séance)</label>
                                    <input type="number" step="0.01" min="0" name="unit_price" class="mt-1 block w-full rounded-md border-gray-300">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700">Description complémentaire</label>
                                <input type="text" name="description" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Texte libre si besoin...">
                            </div>
                        </div>
                        @if($appointment)
                            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        @endif
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Créer</button>
                        <a href="{{ route('quotes.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
