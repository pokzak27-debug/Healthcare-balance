<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $date->translatedFormat('l d F Y') }}
            </h2>
            <div class="flex items-center gap-4 flex-wrap">
                <a href="{{ route('appointments.calendar') }}" class="text-indigo-600 hover:underline text-sm">← Calendrier</a>
                @if($rooms->isNotEmpty())
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="view" value="day">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <label class="text-sm text-gray-600">Salle :</label>
                    <select name="room_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                        <option value="">Toutes</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ ($roomId ?? '') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
                <a href="{{ route('appointments.create', ['date' => $date->toDateString()]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">+ Ajouter</a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('appointments.calendar', array_filter(['view' => 'day', 'date' => $prevDay, 'room_id' => $roomId])) }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="{{ route('appointments.calendar', array_filter(['view' => 'day', 'date' => $nextDay, 'room_id' => $roomId])) }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200">
                <div class="divide-y divide-gray-100">
                    @for($h = 0; $h < 24; $h++)
                        @php
                            $hour = sprintf('%02d:00', $h);
                            $hourAppts = $dayAppointments->filter(fn ($a) => substr($a->appointment_time, 0, 2) == sprintf('%02d', $h));
                        @endphp
                        <div class="flex min-h-[52px] hover:bg-gray-50/50 transition">
                            <div class="w-16 shrink-0 py-2 px-3 text-right text-sm font-mono text-gray-500 border-r border-gray-100">
                                {{ $hour }}
                            </div>
                            <div class="flex-1 p-2 space-y-1">
                                @forelse($hourAppts as $apt)
                                    <a href="{{ route('appointments.show', $apt) }}"
                                        class="block p-2 rounded-lg text-sm border-l-2 hover:shadow-sm transition
                                            @if($apt->status === 'scheduled') border-indigo-500 bg-indigo-50/80 hover:bg-indigo-50
                                            @elseif($apt->status === 'completed') border-emerald-500 bg-emerald-50/80 hover:bg-emerald-50
                                            @elseif($apt->status === 'cancelled') border-red-300 bg-red-50/50
                                            @else border-gray-400 bg-gray-100/80
                                            @endif">
                                        <span class="font-mono font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}–{{ $apt->endTime() }}
                                        </span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $apt->patient->fullName() }}</span>
                                        @if($apt->pole || $apt->room || $apt->user)
                                            <span class="text-gray-500 text-xs ml-2">
                                                {{ collect([$apt->pole?->name, $apt->room?->name, $apt->user?->name])->filter()->join(' · ') }}
                                            </span>
                                        @endif
                                    </a>
                                @empty
                                    @if($h >= 8 && $h <= 23)
                                        <a href="{{ route('appointments.create', ['date' => $date->toDateString(), 'time' => $hour]) }}"
                                            class="block p-1.5 rounded text-xs text-gray-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition">
                                            + Créer un RDV
                                        </a>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> Prévu</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Honoré</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> Annulé</span>
            </div>
        </div>
    </div>
</x-app-layout>
