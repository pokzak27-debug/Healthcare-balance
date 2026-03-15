<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Absences des praticiens</h2>
            <a href="{{ route('practitioner-absences.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Nouvelle absence</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            <form method="GET" class="mb-4">
                <select name="user_id" onchange="this.form.submit()" class="rounded-md border-gray-300">
                    <option value="">Tous les praticiens</option>
                    @foreach($practitioners as $p)
                        <option value="{{ $p->id }}" {{ request('user_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Praticien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motif</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($absences as $a)
                            <tr>
                                <td class="px-6 py-4">{{ $a->user?->name }}</td>
                                <td class="px-6 py-4">{{ $a->start_date->format('d/m/Y') }} – {{ $a->end_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ $a->reason ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('practitioner-absences.edit', $a) }}" class="text-indigo-600 hover:underline text-sm">Modifier</a>
                                    <form action="{{ route('practitioner-absences.destroy', $a) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucune absence.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($absences->hasPages())
                    <div class="px-6 py-3 border-t">{{ $absences->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
