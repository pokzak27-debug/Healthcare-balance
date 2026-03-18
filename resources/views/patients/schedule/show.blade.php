<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Calendrier patient — {{ $patient->fullName() }}
            </h2>
            <div class="flex gap-2 flex-wrap items-center">
                @if(!empty($canExport))
                    <a href="{{ route('patients.schedule.export-pdf', $patient) }}" class="px-3 py-1.5 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">PDF</a>
                    <a href="{{ route('patients.schedule.export-word', $patient) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Word</a>
                @endif
                <a href="{{ route('patients.show', $patient) }}" class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded-md text-sm hover:bg-gray-300">← Dossier patient</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <p class="px-3 py-2 bg-green-50 text-green-700 text-sm rounded">{{ session('success') }}</p>
            @endif
            @if(session('info'))
                <p class="px-3 py-2 bg-blue-50 text-blue-700 text-sm rounded">{{ session('info') }}</p>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    Ce calendrier est une version <strong>simplifiée pour le patient</strong>, indépendante du calendrier global du cabinet.
                    Il affiche uniquement la <strong>date</strong>, l'<strong>heure</strong> et le <strong>pôle / spécialité</strong>.
                </p>

                @if(isset($periodStart, $periodEnd) && $periodStart && $periodEnd)
                    @php $periodStart = $periodStart instanceof \Carbon\CarbonInterface ? $periodStart : \Carbon\Carbon::parse($periodStart); $periodEnd = $periodEnd instanceof \Carbon\CarbonInterface ? $periodEnd : \Carbon\Carbon::parse($periodEnd); @endphp
                    <p class="text-sm text-gray-700 mb-4">
                        Période : <strong>{{ $periodStart->format('d/m/Y') }}</strong>
                        @if($periodEnd->ne($periodStart))
                            — <strong>{{ $periodEnd->format('d/m/Y') }}</strong>
                        @endif
                    </p>
                @endif

                @if(!$hasEntries)
                    <p class="text-sm text-gray-600 mb-4">
                        Aucun calendrier patient n’a encore été généré. Vous pouvez le créer à partir des rendez-vous existants du cabinet pour ce patient.
                    </p>
                    <form action="{{ route('patients.schedule.initialize', $patient) }}" method="post">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
                            Initialiser à partir des rendez-vous
                        </button>
                    </form>
                @else
                    <form action="{{ route('patients.schedule.update', $patient) }}" method="post" class="space-y-4">
                        @csrf
                        <p class="text-sm text-gray-600">
                            Vous pouvez <strong>ajouter, modifier ou réordonner</strong> les séances affichées ci-dessous.
                            Les champs vides seront ignorés. Ce calendrier <strong>n’impacte pas</strong> les rendez-vous internes du cabinet.
                        </p>

                        @error('entries.*.date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('entries.*.time')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('entries.*.pole_label')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Heure</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Pôle / spécialité</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $rows = $entries->values(); @endphp
                                @for($i = 0; $i < max($rows->count(), 5); $i++)
                                    @php $entry = $rows->get($i); @endphp
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input
                                                type="date"
                                                name="entries[{{ $i }}][date]"
                                                value="{{ old("entries.$i.date", $entry && ($d = $entry->date ?? $entry->session_date ?? null) ? (\Carbon\Carbon::parse($d)->format('Y-m-d')) : '') }}"
                                                class="block w-full border-gray-300 rounded-md text-sm"
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="time"
                                                name="entries[{{ $i }}][time]"
                                                value="{{ old("entries.$i.time", $entry?->time ?? $entry?->session_time ?? '') }}"
                                                class="block w-full border-gray-300 rounded-md text-sm"
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="text"
                                                name="entries[{{ $i }}][pole_label]"
                                                value="{{ old("entries.$i.pole_label", $entry?->pole_label ?? $entry?->pole ?? '') }}"
                                                placeholder="Ex. Kiné, Ostéo…"
                                                class="block w-full border-gray-300 rounded-md text-sm"
                                            >
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                Enregistrer le calendrier patient
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

