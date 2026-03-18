<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Répartition des rendez-vous</h2>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('reports.stats') }}" class="mb-8 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Mois analysé</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label for="month" class="block text-xs font-medium text-gray-500 mb-1">Mois</label>
                        <input type="month" name="month" id="month" value="{{ $month }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">Appliquer</button>
                </div>
            </form>

            <p class="text-sm text-gray-500 mb-6">{{ $label }} ({{ $start }} → {{ $end }}) — {{ $totalRdvs }} RDV sur ce mois</p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Répartition par statut --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Par statut</h3>
                    @php $statusColors = ['scheduled' => 'bg-blue-500', 'completed' => 'bg-emerald-500', 'cancelled' => 'bg-red-400', 'no_show' => 'bg-gray-400']; @endphp
                    <div class="space-y-3">
                        @foreach($statusLabels as $key => $labelName)
                            @php $val = $byStatus[$key] ?? 0; $pct = $totalRdvs > 0 ? ($val / $totalRdvs) * 100 : 0; @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-0.5">
                                    <span class="text-gray-600">{{ $labelName }}</span>
                                    <span class="font-medium text-gray-900">{{ $val }}</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $statusColors[$key] ?? 'bg-gray-400' }} rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Par pôle --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Par pôle</h3>
                    @if($byPole->isEmpty())
                        <p class="text-gray-500 text-sm">Aucun RDV sur cette période.</p>
                    @else
                        @php $maxPole = $byPole->max('total') ?: 1; @endphp
                        <div class="space-y-2">
                            @foreach($byPole as $row)
                                <div>
                                    <div class="flex justify-between text-sm mb-0.5">
                                        <span class="text-gray-600">{{ $row->name }}</span>
                                        <span class="font-medium text-gray-900">{{ $row->total }}</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-400 rounded-full" style="width: {{ ($row->total / $maxPole) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Par praticien --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Par praticien</h3>
                    @if($byPractitioner->isEmpty())
                        <p class="text-gray-500 text-sm">Aucun RDV sur cette période.</p>
                    @else
                        @php $maxPrat = $byPractitioner->max('total') ?: 1; @endphp
                        <div class="space-y-2">
                            @foreach($byPractitioner as $row)
                                <div>
                                    <div class="flex justify-between text-sm mb-0.5">
                                        <span class="text-gray-600">{{ $row->name }}</span>
                                        <span class="font-medium text-gray-900">{{ $row->total }}</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-violet-400 rounded-full" style="width: {{ ($row->total / $maxPrat) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
