<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendrier</h2>
                <div class="flex items-center gap-4 flex-wrap">
                <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:underline text-sm">← Dashboard</a>
                @if(isset($rooms) && $rooms->isNotEmpty())
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="view" value="{{ $view }}">
                    @if($view === 'month')<input type="hidden" name="month" value="{{ $start->format('Y-m') }}">@endif
                    @if($view === 'week')<input type="hidden" name="week" value="{{ $start->toDateString() }}">@endif
                    <label class="text-sm text-gray-600">Salle :</label>
                    <select name="room_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                        <option value="">Toutes</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
                <a href="{{ route('appointments.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">+ Ajouter</a>
                <div class="flex gap-1">
                    <a href="{{ route('appointments.calendar', array_merge(request()->only('room_id'), ['view' => 'month', 'month' => $start->format('Y-m')])) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'month' ? 'bg-indigo-100 text-indigo-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Mois</a>
                    <a href="{{ route('appointments.calendar', array_merge(request()->only('room_id'), ['view' => 'week', 'week' => $start->toDateString()])) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'week' ? 'bg-indigo-100 text-indigo-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semaine</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('appointments.calendar', array_merge(request()->only('view','room_id'), $view === 'week' ? ['week' => $prevWeek] : ['month' => $prevMonth])) }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="min-w-[160px] text-center font-semibold text-gray-900">
                        {{ $view === 'week' ? $start->translatedFormat('d M') . ' – ' . $start->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->translatedFormat('d M Y') : $start->translatedFormat('F Y') }}
                    </span>
                    <a href="{{ route('appointments.calendar', array_merge(request()->only('view','room_id'), $view === 'week' ? ['week' => $nextWeek] : ['month' => $nextMonth])) }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200">
                {{-- En-têtes des jours --}}
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                    @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                        <div class="py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                {{-- Grille des semaines --}}
                <div class="divide-y divide-gray-100">
                    @foreach($weeks as $week)
                        <div class="grid grid-cols-7 {{ $view === 'week' ? 'min-h-[400px]' : 'min-h-[160px]' }}">
                            @foreach($week as $date)
                                <div class="group p-2 border-r border-gray-100 last:border-r-0 {{ $view === 'week' ? 'bg-white hover:bg-gray-50/30' : ($date->month !== $start->month ? 'bg-gray-50/50' : 'bg-white hover:bg-gray-50/30') }}">
                                        <div class="flex flex-col h-full">
                                            <div class="flex justify-between items-center mb-1">
                                                @if($date->month === $start->month)
                                                    <a href="{{ route('appointments.calendar', ['view' => 'day', 'date' => $date->format('Y-m-d')] + request()->only('room_id')) }}"
                                                        class="text-sm font-medium {{ $date->isToday() ? 'flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600' }}">
                                                        {{ $date->day }}
                                                    </a>
                                                    <a href="{{ route('appointments.create', ['date' => $date->format('Y-m-d')]) }}"
                                                        class="opacity-50 hover:opacity-100 text-indigo-500 hover:text-indigo-700 text-xs font-bold"
                                                        title="Ajouter RDV">
                                                        +
                                                    </a>
                                                @else
                                                    <span class="text-sm font-medium text-gray-400">{{ $date->day }}</span>
                                                @endif
                                            </div>
                                            <div class="flex-1 space-y-1 overflow-y-auto max-h-[130px]">
                                                @foreach($appointments->get($date->format('Y-m-d'), collect()) as $apt)
                                                    <a href="{{ route('appointments.show', $apt) }}"
                                                        class="block p-1.5 rounded-lg text-xs border-l-2 hover:shadow-sm transition
                                                            @if($apt->status === 'scheduled') border-indigo-500 bg-indigo-50/80 hover:bg-indigo-50
                                                            @elseif($apt->status === 'completed') border-emerald-500 bg-emerald-50/80 hover:bg-emerald-50
                                                            @elseif($apt->status === 'cancelled') border-red-300 bg-red-50/50 hover:bg-red-50
                                                            @else border-gray-400 bg-gray-100/80 hover:bg-gray-100
                                                            @endif">
                                                        <div class="font-mono font-medium text-gray-800">
                                                            {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}–{{ $apt->endTime() }}
                                                        </div>
                                                        <div class="font-medium text-gray-900 truncate" title="{{ $apt->patient->fullName() }}">
                                                            {{ $apt->patient->fullName() }}
                                                        </div>
                                                        @if($apt->pole || $apt->user)
                                                            <div class="text-gray-500 truncate text-[10px]">
                                                                {{ collect([$apt->pole?->name, $apt->user?->name])->filter()->join(' · ') ?: '—' }}
                                                            </div>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Légende --}}
            <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> Prévu</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Honoré</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> Annulé</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Absent</span>
            </div>
        </div>
    </div>
</x-app-layout>
