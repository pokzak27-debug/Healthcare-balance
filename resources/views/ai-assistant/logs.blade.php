<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Traçabilité — Assistant IA</h2>
            <div class="flex gap-2">
                @if(auth()->user()?->hasRole('super_admin'))
                    @if($showArchived)
                        <a href="{{ route('ai-assistant.logs') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Logs actifs</a>
                    @else
                        <a href="{{ route('ai-assistant.logs', ['archive' => 1]) }}" class="px-4 py-2 bg-amber-100 text-amber-800 rounded-md hover:bg-amber-200 text-sm">Voir les archives</a>
                    @endif
                @endif
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Dashboard</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <p class="mb-4 text-sm text-green-600 bg-green-50 px-3 py-2 rounded">{{ session('success') }}</p>
            @endif
            <p class="mb-4 text-sm text-gray-600">
                @if($showArchived)
                    Logs archivés — consultables et exploitables (voir résultat, désarchiver ou supprimer).
                @else
                    Historique des appels à l’assistant IA. Validation humaine obligatoire. Archiver pour ranger, consulter les archives pour retrouver les résultats.
                @endif
            </p>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Résumé</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->user?->name }}</td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs bg-indigo-100 text-indigo-800">{{ $log->context_type ?? '—' }}</span></td>
                                <td class="px-4 py-2 text-sm">{{ $log->patient?->fullName() ?? '—' }}</td>
                                @php
                                    $summary = null;
                                    $j = is_array($log->response_json ?? null) ? $log->response_json : null;
                                    if ($j) {
                                        if (!empty($j['title']) || !empty($j['resume_rapport'])) {
                                            $t = trim($j['title'] ?? '');
                                            $r = trim($j['resume_rapport'] ?? '');
                                            if ($t && $r) {
                                                $summary = $t . '. ' . Str::limit($r, 100);
                                            } elseif ($t) {
                                                $summary = $t;
                                            } else {
                                                $summary = Str::limit($r, 120);
                                            }
                                        } elseif (isset($j['steps']) && is_array($j['steps'])) {
                                            $summary = 'Protocole — ' . count($j['steps']) . ' étape(s)';
                                        } elseif (isset($j['suggested_min_dh']) || isset($j['suggested_max_dh'])) {
                                            $min = $j['suggested_min_dh'] ?? null;
                                            $max = $j['suggested_max_dh'] ?? null;
                                            if ($min !== null && $max !== null) {
                                                $summary = 'Tarification : ' . $min . ' – ' . $max . ' DH';
                                            } elseif ($min !== null) {
                                                $summary = 'Tarification : à partir de ' . $min . ' DH';
                                            } elseif ($max !== null) {
                                                $summary = 'Tarification : jusqu\'à ' . $max . ' DH';
                                            } else {
                                                $summary = 'Tarification (voir détail)';
                                            }
                                        } elseif (!empty($j['titre_rapport']) || !empty($j['introduction'])) {
                                            $summary = Str::limit($j['titre_rapport'] ?? $j['introduction'] ?? 'Rapport cabinet', 120);
                                        } else {
                                            $summary = 'Résultat structuré (voir détail)';
                                        }
                                    } elseif ($log->response_text) {
                                        $raw = trim($log->response_text);
                                        if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
                                            $summary = 'Résultat structuré (voir détail)';
                                        } else {
                                            $clean = preg_replace('/\s+/', ' ', $raw);
                                            $summary = Str::limit($clean, 120);
                                        }
                                    }
                                    $summary = $summary ?? '—';
                                @endphp
                                <td class="px-4 py-2 text-sm text-gray-700 max-w-md">
                                    <span class="block truncate" title="{{ e($summary) }}">{{ e($summary) }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <div class="flex flex-wrap gap-1">
                                        <a href="{{ route('ai-assistant.logs.show', $log) }}" class="px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-700 hover:bg-indigo-200">Voir résultat</a>
                                        @if(auth()->user()?->hasRole('super_admin'))
                                            <form action="{{ route('ai-assistant.logs.archive', $log) }}" method="post" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-700 hover:bg-amber-200">{{ $log->isArchived() ? 'Désarchiver' : 'Archiver' }}</button>
                                            </form>
                                            <form action="{{ route('ai-assistant.logs.destroy', $log) }}" method="post" class="inline" onsubmit="return confirm('Supprimer définitivement ce log ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 rounded text-xs bg-red-100 text-red-700 hover:bg-red-200">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ $showArchived ? 'Aucun log archivé.' : 'Aucun log.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($logs->hasPages())
                    <div class="px-4 py-3 border-t">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
