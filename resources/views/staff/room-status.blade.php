<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disponibilité des salles</h2>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            <p class="text-gray-600 mb-6">Choisissez pour chaque salle : patient, prestation, puis le statut. Un chronomètre s'affiche quand la salle est occupée.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($rooms as $room)
                    @php
                        $effectivePatient = $room->currentPatient ?? $room->currentAppointment?->patient;
                        $effectivePrestation = $room->currentPrestation ?? $room->currentAppointment?->prestation ?? $room->currentAppointment?->pole;
                        $isOccupied = ($room->status ?? 'free') === 'occupied';
                        $startedAt = $room->occupied_started_at?->timestamp;
                    @endphp
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ $room->name }}</h3>

                        {{-- Chronomètre (visible quand occupée) --}}
                        @if($isOccupied && $startedAt)
                            <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200">
                                <p class="text-xs font-medium text-amber-700 uppercase tracking-wide mb-1">Chrono</p>
                                <p class="text-2xl font-mono font-bold text-amber-800" data-chrono-room="{{ $room->id }}" data-chrono-start="{{ $startedAt }}">00:00</p>
                            </div>
                        @endif

                        {{-- Affichage actuel patient / prestation --}}
                        <div class="mb-4 p-3 rounded-lg {{ in_array($room->status ?? 'free', ['occupied', 'cleaning']) ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 border border-gray-200' }}">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">Patient occupant</p>
                            <p class="font-medium text-gray-900">{{ $effectivePatient?->fullName() ?? '—' }}</p>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-2 mb-0.5">Prestation</p>
                            <p class="text-sm text-gray-700">{{ $effectivePrestation?->name ?? '—' }}</p>
                        </div>

                        <form action="{{ route('rooms.update-status', $room) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Patient</label>
                                <select name="patient_id" class="block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($patients as $p)
                                        <option value="{{ $p->id }}" {{ $effectivePatient?->id == $p->id ? 'selected' : '' }}>
                                            {{ $p->last_name }} {{ $p->first_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Prestation</label>
                                <select name="prestation_id" class="block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($prestations as $pr)
                                        <option value="{{ $pr->id }}" {{ $effectivePrestation?->id == $pr->id ? 'selected' : '' }}>
                                            {{ $pr->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @php $roomRdv = $appointmentsByRoom[$room->id] ?? collect(); @endphp
                            @if($roomRdv->isNotEmpty())
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">RDV du jour (optionnel)</label>
                                    <select name="appointment_id" class="block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                        <option value="">— Non lié —</option>
                                        @foreach($roomRdv as $a)
                                            <option value="{{ $a->id }}" {{ $room->current_appointment_id == $a->id ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($a->appointment_time)->format('H:i') }} — {{ $a->patient?->fullName() }} — {{ $a->prestation?->name ?? $a->pole?->name ?? '—' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Models\Room::STATUSES as $k => $v)
                                    <button type="submit" name="status" value="{{ $k }}"
                                        class="inline-block px-4 py-2 rounded-md text-sm font-medium transition
                                        @if(($room->status ?? 'free') === $k)
                                            @if($k === 'free') bg-green-600 text-white
                                            @elseif($k === 'occupied') bg-amber-600 text-white
                                            @else bg-blue-600 text-white
                                            @endif
                                        @else
                                            bg-gray-100 text-gray-700 hover:bg-gray-200
                                        @endif">
                                        {{ $v }}
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
            @if($rooms->isEmpty())
                <p class="text-gray-500">Aucune salle configurée. Le médecin doit d'abord créer les salles.</p>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-chrono-room]').forEach(function(el) {
            var start = parseInt(el.getAttribute('data-chrono-start'), 10);
            function tick() {
                var now = Math.floor(Date.now() / 1000);
                var s = Math.max(0, now - start);
                var m = Math.floor(s / 60);
                var h = Math.floor(m / 60);
                m = m % 60;
                s = s % 60;
                el.textContent = (h > 0 ? String(h).padStart(2, '0') + ':' : '') + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }
            tick();
            setInterval(tick, 1000);
        });
    </script>
</x-app-layout>
