<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">Praticien</label>
            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('user_id') border-red-500 @enderror">
                <option value="">— Aucun —</option>
                @foreach($practitioners as $u)
                    <option value="{{ $u->id }}" {{ old('user_id', $availabilitySlot->user_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="pole_id" class="block text-sm font-medium text-gray-700">Pôle</label>
            <select name="pole_id" id="pole_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('pole_id') border-red-500 @enderror">
                <option value="">— Aucun —</option>
                @foreach($poles as $p)
                    <option value="{{ $p->id }}" {{ old('pole_id', $availabilitySlot->pole_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Au moins un praticien ou un pôle doit être sélectionné.</p>
            @error('pole_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="day_of_week" class="block text-sm font-medium text-gray-700">Jour *</label>
            <select name="day_of_week" id="day_of_week" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('day_of_week') border-red-500 @enderror">
                @foreach(\App\Models\AvailabilitySlot::DAYS as $num => $label)
                    <option value="{{ $num }}" {{ old('day_of_week', $availabilitySlot->day_of_week ?? '') == $num ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('day_of_week')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slot_minutes" class="block text-sm font-medium text-gray-700">Durée créneau (min)</label>
            <select name="slot_minutes" id="slot_minutes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach(\App\Models\AvailabilitySlot::SLOT_MINUTES as $m)
                    <option value="{{ $m }}" {{ old('slot_minutes', $availabilitySlot->slot_minutes ?? 30) == $m ? 'selected' : '' }}>{{ $m }} min</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700">Heure de début *</label>
            <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $availabilitySlot->start_time ? \Carbon\Carbon::parse($availabilitySlot->start_time)->format('H:i') : '08:00') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_time') border-red-500 @enderror">
            @error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="end_time" class="block text-sm font-medium text-gray-700">Heure de fin *</label>
            <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $availabilitySlot->end_time ? \Carbon\Carbon::parse($availabilitySlot->end_time)->format('H:i') : '23:00') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_time') border-red-500 @enderror">
            @error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $availabilitySlot->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Actif</span>
        </label>
    </div>
</div>
