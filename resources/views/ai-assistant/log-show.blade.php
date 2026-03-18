<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Résultat — Log Assistant IA</h2>
            <div class="flex gap-2">
                <a href="{{ route('ai-assistant.logs', $log->isArchived() ? ['archive' => 1] : []) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">← Retour aux logs</a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Date</dt><dd class="font-medium">{{ $log->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Utilisateur</dt><dd class="font-medium">{{ $log->user?->name }}</dd></div>
                    <div><dt class="text-gray-500">Type</dt><dd><span class="px-2 py-0.5 rounded text-xs bg-indigo-100 text-indigo-800">{{ $log->context_type ?? '—' }}</span></dd></div>
                    <div><dt class="text-gray-500">Patient</dt><dd class="font-medium">{{ $log->patient?->fullName() ?? '—' }}</dd></div>
                    @if($log->archived_at)
                        <div><dt class="text-gray-500">Archivé le</dt><dd class="text-gray-600">{{ $log->archived_at->format('d/m/Y H:i') }}</dd></div>
                    @endif
                </dl>
            </div>
            @if($log->request_payload && count($log->request_payload))
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Contexte / requête</h3>
                    <dl class="text-sm text-gray-700 space-y-1">
                        @foreach($log->request_payload as $key => $value)
                            <div><dt class="text-gray-500 font-medium">{{ str_replace('_', ' ', ucfirst($key)) }}</dt>
                            <dd class="mt-0.5">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            @endif
            @if($log->response_text)
                @php
                    $rawText = trim($log->response_text);
                    $responseTextIsJson = str_starts_with($rawText, '{') || str_starts_with($rawText, '[');
                @endphp
                @if(!$responseTextIsJson)
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Réponse</h3>
                        <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $log->response_text }}</div>
                    </div>
                @endif
            @endif
            @if($log->response_json && count($log->response_json))
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Rapport</h3>
                    <div class="text-sm text-gray-800 leading-relaxed space-y-4">
                        @php
                            $j = $log->response_json;
                            $sectionLabels = [
                                'title' => 'Titre', 'titre' => 'Titre', 'titre_rapport' => 'Titre du rapport',
                                'resume_rapport' => 'Résumé', 'introduction' => 'Introduction',
                                'reasoning' => 'Justification', 'disclaimer' => 'À noter',
                                'analyse_efficacite_machines' => 'Efficacité des machines',
                                'analyse_efficacite_prestations' => 'Efficacité des prestations',
                                'recommandations_generales' => 'Recommandations générales',
                                'conclusion' => 'Conclusion', 'orientations' => 'Orientations',
                                'suggestions_actes' => 'Suggestions d\'actes',
                                'rappels_contre_indications' => 'Contre-indications',
                                'note_validation_medecin' => 'Validation médecin',
                            ];
                        @endphp
                        @foreach($log->response_json as $key => $value)
                            @if($value === null || $value === '')
                                @continue
                            @endif
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $sectionLabels[$key] ?? str_replace('_', ' ', ucfirst($key)) }}</p>
                                @if(is_string($value))
                                    <p class="whitespace-pre-wrap">{{ $value }}</p>
                                @elseif(is_array($value))
                                    @if(isset($value[0]) && (is_string($value[0]) || is_numeric($value[0])))
                                        <ul class="list-disc list-inside space-y-0.5">
                                            @foreach($value as $item)
                                                <li>{{ is_string($item) || is_numeric($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        @foreach($value as $idx => $item)
                                            @if(is_array($item))
                                                <div class="pl-3 border-l-2 border-gray-200 py-1 my-1">
                                                    @foreach($item as $k => $v)
                                                        <p><span class="text-gray-500">{{ str_replace('_', ' ', ucfirst($k)) }} :</span> {{ is_string($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</p>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p>{{ $item }}</p>
                                            @endif
                                        @endforeach
                                    @endif
                                @else
                                    <p>{{ $value }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(!$log->response_text && (!$log->response_json || count($log->response_json) === 0))
                <p class="text-gray-500 text-sm">Aucun contenu enregistré pour ce log.</p>
            @endif
            @if(auth()->user()?->hasRole('super_admin'))
                <div class="flex flex-wrap gap-2">
                    <form action="{{ route('ai-assistant.logs.archive', $log) }}" method="post" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-amber-100 text-amber-800 rounded-md hover:bg-amber-200 text-sm">
                            {{ $log->isArchived() ? 'Désarchiver' : 'Archiver' }}
                        </button>
                    </form>
                    <form action="{{ route('ai-assistant.logs.destroy', $log) }}" method="post" class="inline" onsubmit="return confirm('Supprimer définitivement ce log ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm">Supprimer</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
