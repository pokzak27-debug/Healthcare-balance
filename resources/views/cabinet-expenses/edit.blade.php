<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier — Besoin cabinet / Charge</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form action="{{ route('cabinet-expenses.update', $expense) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300" onchange="toggleCategories()">
                            <option value="supply" {{ old('type', $expense->type) === 'supply' ? 'selected' : '' }}>Fourniture</option>
                            <option value="charge" {{ old('type', $expense->type) === 'charge' ? 'selected' : '' }}>Charge à payer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                        <select name="category" id="category" required class="mt-1 block w-full rounded-md border-gray-300">
                            @foreach(\App\Models\CabinetExpense::SUPPLY_CATEGORIES as $k => $v)
                                <option value="{{ $k }}" {{ old('category', $expense->category) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                            @foreach(\App\Models\CabinetExpense::CHARGE_CATEGORIES as $k => $v)
                                <option value="{{ $k }}" {{ old('category', $expense->category) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description (optionnel)</label>
                        <input type="text" name="description" value="{{ old('description', $expense->description) }}" class="mt-1 block w-full rounded-md border-gray-300" maxlength="500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Montant (DH)</label>
                        <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div id="due_date_block">
                        <label class="block text-sm font-medium text-gray-700">Échéance (optionnel)</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $expense->due_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300">
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
            document.getElementById('due_date_block').style.display = type === 'charge' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function() { document.getElementById('due_date_block').style.display = document.getElementById('type').value === 'charge' ? 'block' : 'none'; });
    </script>
</x-app-layout>
