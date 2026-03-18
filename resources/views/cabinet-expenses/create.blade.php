<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau — Besoin cabinet / Charge</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form action="{{ route('cabinet-expenses.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300" onchange="toggleCategories()">
                            <option value="supply" {{ old('type') === 'supply' ? 'selected' : '' }}>Fourniture (huiles, bandages…)</option>
                            <option value="charge" {{ old('type') === 'charge' ? 'selected' : '' }}>Charge à payer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                        <select name="category" id="category" required class="mt-1 block w-full rounded-md border-gray-300">
                            <optgroup id="opt-supply" label="Fournitures">
                                @foreach(\App\Models\CabinetExpense::SUPPLY_CATEGORIES as $k => $v)
                                    <option value="{{ $k }}" data-type="supply" {{ old('category', 'huiles') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup id="opt-charge" label="Charges">
                                @foreach(\App\Models\CabinetExpense::CHARGE_CATEGORIES as $k => $v)
                                    <option value="{{ $k }}" data-type="charge" {{ old('category') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description (optionnel)</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="mt-1 block w-full rounded-md border-gray-300" maxlength="500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Montant (DH)</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div id="due_date_block">
                        <label class="block text-sm font-medium text-gray-700">Échéance (optionnel)</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enregistrer</button>
                        <a href="{{ route('cabinet-expenses.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleCategories() {
            var type = document.getElementById('type').value;
            var cat = document.getElementById('category');
            var opts = cat.querySelectorAll('option');
            var firstSupply = null, firstCharge = null;
            opts.forEach(function(o) {
                var t = o.getAttribute('data-type');
                var show = (type === 'supply' && t === 'supply') || (type === 'charge' && t === 'charge');
                o.style.display = show ? '' : 'none';
                if (show && t === 'supply' && !firstSupply) firstSupply = o.value;
                if (show && t === 'charge' && !firstCharge) firstCharge = o.value;
            });
            cat.value = type === 'supply' ? firstSupply : firstCharge;
            document.getElementById('due_date_block').style.display = type === 'charge' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleCategories();
            document.getElementById('due_date_block').style.display = document.getElementById('type').value === 'charge' ? 'block' : 'none';
        });
    </script>
</x-app-layout>
