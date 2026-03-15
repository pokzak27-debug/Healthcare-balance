<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créneau de disponibilité</h2>
            <a href="{{ route('availability-slots.edit', $availabilitySlot) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div><span class="text-gray-500">Praticien :</span> {{ $availabilitySlot->user?->name ?? '—' }}</div>
                <div><span class="text-gray-500">Pôle :</span> {{ $availabilitySlot->pole?->name ?? '—' }}</div>
                <div><span class="text-gray-500">Jour :</span> {{ $availabilitySlot->day_name }}</div>
                <div><span class="text-gray-500">Horaires :</span> {{ \Carbon\Carbon::parse($availabilitySlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($availabilitySlot->end_time)->format('H:i') }}</div>
                <div><span class="text-gray-500">Durée créneau :</span> {{ $availabilitySlot->slot_minutes }} min</div>
                <div><span class="text-gray-500">Actif :</span> {{ $availabilitySlot->is_active ? 'Oui' : 'Non' }}</div>
            </div>
            <div class="mt-4">
                <a href="{{ route('availability-slots.index') }}" class="text-indigo-600 hover:underline">← Retour à la liste</a>
            </div>
        </div>
    </div>
</x-app-layout>
