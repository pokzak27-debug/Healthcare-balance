<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom *</label>
            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $patient->first_name ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('first_name') border-red-500 @enderror" required>
            @error('first_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Nom *</label>
            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $patient->last_name ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('last_name') border-red-500 @enderror" required>
            @error('last_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700">Date de naissance</label>
            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $patient->birth_date?->format('Y-m-d') ?? '') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('birth_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="sexe" class="block text-sm font-medium text-gray-700">Sexe</label>
            <select name="sexe" id="sexe" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">—</option>
                <option value="M" {{ old('sexe', $patient->sexe ?? '') == 'M' ? 'selected' : '' }}>Homme</option>
                <option value="F" {{ old('sexe', $patient->sexe ?? '') == 'F' ? 'selected' : '' }}>Femme</option>
                <option value="autre" {{ old('sexe', $patient->sexe ?? '') == 'autre' ? 'selected' : '' }}>Autre</option>
            </select>
            @error('sexe')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $patient->phone ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $patient->email ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
        <input type="text" name="address" id="address" value="{{ old('address', $patient->address ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('address')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $patient->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
