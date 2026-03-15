<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $pole->name ?? '') }}" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $pole->description ?? '') }}</textarea>
    </div>
    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordre d'affichage</label>
        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $pole->sort_order ?? 0) }}" min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $pole->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Actif</span>
        </label>
    </div>
</div>
