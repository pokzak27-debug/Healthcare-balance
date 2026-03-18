<?php

namespace App\Http\Controllers;

use App\Models\AiAssistantLog;
use App\Models\AiInteraction;
use App\Models\Patient;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $ai
    ) {
        // Accès restreint via routes : middleware auth + role:super_admin,doctor
    }

    /**
     * Page des logs (traçabilité) — doctor & super_admin. ?archive=1 pour voir les archives.
     */
    public function logs(Request $request): View
    {
        $showArchived = $request->boolean('archive');

        // L'accès à l'archive complète est réservé au super administrateur.
        if ($showArchived && ! $request->user()?->hasRole('super_admin')) {
            abort(403, 'Accès non autorisé.');
        }

        $query = AiAssistantLog::with(['user', 'patient'])
            ->when($showArchived, fn ($q) => $q->archived(), fn ($q) => $q->active())
            ->orderByDesc('created_at');
        $logs = $query->paginate(20)->withQueryString();

        return view('ai-assistant.logs', compact('logs', 'showArchived'));
    }

    /**
     * Affiche un log (voir le résultat complet) — doctor & super_admin.
     */
    public function show(AiAssistantLog $log): View
    {
        $log->load(['user', 'patient']);
        return view('ai-assistant.log-show', compact('log'));
    }

    /**
     * Archive un log — doctor & super_admin.
     */
    public function archive(AiAssistantLog $log): RedirectResponse
    {
        $archived = ! $log->archived_at;
        $log->update(['archived_at' => $archived ? now() : null]);
        $message = $archived ? 'Log archivé.' : 'Log désarchivé.';
        return redirect()->route('ai-assistant.logs', request()->only('archive'))->with('success', $message);
    }

    /**
     * Supprime définitivement un log — doctor & super_admin.
     */
    public function destroy(AiAssistantLog $log): RedirectResponse
    {
        $wasArchived = $log->isArchived();
        $log->delete();
        $params = $wasArchived ? ['archive' => 1] : [];
        return redirect()->route('ai-assistant.logs', $params)->with('success', 'Log supprimé.');
    }

    /**
     * Recherche patients par nom / prénom (tolérant aux fautes de frappe).
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        if (strlen($q) === 0) {
            return response()->json(['patients' => []]);
        }

        $term = '%' . preg_replace('/\s+/', '%', $q) . '%';
        // Terme « flou » pour tolérer les fautes de frappe : chaque caractère peut avoir des caractères entre (ex. dupnt → Dupont)
        $fuzzyTerm = strlen($q) >= 2 ? '%' . implode('%', mb_str_split($q)) . '%' : $term;

        $patients = Patient::where('is_active', true)
            ->where(function ($query) use ($term, $fuzzyTerm) {
                $query->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', [$term])
                    ->orWhereRaw('CONCAT(last_name, " ", first_name) LIKE ?', [$term])
                    ->orWhere('first_name', 'like', $fuzzyTerm)
                    ->orWhere('last_name', 'like', $fuzzyTerm)
                    ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ?', [$fuzzyTerm])
                    ->orWhereRaw('CONCAT(last_name, " ", first_name) LIKE ?', [$fuzzyTerm]);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(15)
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn ($p) => ['id' => $p->id, 'label' => $p->fullName()]);

        return response()->json(['patients' => $patients]);
    }

    /**
     * Config et statut pour l’interface (sans clé API).
     */
    public function config(): JsonResponse
    {
        $enabled = $this->ai->isEnabled();
        $message = null;
        if (! $enabled) {
            $message = empty(config('ai.api_key'))
                ? 'Ajoutez AI_API_KEY dans le fichier .env (clé Groq) puis exécutez : php artisan config:clear'
                : 'Activez l’assistant avec AI_ENABLED=true dans .env';
        }

        return response()->json([
            'enabled' => $enabled,
            'message' => $message,
            'disclaimer' => 'Cet assistant est une aide à la décision uniquement. Aucune suggestion ne remplace le jugement du médecin. Validation humaine obligatoire.',
        ]);
    }

    /**
     * Suggestion de protocole (patient optionnel, pôle optionnel).
     */
    public function suggestProtocol(Request $request): JsonResponse
    {
        $patient = $request->has('patient_id') ? Patient::find($request->get('patient_id')) : null;
        $poleHint = $request->get('pole');

        $result = $this->ai->suggestProtocol($patient, $poleHint);

        if ($patient && isset($result['json']) && is_array($result['json'])) {
            $result['json'] = array_merge($result['json'], $this->enrichProtocolWithPatient($patient));
        }

        $log = $this->logRequest('protocol', 'protocol', [
            'patient_id' => $patient?->id,
            'pole' => $poleHint,
        ], $result);

        return $this->jsonResponse($result, $log);
    }

    /**
     * Enrichit le JSON protocole avec les données patient (âge, état, fiches diagnostiques).
     */
    protected function enrichProtocolWithPatient(Patient $patient): array
    {
        $patient->load(['diagnosticFishes' => fn ($q) => $q->latest('date_examen')->latest()->limit(15)]);

        $age = null;
        if ($patient->birth_date) {
            $age = (int) $patient->birth_date->diffInYears(now());
        }

        $fiches = $patient->diagnosticFishes->map(fn ($f) => [
            'date' => $f->date_examen?->format('d/m/Y') ?? $f->created_at?->format('d/m/Y'),
            'diagnostic' => $f->diagnostic ? \Illuminate\Support\Str::limit($f->diagnostic, 500) : null,
            'protocole' => $f->protocole ? \Illuminate\Support\Str::limit($f->protocole, 400) : null,
            'examen_objectif' => $f->examen_objectif ? \Illuminate\Support\Str::limit($f->examen_objectif, 200) : null,
        ])->toArray();

        return [
            'patient_age_ans' => $age,
            'patient_etat_notes' => $patient->notes ? \Illuminate\Support\Str::limit($patient->notes, 800) : null,
            'patient_fiches_diagnostiques' => $fiches,
        ];
    }

    /**
     * Suggestion de tarification.
     */
    public function suggestPricing(Request $request): JsonResponse
    {
        $request->validate(['prestation_or_protocol' => 'required|string|max:500']);
        $patient = $request->has('patient_id') ? Patient::find($request->get('patient_id')) : null;

        $result = $this->ai->suggestPricing($request->get('prestation_or_protocol'), $patient);
        $log = $this->logRequest('pricing', 'pricing', [
            'prestation_or_protocol' => $request->get('prestation_or_protocol'),
            'patient_id' => $patient?->id,
        ], $result);

        return $this->jsonResponse($result, $log);
    }

    /**
     * Aide à la consultation (symptômes, examen, etc.).
     */
    public function consultationSupport(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'symptomes' => 'nullable|string|max:2000',
            'examen' => 'nullable|string|max:2000',
            'antecedents' => 'nullable|string|max:2000',
        ]);
        $data = array_filter($payload);

        $result = $this->ai->consultationSupport($data);
        $log = $this->logRequest('consultation', 'consultation', $data, $result);

        return $this->jsonResponse($result, $log);
    }

    /**
     * Question libre.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate(['question' => 'required|string|max:2000']);
        $patient = $request->has('patient_id') ? Patient::find($request->get('patient_id')) : null;

        $result = $this->ai->generalAsk($request->get('question'), $patient);
        $log = $this->logRequest('general', 'general', [
            'question' => $request->get('question'),
            'patient_id' => $patient?->id,
        ], $result);

        return $this->jsonResponse($result, $log);
    }

    /**
     * Rapport stratégique cabinet (praticien expert tous pôles, analyse machines/prestations, suggestions).
     */
    public function rapportCabinet(Request $request): JsonResponse
    {
        $result = $this->ai->rapportCabinet();
        $log = $this->logRequest('rapport_cabinet', 'rapport_cabinet', [], $result);

        return $this->jsonResponse($result, $log);
    }

    protected function logRequest(string $contextType, string $promptKey, array $requestPayload, array $result): ?AiAssistantLog
    {
        if (! config('ai.log_requests', true) || ! auth()->id()) {
            return null;
        }

        $log = AiAssistantLog::create([
            'user_id' => auth()->id(),
            'patient_id' => $requestPayload['patient_id'] ?? null,
            'context_type' => $contextType,
            'prompt_key' => $promptKey,
            'request_payload' => $requestPayload,
            'response_text' => $result['text'] ?? null,
            'response_json' => $result['json'] ?? null,
            'model' => config('ai.model'),
            'tokens_used' => $result['tokens'] ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        AiInteraction::create([
            'user_id' => auth()->id(),
            'source' => 'assistant',
            'context_type' => $contextType,
            'related_id' => $log->id,
            'prompt' => $requestPayload['question'] ?? ($requestPayload['prestation_or_protocol'] ?? null),
            'response' => $result['text'] ?? null,
            'meta' => [
                'log_id' => $log->id,
                'prompt_key' => $promptKey,
                'has_json' => isset($result['json']),
            ],
        ]);

        return $log;
    }

    protected function jsonResponse(array $result, ?AiAssistantLog $log): JsonResponse
    {
        $response = [
            'success' => empty($result['error']),
            'error' => $result['error'] ?? null,
            'text' => $result['text'] ?? null,
            'json' => $result['json'] ?? null,
            'log_id' => $log?->id,
            'disclaimer' => 'Aide à la décision uniquement. Validation médicale obligatoire.',
        ];

        return response()->json($response);
    }
}
