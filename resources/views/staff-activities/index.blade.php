<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $isDoctor ? 'Activités du cabinet' : 'Mes activités remontées' }}
            </h2>
            @if(!$isDoctor)
                <a href="{{ route('staff-activities.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Remonter une activité</a>
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
            @if($isDoctor)
                @php $unreadCount = \App\Models\StaffActivity::where('read_by_doctor', false)->count(); @endphp
                @if($unreadCount > 0)
                    <div class="mb-4 flex justify-end">
                        <form action="{{ route('staff-activities.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                                Tout marquer comme lu ({{ $unreadCount }} non lu{{ $unreadCount > 1 ? 's' : '' }})
                            </button>
                        </form>
                    </div>
                @endif
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($activities->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        @if($isDoctor)
                            Aucune activité remontée par le staff.
                        @else
                            Vous n'avez pas encore remonté d'activité. Utilisez « Remonter une activité » pour informer le médecin.
                        @endif
                    </div>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach($activities as $act)
                            <li class="p-6 hover:bg-gray-50 {{ !$act->read_by_doctor && $isDoctor ? 'bg-blue-50' : '' }}">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap text-sm text-gray-500 mb-2">
                                            <span class="font-medium text-gray-700">{{ $act->user?->name ?? '—' }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs
                                                @if($act->type === 'alert') bg-red-100 text-red-800
                                                @elseif($act->type === 'report') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ \App\Models\StaffActivity::TYPES[$act->type] ?? $act->type }}
                                            </span>
                                            <span>— {{ $act->created_at->format('d/m/Y H:i') }}</span>
                                            @if($isDoctor && !$act->read_by_doctor)
                                                <span class="text-blue-600 text-xs font-medium">Non lu</span>
                                            @endif
                                        </div>
                                        <p class="text-gray-900 whitespace-pre-wrap">{{ $act->activity }}</p>
                                    </div>
                                    @if($isDoctor && !$act->read_by_doctor)
                                        <form action="{{ route('staff-activities.mark-read', $act) }}" method="POST" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Marquer lu</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-4 border-t border-gray-200">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
