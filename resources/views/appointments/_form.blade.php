<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="patient_id" class="block text-sm font-medium text-gray-700">Patient *</label>
            <select name="patient_id" id="patient_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('patient_id') border-red-500 @enderror">
                <option value="">— Choisir un patient —</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}" {{ old('patient_id', $appointment->patient_id ?? request('patient_id')) == $p->id ? 'selected' : '' }}>
                        {{ $p->fullName() }}
                    </option>
                @endforeach
            </select>
            @error('patient_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="pole_id" class="block text-sm font-medium text-gray-700">Pôle *</label>
            <select name="pole_id" id="pole_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('pole_id') border-red-500 @enderror">
                <option value="">— Choisir un pôle —</option>
                @foreach($poles as $pole)
                    <option value="{{ $pole->id }}" {{ old('pole_id', $appointment->pole_id ?? '') == $pole->id ? 'selected' : '' }}>
                        {{ $pole->name }}
                    </option>
                @endforeach
            </select>
            @error('pole_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700">Salle</label>
                <select name="room_id" id="room_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('room_id') border-red-500 @enderror">
                    <option value="">— Non assignée —</option>
                    @foreach($rooms ?? [] as $r)
                        <option value="{{ $r->id }}" {{ old('room_id', $appointment->room_id ?? '') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @error('room_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div x-data="{ prestations: {{ json_encode(($prestations ?? collect())->pluck('duration_minutes', 'id')) }} }">
                <label for="prestation_id" class="block text-sm font-medium text-gray-700">Prestation</label>
                <select name="prestation_id" id="prestation_id"
                    x-on:change="const d = prestations[$event.target.value]; if(d){ $event.target.form?.querySelector('#duration_minutes').value = d; }"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Aucune —</option>
                    @foreach($prestations ?? [] as $p)
                        <option value="{{ $p->id }}" data-duration="{{ $p->duration_minutes }}" {{ old('prestation_id', $appointment->prestation_id ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->duration_minutes }} min)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">Responsable de la prestation</label>
        <select name="user_id" id="user_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('user_id') border-red-500 @enderror">
            <option value="">— Non assigné —</option>
            @foreach($practitioners ?? [] as $prat)
                <option value="{{ $prat->id }}" {{ old('user_id', $appointment->user_id ?? '') == $prat->id ? 'selected' : '' }}>
                    {{ $prat->name }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="appointment_date" class="block text-sm font-medium text-gray-700">Date *</label>
            <input type="date" name="appointment_date" id="appointment_date"
                value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d') ?? request('date')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('appointment_date') border-red-500 @enderror" required>
            @error('appointment_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="appointment_time" class="block text-sm font-medium text-gray-700">Heure *</label>
            <input type="time" name="appointment_time" id="appointment_time" min="08:00" max="23:00"
                value="{{ old('appointment_time', $appointment->appointment_time ?? request('time')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('appointment_time') border-red-500 @enderror" required
                title="Heures cabinet : 8h - 23h">
            <div x-data="availableSlots()" x-init="init()" class="mt-2">
                <template x-if="times.length > 0">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Créneaux disponibles :</p>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="t in times" :key="t">
                                <button type="button" @click="select(t)"
                                    class="px-2 py-1 text-sm rounded border border-gray-300 hover:bg-indigo-50 hover:border-indigo-400"
                                    x-text="t"></button>
                            </template>
                        </div>
                    </div>
                </template>
                <p x-show="loading" class="text-xs text-gray-500">Chargement…</p>
            </div>
            @error('appointment_time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Durée (min)</label>
            <select name="duration_minutes" id="duration_minutes"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('duration_minutes') border-red-500 @enderror">
                @foreach(\App\Models\Appointment::DURATIONS as $d)
                    <option value="{{ $d }}" {{ old('duration_minutes', $appointment->duration_minutes ?? 30) == $d ? 'selected' : '' }}>
                        {{ $d }} min
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-0.5">Choisir une prestation remplit automatiquement la durée.</p>
            @error('duration_minutes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Statut *</label>
        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="scheduled" {{ old('status', $appointment->status ?? 'scheduled') == 'scheduled' ? 'selected' : '' }}>Prévu</option>
            <option value="completed" {{ old('status', $appointment->status ?? '') == 'completed' ? 'selected' : '' }}>Honoré</option>
            <option value="cancelled" {{ old('status', $appointment->status ?? '') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
            <option value="no_show" {{ old('status', $appointment->status ?? '') == 'no_show' ? 'selected' : '' }}>Absent</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="reason" class="block text-sm font-medium text-gray-700">Motif</label>
        <input type="text" name="reason" id="reason" value="{{ old('reason', $appointment->reason ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Ex. Consultation, suivi...">
        @error('reason')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $appointment->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
