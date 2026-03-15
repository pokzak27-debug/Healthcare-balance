<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cabinet en direct</h2>
            <div class="flex gap-2 items-center flex-wrap">
                <span class="text-xs text-gray-500" x-text="'MAJ ' + (updatedAt || '{{ now()->format('H:i:s') }}')"></span>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        rooms: {!! \Illuminate\Support\Js::from($roomsJson) !!},
        appointments: {!! \Illuminate\Support\Js::from($appointmentsJson) !!},
        accueil: {!! \Illuminate\Support\Js::from($accueilJson ?? []) !!},
        updatedAt: '{{ now()->format('H:i:s') }}',
        loading: false,
        statusLabel(s) { return s==='free'?'Libre':s==='occupied'?'Occupée':s==='cleaning'?'Nettoyage':'Libre'; },
        init() {
            setInterval(() => this.fetchData(), 15000);
        },
        async fetchData() {
            this.loading = true;
            try {
                const r = await fetch('{{ route('live-cabinet.data') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.rooms = d.rooms || [];
                this.appointments = d.appointments || [];
                this.accueil = d.patientsAtReception || [];
                this.updatedAt = d.updated_at || '';
            } catch(e) {}
            this.loading = false;
        }
    }" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Disponibilité des salles (complète) --}}
            <section class="mb-8">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Disponibilité des salles</h3>
                <p class="text-gray-600 text-sm mb-4">Choisissez pour chaque salle : patient, prestation, statut. Chrono affiché quand la salle est occupée.</p>
                @if($rooms->isEmpty())
                    <p class="text-gray-500">Aucune salle configurée.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($rooms as $room)
                            @php
                                $effectivePatient = $room->currentPatient ?? $room->currentAppointment?->patient;
                                $effectivePrestation = $room->currentPrestation ?? $room->currentAppointment?->prestation ?? $room->currentAppointment?->pole;
                                $isOccupied = ($room->status ?? 'free') === 'occupied';
                                $startedAt = $room->occupied_started_at?->timestamp;
                            @endphp
                            <div class="bg-white shadow-sm sm:rounded-lg p-5">
                                <h4 class="font-semibold text-gray-900 mb-2">{{ $room->name }}</h4>
                                @if($isOccupied && $startedAt)
                                    <div class="mb-3 p-2 rounded-lg bg-amber-50 border border-amber-200">
                                        <p class="text-[10px] font-medium text-amber-700 uppercase">Chrono</p>
                                        <p class="text-xl font-mono font-bold text-amber-800" data-chrono-room="{{ $room->id }}" data-chrono-start="{{ $startedAt }}">00:00</p>
                                    </div>
                                @endif
                                <div class="mb-3 p-2 rounded-lg {{ in_array($room->status ?? 'free', ['occupied', 'cleaning']) ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 border border-gray-200' }}">
                                    <p class="text-[10px] font-medium text-gray-500 uppercase">Patient · Prestation · Machine</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $effectivePatient?->fullName() ?? '—' }} · {{ $effectivePrestation?->name ?? '—' }}{{ ($room->currentMachine?->name) ? ' · ' . $room->currentMachine->name : '' }}</p>
                                </div>
                                <form action="{{ route('rooms.update-status', $room) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Patient</label>
                                        <select name="patient_id" class="block w-full text-xs rounded-md border-gray-300 shadow-sm">
                                            <option value="">—</option>
                                            @foreach($patients as $p)
                                                <option value="{{ $p->id }}" {{ $effectivePatient?->id == $p->id ? 'selected' : '' }}>{{ $p->last_name }} {{ $p->first_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Prestation</label>
                                        <select name="prestation_id" class="block w-full text-xs rounded-md border-gray-300 shadow-sm">
                                            <option value="">—</option>
                                            @foreach($prestations as $pr)
                                                <option value="{{ $pr->id }}" {{ $effectivePrestation?->id == $pr->id ? 'selected' : '' }}>{{ $pr->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if($room->machines->isNotEmpty())
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-600 mb-0.5">Machine</label>
                                            <select name="machine_id" class="block w-full text-xs rounded-md border-gray-300 shadow-sm">
                                                <option value="">—</option>
                                                @foreach($room->machines as $m)
                                                    <option value="{{ $m->id }}" {{ $room->current_machine_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-1 pt-1">
                                        @foreach(\App\Models\Room::STATUSES as $k => $v)
                                            <button type="submit" name="status" value="{{ $k }}" class="inline-block px-3 py-1.5 rounded text-xs font-medium {{ ($room->status ?? 'free') === $k ? ($k === 'free' ? 'bg-green-600 text-white' : ($k === 'occupied' ? 'bg-amber-600 text-white' : 'bg-blue-600 text-white')) : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ $v }}</button>
                                        @endforeach
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Salles (résumé) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900">Salles</h3>
                        </div>
                        <ul class="divide-y divide-gray-200">
                            <template x-for="r in rooms" :key="r.id">
                                <li class="px-4 py-3" :class="{
                                    'border-l-4 border-amber-500 bg-amber-50': r.status === 'occupied',
                                    'border-l-4 border-blue-500 bg-blue-50': r.status === 'cleaning',
                                    'border-l-4 border-green-500 bg-green-50': r.status === 'free' || !r.status
                                }">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium" x-text="r.name"></span>
                                        <span class="text-xs font-medium" x-text="statusLabel(r.status)"></span>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1 font-medium" x-show="(r.status === 'occupied' || r.status === 'cleaning') && (r.patient || r.prestation || r.machine)" x-text="[r.patient || '', r.prestation || '', r.machine || ''].filter(Boolean).join(' · ') || '—'"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="(r.machines_count || 0) + ' machine(s)'"></p>
                                </li>
                            </template>
                            <li x-show="rooms.length === 0 && !loading" class="px-4 py-6 text-center text-gray-500">Aucune salle configurée.</li>
                        </ul>
                    </div>
                </div>

                {{-- RDV du jour + Accueil --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Patients à l'accueil (en attente) --}}
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" x-show="accueil.length > 0">
                        <div class="px-4 py-3 bg-amber-50 border-b border-amber-200">
                            <h3 class="font-semibold text-gray-900">Patients à l'accueil</h3>
                        </div>
                        <ul class="p-4 space-y-2 divide-y divide-gray-100">
                            <template x-for="p in accueil" :key="p.id">
                                <li class="flex items-center justify-between py-3">
                                    <div>
                                        <span class="font-medium text-gray-900" x-text="p.time + ' — ' + p.patient"></span>
                                        <p class="text-sm text-gray-500" x-text="p.prestation"></p>
                                    </div>
                                    <a :href="'{{ url('appointments') }}/' + p.id" class="text-indigo-600 text-sm hover:underline">RDV</a>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- RDV du jour --}}
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-900">Rendez-vous du jour</h3>
                            <a href="{{ route('appointments.index') }}" class="text-sm text-indigo-600 hover:underline">Voir tout</a>
                        </div>
                        <div class="p-4 max-h-[320px] overflow-y-auto">
                            <template x-if="appointments.length === 0 && !loading">
                                <p class="text-gray-500">Aucun rendez-vous aujourd'hui.</p>
                            </template>
                            <ul class="space-y-2" x-show="appointments.length > 0">
                                <template x-for="a in appointments" :key="a.id">
                                    <li class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50" :class="{ 'bg-green-50': a.status === 'completed', 'bg-amber-50/50': a.arrived_at }">
                                        <div class="min-w-0 flex-1">
                                            <a :href="a.url" class="font-medium text-gray-900 hover:text-indigo-600" x-text="a.time + ' — ' + a.patient"></a>
                                            <p class="text-sm text-gray-500" x-text="[a.prestation,a.room,a.user].filter(Boolean).join(' · ')||'—'"></p>
                                            <span x-show="a.arrived_at" class="text-xs text-amber-700">Arrivé à <span x-text="a.arrived_at"></span></span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <template x-if="!a.arrived_at">
                                                <form class="inline" @submit.prevent="fetch('{{ url('appointments') }}/'+a.id+'/mark-arrived', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData($event.target) }).then(() => fetchData())">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-xs bg-emerald-600 text-white rounded hover:bg-emerald-700">Arrivé</button>
                                                </form>
                                            </template>
                                            <template x-if="a.arrived_at">
                                                <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800">Accueil</span>
                                            </template>
                                            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="a.status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" x-text="a.status === 'scheduled' ? 'Prévu' : 'Honoré'"></span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
            </section>
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
