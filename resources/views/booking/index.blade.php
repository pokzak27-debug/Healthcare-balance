@extends('layouts.booking')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Prendre rendez-vous en ligne</h1>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <form action="{{ route('booking.store') }}" method="POST" class="space-y-6 bg-white shadow-sm rounded-lg p-6">
                @csrf
                <div>
                    <label for="pole_id" class="block text-sm font-medium text-gray-700">Pôle *</label>
                    <select name="pole_id" id="pole_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Choisir —</option>
                        @foreach($poles as $pole)
                            <option value="{{ $pole->id }}" {{ old('pole_id') == $pole->id ? 'selected' : '' }}>{{ $pole->name }}</option>
                        @endforeach
                    </select>
                    @error('pole_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="prestation_id" class="block text-sm font-medium text-gray-700">Prestation (optionnel)</label>
                    <select name="prestation_id" id="prestation_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Aucune —</option>
                        @foreach($prestations ?? [] as $p)
                            <option value="{{ $p->id }}" data-duration="{{ $p->duration_minutes }}">{{ $p->name }} ({{ $p->duration_minutes }} min)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Praticien (optionnel)</label>
                    <select name="user_id" id="user_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Choisir (indifférent) —</option>
                        @foreach($practitioners ?? [] as $prat)
                            <option value="{{ $prat->id }}" {{ old('user_id') == $prat->id ? 'selected' : '' }}>{{ $prat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="appointment_date" class="block text-sm font-medium text-gray-700">Date *</label>
                        <input type="date" name="appointment_date" id="appointment_date" required
                            min="{{ now()->format('Y-m-d') }}"
                            value="{{ old('appointment_date') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('appointment_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="appointment_time" class="block text-sm font-medium text-gray-700">Heure *</label>
                        <input type="time" name="appointment_time" id="appointment_time" required min="08:00" max="23:00"
                            value="{{ old('appointment_time') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            title="Heures cabinet : 8h - 23h">
                        <p class="mt-1 text-xs text-gray-500" id="slots-hint">Choisir d'abord date et pôle pour voir les créneaux.</p>
                        <div id="available-slots" class="mt-2 flex flex-wrap gap-1 hidden"></div>
                        @error('appointment_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <input type="hidden" name="duration_minutes" id="duration_minutes" value="30">
                @if(isset($rooms) && $rooms->isNotEmpty())
                <div>
                    <label for="room_id" class="block text-sm font-medium text-gray-700">Salle (optionnel)</label>
                    <select name="room_id" id="room_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Aucune —</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Vos coordonnées</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">Prénom *</label>
                            <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Nom *</label>
                            <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone *</label>
                            <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700">Motif (optionnel)</label>
                        <input type="text" name="reason" id="reason" value="{{ old('reason') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Ex. Consultation, suivi...">
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Confirmer le rendez-vous</button>
                    <a href="/" class="px-6 py-2 text-gray-600 hover:text-gray-900">Retour</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const poleEl = document.getElementById('pole_id');
            const dateEl = document.getElementById('appointment_date');
            const timeEl = document.getElementById('appointment_time');
            const slotsDiv = document.getElementById('available-slots');
            const hint = document.getElementById('slots-hint');

            function fetchSlots() {
                const date = dateEl.value;
                const poleId = poleEl.value;
                if (!date || !poleId) {
                    slotsDiv.classList.add('hidden');
                    slotsDiv.innerHTML = '';
                    return;
                }
                hint.textContent = 'Chargement…';
                const userId = document.getElementById('user_id')?.value || '';
                const prestationId = document.getElementById('prestation_id')?.value || '';
                const duration = document.getElementById('duration_minutes')?.value || '30';
                let url = `{{ route('booking.slots') }}?date=${date}&pole_id=${poleId}&duration_minutes=${duration}`;
                if (userId) url += '&user_id=' + userId;
                if (prestationId) url += '&prestation_id=' + prestationId;
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        const times = data.times || [];
                        hint.textContent = times.length ? 'Cliquez pour sélectionner :' : 'Aucun créneau disponible.';
                        slotsDiv.innerHTML = times.map(t => 
                            `<button type="button" class="px-3 py-1.5 text-sm rounded border border-gray-300 hover:bg-indigo-50 hover:border-indigo-400" data-time="${t}">${t}</button>`
                        ).join('');
                        slotsDiv.classList.remove('hidden');
                        slotsDiv.querySelectorAll('button').forEach(btn => {
                            btn.addEventListener('click', () => {
                                timeEl.value = btn.dataset.time;
                                slotsDiv.querySelectorAll('button').forEach(b => b.classList.remove('ring-2', 'ring-indigo-500'));
                                btn.classList.add('ring-2', 'ring-indigo-500');
                            });
                        });
                    })
                    .catch(() => { hint.textContent = 'Erreur de chargement.'; slotsDiv.innerHTML = ''; });
            }

            poleEl.addEventListener('change', fetchSlots);
            dateEl.addEventListener('change', fetchSlots);
            document.getElementById('user_id')?.addEventListener('change', fetchSlots);
            document.getElementById('prestation_id')?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset.duration) {
                    document.getElementById('duration_minutes').value = opt.dataset.duration;
                }
                fetchSlots();
            });
        });
    </script>
    @endpush
@endsection
