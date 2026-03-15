<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Logs d'activité</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <input type="text" name="action" value="{{ request('action') }}" placeholder="ex: patient.created"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur ID</label>
                    <input type="number" name="user_id" value="{{ request('user_id') }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Filtrer</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $log->user?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-700 font-mono text-sm">{{ $log->action }}</td>
                                <td class="px-6 py-4 text-gray-600 text-sm">{{ $log->description ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucun log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($logs->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
