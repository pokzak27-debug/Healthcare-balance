<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recherche globale</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('search') }}" class="mb-8">
                <div class="flex gap-2">
                    <input type="search" name="q" value="{{ old('q', $q ?? '') }}"
                        placeholder="Rechercher patients, rendez-vous… (min. 2 caractères)"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Rechercher</button>
                </div>
            </form>

            @if(strlen($q ?? '') >= 2)
                <div class="space-y-8">
                    <section>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Patients ({{ $patients->count() }})</h3>
                        @if($patients->isEmpty())
                            <p class="text-gray-500">Aucun patient trouvé.</p>
                        @else
                            <ul class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-200 overflow-hidden">
                                @foreach($patients as $p)
                                    <li class="px-6 py-4 hover:bg-gray-50">
                                        <a href="{{ route('patients.show', $p) }}" class="block">
                                            <span class="font-medium text-gray-900">{{ $p->fullName() }}</span>
                                            <span class="text-sm text-gray-500 ml-2">{{ $p->email ?? $p->phone ?? '—' }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>

                    <section>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rendez-vous ({{ $appointments->count() }})</h3>
                        @if($appointments->isEmpty())
                            <p class="text-gray-500">Aucun rendez-vous trouvé.</p>
                        @else
                            <ul class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-200 overflow-hidden">
                                @foreach($appointments as $apt)
                                    <li class="px-6 py-4 hover:bg-gray-50">
                                        <a href="{{ route('appointments.show', $apt) }}" class="block">
                                            <span class="font-medium text-gray-900">{{ $apt->patient?->fullName() ?? '—' }}</span>
                                            <span class="text-sm text-gray-600 ml-2">
                                                {{ $apt->appointment_date?->format('d/m/Y') }}
                                                {{ $apt->appointment_time ? \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') : '' }}
                                                — {{ $apt->pole?->name ?? '—' }}
                                                @if($apt->reason)
                                                    · {{ Str::limit($apt->reason, 40) }}
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>
            @else
                <p class="text-gray-500">Saisissez au moins 2 caractères pour lancer la recherche.</p>
            @endif
        </div>
    </div>
</x-app-layout>
