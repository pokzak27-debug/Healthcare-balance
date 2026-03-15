<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rapports par salle et prestation</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.by-room-prestation.export-pdf', request()->only('month')) }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">Export PDF</a>
                <a href="{{ route('reports.by-room-prestation.export-excel', request()->only('month')) }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm">Export Excel</a>
                <a href="{{ route('reports.monthly', request()->only('month')) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Rapport mensuel PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('reports.by-room-prestation') }}" class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Mois</label>
                <div class="flex gap-2 mt-2">
                    <input type="month" name="month" value="{{ $month }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Appliquer</button>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-900">Par salle</h3>
                        <p class="text-sm text-gray-500">{{ $start }} → {{ $end }}</p>
                    </div>
                    <div class="p-4">
                        @if($byRoom->isEmpty())
                            <p class="text-gray-500">Aucun RDV avec salle sur cette période.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($byRoom as $row)
                                    <li class="flex justify-between py-2 border-b border-gray-100 last:border-0">
                                        <span class="font-medium">{{ $row->room?->name ?? 'Salle #'.$row->room_id }}</span>
                                        <span class="text-indigo-600 font-semibold">{{ $row->total }} RDV</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-900">Par prestation</h3>
                        <p class="text-sm text-gray-500">{{ $start }} → {{ $end }}</p>
                    </div>
                    <div class="p-4">
                        @if($byPrestation->isEmpty())
                            <p class="text-gray-500">Aucun RDV avec prestation sur cette période.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($byPrestation as $row)
                                    <li class="flex justify-between py-2 border-b border-gray-100 last:border-0">
                                        <span class="font-medium">{{ $row->prestation?->name ?? 'Prestation #'.$row->prestation_id }}</span>
                                        <span class="text-indigo-600 font-semibold">{{ $row->total }} RDV</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
