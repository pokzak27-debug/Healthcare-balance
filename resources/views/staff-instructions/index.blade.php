<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Consignes</h2>
            @if($isDoctor)
                <a href="{{ route('staff-instructions.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle consigne</a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($instructions->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        @if($isDoctor)
                            Aucune consigne envoyée. Les consignes que vous créez seront visibles par le staff.
                        @else
                            Aucune consigne reçue.
                        @endif
                    </div>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($instructions as $ins)
                            <li class="p-6 hover:bg-gray-50">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap text-sm text-gray-500 mb-2">
                                            @if($isDoctor)
                                                <span>À : {{ $ins->toUser ? $ins->toUser->name : 'Tout le staff' }}</span>
                                            @else
                                                <span>De : {{ $ins->fromUser?->name ?? '—' }}</span>
                                            @endif
                                            <span>— {{ $ins->created_at->format('d/m/Y H:i') }}</span>
                                            @if($ins->is_read)
                                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">Lu</span>
                                            @endif
                                            @if($ins->is_done)
                                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Effectuée</span>
                                                @if($ins->done_at)
                                                    <span class="text-gray-400">le {{ $ins->done_at->format('d/m/Y H:i') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                        @if($ins->patient_id)
                                            <div class="space-y-1 mb-2 text-sm">
                                                @if($ins->patient)
                                                    <p><span class="font-medium text-gray-700">Patient :</span> {{ $ins->patient->fullName() }}</p>
                                                @endif
                                                @if($ins->room)
                                                    <p><span class="font-medium text-gray-700">Salle :</span> {{ $ins->room->name }}</p>
                                                @endif
                                                @if($ins->prestation_names !== '—')
                                                    <p><span class="font-medium text-gray-700">Prestation(s) :</span> {{ $ins->prestation_names }}</p>
                                                @endif
                                                @if($ins->duration_minutes)
                                                    <p><span class="font-medium text-gray-700">Durée :</span> {{ $ins->duration_minutes }} min</p>
                                                @endif
                                                @if($ins->voir_patient)
                                                    <p><span class="font-medium text-amber-700">Voir le patient</span></p>
                                                @endif
                                            </div>
                                        @endif
                                        @if($ins->instruction)
                                            <p class="text-gray-900 whitespace-pre-wrap">{{ $ins->instruction }}</p>
                                        @endif
                                    </div>
                                    @if(!$isDoctor && !$ins->is_done)
                                        <div class="flex flex-col gap-2 shrink-0">
                                            @if(!$ins->is_read)
                                                <form action="{{ route('staff-instructions.mark-read', $ins) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Confirmer lu</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('staff-instructions.mark-done', $ins) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700">Marquer effectuée</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-4 border-t border-gray-200">
                        {{ $instructions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
