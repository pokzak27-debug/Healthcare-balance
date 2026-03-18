<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Quote;
use App\Models\Room;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAssistantService
{
    public function isEnabled(): bool
    {
        return config('ai.enabled', false) && ! empty(config('ai.api_key'));
    }

    /**
     * Construit le contexte fiche patient pour l'IA (anonymisé si besoin, ici données internes).
     */
    public function buildPatientContext(?Patient $patient): array
    {
        if (! $patient) {
            return ['patient' => null, 'message' => 'Aucun patient sélectionné.'];
        }

        $patient->load([
            'medicalEntries' => fn ($q) => $q->latest()->limit(50),
            'diagnosticFishes' => fn ($q) => $q->latest()->limit(20),
            'appointments' => fn ($q) => $q->latest()->limit(30)->with(['prestation', 'pole', 'room', 'user']),
        ]);

        $entriesByType = $patient->medicalEntries->groupBy('type')->map(fn ($items) => $items->map(fn ($e) => [
            'date' => $e->entry_date?->format('Y-m-d'),
            'content' => \Illuminate\Support\Str::limit($e->content, 500),
        ])->values()->toArray())->toArray();

        $diagnostics = $patient->diagnosticFishes->map(fn ($d) => [
            'date' => $d->date_examen?->format('Y-m-d'),
            'examen_subjectif' => \Illuminate\Support\Str::limit($d->examen_subjectif, 300),
            'examen_objectif' => \Illuminate\Support\Str::limit($d->examen_objectif, 300),
            'diagnostic' => $d->diagnostic,
            'protocole' => \Illuminate\Support\Str::limit($d->protocole, 400),
        ])->toArray();

        $appointments = $patient->appointments->map(fn ($a) => [
            'date' => $a->appointment_date?->format('Y-m-d'),
            'prestation' => $a->prestation?->name,
            'pole' => $a->pole?->name,
            'statut' => $a->status,
        ])->toArray();

        $invoices = Invoice::where('patient_id', $patient->id)->orderByDesc('invoice_date')->limit(20)->get(['invoice_date', 'amount', 'status'])->map(fn ($i) => [
            'date' => $i->invoice_date?->format('Y-m-d'),
            'amount' => (float) $i->amount,
            'status' => $i->status,
        ])->toArray();

        $quotes = Quote::where('patient_id', $patient->id)->orderByDesc('quote_date')->limit(10)->get(['quote_date', 'amount', 'status'])->map(fn ($q) => [
            'date' => $q->quote_date?->format('Y-m-d'),
            'amount' => (float) $q->amount,
            'status' => $q->status,
        ])->toArray();

        return [
            'patient' => [
                'id' => $patient->id,
                'genre' => $patient->sexe ?? '—',
                'date_naissance' => $patient->birth_date?->format('Y-m-d'),
                'notes_patient' => \Illuminate\Support\Str::limit($patient->notes, 500),
            ],
            'entrees_medicales' => $entriesByType,
            'diagnostics_protocoles' => $diagnostics,
            'rendez_vous' => $appointments,
            'factures' => $invoices,
            'devis' => $quotes,
        ];
    }

    /**
     * Résumé des prestations, pôles, salles, machines pour l'IA.
     */
    public function buildClinicResourcesContext(): array
    {
        $prestations = Prestation::active()->ordered()->with(['pole', 'room', 'machines'])->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'pole' => $p->pole?->name,
            'duree_min' => $p->duration_minutes,
            'salle' => $p->room?->name,
            'machines' => $p->machines->pluck('name')->toArray(),
        ])->toArray();

        $poles = Pole::active()->ordered()->get(['id', 'name'])->toArray();
        $rooms = Room::active()->ordered()->get(['id', 'name'])->toArray();
        $machines = Machine::active()->ordered()->with('room')->get()->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'salle' => $m->room?->name,
        ])->toArray();

        $pricingHint = Invoice::where('status', 'paid')
            ->whereNotNull('appointment_id')
            ->with('appointment.prestation')
            ->get()
            ->groupBy(fn ($i) => $i->appointment?->prestation_id)
            ->map(fn ($group, $pid) => [
                'prestation_id' => $pid,
                'prestation_name' => $group->first()?->appointment?->prestation?->name,
                'moyenne_dh' => round($group->avg('amount'), 2),
                'nombre' => $group->count(),
            ])
            ->values()
            ->toArray();

        return [
            'prestations' => $prestations,
            'poles' => $poles,
            'salles' => $rooms,
            'machines' => $machines,
            'historique_tarifaire_cabinet' => $pricingHint,
        ];
    }

    /**
     * Appel Groq/OpenAI-compatible. Retourne ['text' => ..., 'json' => ...?, 'tokens' => ...?, 'error' => ...?]
     *
     * - $requestJson: true pour demander explicitement du JSON structuré.
     * - Si l'API échoue avec "Failed to generate JSON", on refait automatiquement
     *   un appel SANS contrainte JSON et on renvoie le texte brut (pages plus fluides).
     */
    public function ask(string $userMessage, string $systemPrompt, ?array $context = null, bool $requestJson = false, int $maxTokens = 2048): array
    {
        if (! $this->isEnabled()) {
            return ['error' => 'AI non configurée ou désactivée.', 'text' => null];
        }

        $contextBlock = $context ? "\n\nContexte:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
        $fullUser = $userMessage . $contextBlock;

        $useJson = $requestJson || config('ai.json_mode');

        $basePayload = [
            'model' => config('ai.model'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $fullUser],
            ],
            'temperature' => 0.3,
            'max_tokens' => $maxTokens,
        ];

        $payload = $basePayload;
        if ($useJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $url = rtrim(config('ai.base_url'), '/') . '/chat/completions';
        $timeout = config('ai.timeout', 30);

        try {
            $response = Http::withToken(config('ai.api_key'))
                ->timeout($timeout)
                ->retry(config('ai.max_retries', 2), 500)
                ->acceptJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('AI Assistant API error', ['status' => $response->status(), 'body' => $response->body()]);

                $body = $response->json();
                $rawMessage = $body['error']['message'] ?? $body['message'] ?? $response->body();
                $msgString = (string) $rawMessage;

                $isJsonFailure = $useJson
                    && $response->status() === 400
                    && (str_contains($msgString, 'JSON') || str_contains($msgString, 'failed_generation'));

                // Fallback automatique : on réessaie sans contrainte JSON.
                if ($isJsonFailure) {
                    Log::info('AI Assistant retrying without JSON format after failed_generation');
                    $fallbackResponse = Http::withToken(config('ai.api_key'))
                        ->timeout($timeout)
                        ->retry(config('ai.max_retries', 2), 500)
                        ->acceptJson()
                        ->post($url, $basePayload);

                    if (! $fallbackResponse->successful()) {
                        $fbBody = $fallbackResponse->json();
                        $fbMsg = $fbBody['error']['message'] ?? $fbBody['message'] ?? $fallbackResponse->body();
                        $fbMsg = 'Erreur API ' . $fallbackResponse->status() . ': ' . (is_string($fbMsg) ? Str::limit($fbMsg, 200) : 'voir logs.');

                        return ['error' => $fbMsg, 'text' => null];
                    }

                    $fbData = $fallbackResponse->json();
                    $fbContent = $fbData['choices'][0]['message']['content'] ?? '';
                    $fbUsage = $fbData['usage'] ?? [];

                    return [
                        'text' => $fbContent,
                        'json' => null,
                        'tokens' => $fbUsage['total_tokens'] ?? null,
                        'error' => null,
                    ];
                }

                $message = 'Erreur API ' . $response->status() . ': ' . (is_string($rawMessage) ? Str::limit($rawMessage, 200) : 'voir logs.');
                return ['error' => $message, 'text' => null];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];

            $json = null;
            if ($useJson) {
                $decoded = json_decode($content, true);
                $json = is_array($decoded) ? $decoded : null;
            }

            return [
                'text' => $content,
                'json' => $json,
                'tokens' => $usage['total_tokens'] ?? null,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('AI Assistant exception', ['message' => $e->getMessage()]);
            return ['error' => 'Erreur: ' . $e->getMessage(), 'text' => null];
        }
    }

    /**
     * Suggestion de protocole pour un patient (et optionnellement un pôle).
     */
    public function suggestProtocol(?Patient $patient, ?string $poleHint = null): array
    {
        $patientContext = $this->buildPatientContext($patient);
        $resources = $this->buildClinicResourcesContext();
        $context = array_merge($patientContext, $resources);
        if ($poleHint) {
            $context['pole_demandé'] = $poleHint;
        }

        $prompt = config('ai.prompts.protocol');
        $userMessage = $poleHint
            ? "Rédige un rapport détaillé de protocole pour : {$poleHint}. Utilise uniquement les machines et salles disponibles dans le contexte."
            : "Rédige un rapport détaillé de protocole adapté au dossier patient. Utilise uniquement les machines et salles disponibles dans le contexte.";

        return $this->ask($userMessage, config('ai.prompts.system') . "\n\n" . $prompt, $context, true);
    }

    /**
     * Suggestion de tarification (structure prête pour comparaison marché marocain).
     *
     * Note: plus utilisée par l'UI Assistant IA (tarification supprimée côté front),
     * mais conservée pour usage éventuel en back-office / rapports.
     */
    public function suggestPricing(string $prestationOrProtocolName, ?Patient $patient = null): array
    {
        $resources = $this->buildClinicResourcesContext();
        $context = $resources;
        if ($patient) {
            $context['patient'] = $this->buildPatientContext($patient)['patient'] ?? null;
            $context['historique_facturation_patient'] = $this->buildPatientContext($patient)['factures'] ?? [];
        }

        $userMessage = "Suggère une tarification (en DH) pour : {$prestationOrProtocolName}.";
        $prompt = config('ai.prompts.pricing');

        // On ne force plus le JSON strict pour éviter "Failed to generate JSON" sur des réponses détaillées.
        // On essaye ensuite d'extraire un JSON du texte si possible.
        $result = $this->ask(
            $userMessage,
            config('ai.prompts.system') . "\n\n" . $prompt,
            $context,
            false,
            2048
        );

        if (empty($result['error']) && ! empty($result['text']) && empty($result['json'])) {
            $content = trim($result['text']);
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $result['json'] = $decoded;
            } else {
                $start = strpos($content, '{');
                if ($start !== false) {
                    $end = strrpos($content, '}');
                    if ($end !== false && $end > $start) {
                        $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
                        if (is_array($decoded)) {
                            $result['json'] = $decoded;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Aide à la consultation (orientation, actes, contre-indications).
     */
    public function consultationSupport(array $consultationData): array
    {
        $resources = $this->buildClinicResourcesContext();
        $context = array_merge($consultationData, $resources);
        $userMessage = "Éléments de consultation fournis. Propose des pistes d'orientation et d'actes.";
        $prompt = config('ai.prompts.consultation');

        return $this->ask($userMessage, config('ai.prompts.system') . "\n\n" . $prompt, $context, true);
    }

    /**
     * Question libre (général).
     */
    public function generalAsk(string $question, ?Patient $patient = null): array
    {
        $context = $this->buildClinicResourcesContext();
        if ($patient) {
            $context = array_merge($this->buildPatientContext($patient), $context);
        }
        $userMessage = $question;
        $prompt = config('ai.prompts.general');

        return $this->ask($userMessage, config('ai.prompts.system') . "\n\n" . $prompt, $context, false);
    }

    /**
     * Rapport stratégique cabinet : rôle praticien expert tous pôles, analyse machines/prestations, suggestions.
     * Sans mode JSON strict pour éviter l'erreur 400 "Failed to generate JSON" sur réponses longues.
     * On tente d'extraire du JSON du texte ; sinon le texte brut est affiché.
     */
    public function rapportCabinet(): array
    {
        $context = $this->buildClinicResourcesContext();
        $prompt = config('ai.prompts.rapport_cabinet');
        $userMessage = 'Rédige le rapport stratégique complet du cabinet : analyse de l\'efficacité des machines et des prestations, puis suggestions de nouvelles prestations et de nouvelles machines. Développe chaque section en paragraphes détaillés. Si possible, structure le résultat en JSON avec des champs clairs (titre_rapport, introduction, analyse_efficacite_machines, suggestions_nouvelles_prestations, suggestions_nouvelles_machines, recommandations_generales, conclusion). Sinon, renvoie un texte structuré lisible.';

        $result = $this->ask(
            $userMessage,
            config('ai.prompts.system') . "\n\n" . $prompt,
            $context,
            false,
            4096
        );

        if (empty($result['error']) && ! empty($result['text'])) {
            $content = trim($result['text']);
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $result['json'] = $decoded;
            } else {
                $start = strpos($content, '{');
                if ($start !== false) {
                    $end = strrpos($content, '}');
                    if ($end !== false && $end > $start) {
                        $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
                        if (is_array($decoded)) {
                            $result['json'] = $decoded;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
