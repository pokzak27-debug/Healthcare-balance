<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            @if(auth()->user()->unreadNotifications->isNotEmpty())
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Tout marquer comme lu</button>
                </form>
            @endif
            <form method="GET" class="flex gap-2 items-center">
                <select name="read" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    <option value="">Toutes</option>
                    <option value="0" {{ request('read') === '0' ? 'selected' : '' }}>Non lues</option>
                    <option value="1" {{ request('read') === '1' ? 'selected' : '' }}>Lues</option>
                </select>
                <select name="type" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    <option value="">Tous types</option>
                    <option value="new_booking" {{ request('type') === 'new_booking' ? 'selected' : '' }}>Nouveau RDV</option>
                    <option value="appointment_reminder" {{ request('type') === 'appointment_reminder' ? 'selected' : '' }}>Rappel RDV</option>
                </select>
                @if(request()->hasAny(['read', 'type']))
                    <a href="{{ route('notifications.index') }}" class="text-sm text-gray-500 hover:underline">Réinitialiser</a>
                @endif
            </form>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                @forelse($notifications as $notification)
                    <div class="p-4 border-b border-gray-100 {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-gray-900">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                @if(!$notification->read_at)
                                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:underline text-sm">Marquer lu</button>
                                    </form>
                                @endif
                                @if(!empty($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}" class="text-indigo-600 hover:underline text-sm">Voir</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="p-8 text-center text-gray-500">Aucune notification.</p>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="mt-4">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
