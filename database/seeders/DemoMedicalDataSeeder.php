<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DiagnosticFish;
use App\Models\MedicalEntry;
use App\Models\Patient;
use App\Models\Pole;
use App\Models\Prestation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder de démo médicale :
 * - 20 patients réalistes
 * - 1 fiche diagnostic complète par patient
 * - 1 à 4 rendez-vous par patient sur les 2 derniers mois (majorité honorés)
 * - Consultations standards → assistante (staff) ; Ostéopathie → docteur
 */
class DemoMedicalDataSeeder extends Seeder
{
    private ?User $doctor = null;

    private ?User $assistante = null;

    /** @var array<int, \App\Models\Prestation> */
    private array $prestationsByName = [];

    public function run(): void
    {
        $this->doctor = User::whereHas('role', fn ($q) => $q->where('slug', 'doctor'))->where('is_active', true)->first();
        $this->assistante = User::whereHas('role', fn ($q) => $q->where('slug', 'staff'))->where('is_active', true)->first();

        if (! $this->doctor || ! $this->assistante) {
            $this->command?->error('DemoMedicalDataSeeder : utilisateur docteur et/ou assistante (staff) manquant. Exécutez UserSeeder.');

            return;
        }

        $prestations = Prestation::active()->ordered()->get();
        if ($prestations->isEmpty()) {
            $this->command?->error('DemoMedicalDataSeeder : aucune prestation active. Exécutez HealthcareBalanceDemoSeeder.');

            return;
        }

        foreach ($prestations as $p) {
            $this->prestationsByName[$p->id] = $p;
        }

        $rooms = Room::active()->ordered()->get();
        $poles = Pole::where('is_active', true)->get();
        if ($rooms->isEmpty() || $poles->isEmpty()) {
            $this->command?->error('DemoMedicalDataSeeder : salles ou pôles manquants.');

            return;
        }

        $diagnosticTemplates = $this->getDiagnosticTemplates();
        $patients = $this->createPatients(20);
        $now = Carbon::now();
        $twoMonthsAgo = $now->copy()->subMonths(2)->startOfDay();

        foreach ($patients as $index => $patient) {
            $template = $diagnosticTemplates[$index % count($diagnosticTemplates)];
            $examDate = $twoMonthsAgo->copy()->addDays(rand(0, 30));
            $this->createDiagnosticFish($patient, $template, $examDate);

            $numAppointments = rand(1, 4);
            $usedSlots = [];
            for ($i = 0; $i < $numAppointments; $i++) {
                $date = $twoMonthsAgo->copy()->addDays(rand(0, 59));
                $time = $this->randomTimeSlot($usedSlots, $date->format('Y-m-d'));
                if (! $time) {
                    continue;
                }
                $usedSlots[$date->format('Y-m-d')][] = $time;
                $prestation = $prestations->random();
                $room = $prestation->room_id ? Room::find($prestation->room_id) : $rooms->random();
                $pole = $prestation->pole_id ? Pole::find($prestation->pole_id) : $poles->random();
                $user = $this->getUserForPrestation($prestation);
                $isPast = $date->lt(Carbon::today());
                $status = $isPast ? (rand(1, 10) <= 8 ? 'completed' : 'scheduled') : (rand(1, 10) <= 7 ? 'scheduled' : 'completed');

                $appointment = Appointment::create([
                    'patient_id' => $patient->id,
                    'pole_id' => $pole?->id,
                    'room_id' => $room?->id,
                    'prestation_id' => $prestation->id,
                    'user_id' => $user->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'appointment_time' => $time,
                    'duration_minutes' => $prestation->duration_minutes ?? 30,
                    'status' => $status,
                    'reason' => $template['reason_short'] ?? null,
                    'notes' => $isPast && $status === 'completed' ? $this->randomNoteClinique() : null,
                ]);

                if ($status === 'completed' && rand(1, 2) === 1) {
                    $this->createMedicalEntry($patient, $appointment, $user, $template);
                }
            }
        }

        $this->command?->info('DemoMedicalDataSeeder : 20 patients, fiches diagnostic et rendez-vous sur 2 mois créés.');
    }

    private function createPatients(int $count): array
    {
        $patients = [];
        for ($i = 0; $i < $count; $i++) {
            $patients[] = Patient::factory()->create();
        }

        return $patients;
    }

    /** @return list<array{examen_clinique: string, examen_subjectif: string, examen_objectif: string, examen_dynamique: string, examen_statique: string, diagnostic: string, protocole: string, reason_short: string}> */
    private function getDiagnosticTemplates(): array
    {
        return [
            [
                'examen_clinique' => "Patient conscient, bien orienté. Pas de fièvre. Examen rachis lombaire demandé.",
                'examen_subjectif' => "Douleur lombaire basse bilatérale, apparue progressivement, majorée en position assise prolongée et à la flexion. Pas de radiation. EVA 5/10.",
                'examen_objectif' => "Rachis lombaire : attitude antalgique en cyphose légère. Mobilité : flexion limitée à 60°, extension et latéroflexions douloureuses. Pas de signe neurologique. Palpation : contractures paravertébrales L4-L5.",
                'examen_dynamique' => "Flexion antérieure douloureuse, reprise de la lordose difficile. Latéroflexions asymétriques.",
                'examen_statique' => "Position debout : bascule pelvienne légère. Pas de scoliose.",
                'diagnostic' => "Lombalgie commune d’origine mécanique, syndrome facettaire et contractures paravertébrales.",
                'protocole' => "Thérapie manuelle, mobilisations, étirements et renforcement lombo-abdominal. Éducation posturale. 6 à 8 séances.",
                'reason_short' => "Lombalgie",
            ],
            [
                'examen_clinique' => "Examen clinique cervical et membre supérieur.",
                'examen_subjectif' => "Douleurs cervicales et trapèzes droits, céphalées en casque, depuis 3 semaines. Travail sur écran. EVA 4/10.",
                'examen_objectif' => "Rachis cervical : rotation droite limitée à 50°, flexion et extension sensibles. Trapèze droit contracturé. Tests neurologiques membres supérieurs négatifs.",
                'examen_dynamique' => "Enraidissement global du rachis cervical. Mouvements actifs limités en fin de course.",
                'examen_statique' => "Tête en antéposition. Épaules en élévation légère.",
                'diagnostic' => "Cervicalgie et syndrome myofascial trapèzo-dorsal droit, contexte professionnel.",
                'protocole' => "Mobilisations cervicales, travail sur les points gâchettes, conseils ergonomiques. Kiné respiratoire si tension. 5 à 6 séances.",
                'reason_short' => "Cervicalgie",
            ],
            [
                'examen_clinique' => "Examen du rachis dorsal et posture.",
                'examen_subjectif' => "Douleur interscapulaire, sensation de blocage, aggravée en position assise. Pas de douleur thoracique ni dyspnée.",
                'examen_objectif' => "Rachis dorsal : mobilité en rotation et flexion latérale limitées. Zone D5-D8 sensible à la palpation. Pas de déficit neurologique.",
                'examen_dynamique' => "Enraidissement segmentaire dorsal moyen. Mouvements combinés douloureux.",
                'examen_statique' => "Cyphose dorsale accentuée. Épaules en protraction.",
                'diagnostic' => "Dorsalgie mécanique, syndrome dysfonctionnel intervertébral mineur.",
                'protocole' => "Mobilisations dorsales, travail costal et scapulaire, renforcement des fixateurs de scapula. 4 à 6 séances.",
                'reason_short' => "Dorsalgie",
            ],
            [
                'examen_clinique' => "Examen lombo-sacré et membre inférieur.",
                'examen_subjectif' => "Douleur fessière gauche avec irradiation face postérieure de cuisse jusqu’au mollet, type brûlure. Aggravée à la toux. EVA 6/10.",
                'examen_objectif' => "Lasègue à 45° côté gauche. Pas de déficit moteur. Réflexe achilléen présent. Palpation L5-S1 sensible.",
                'examen_dynamique' => "Flexion rachidienne très limitée et douloureuse. Marche sur la pointe des pieds normale.",
                'examen_statique' => "Liste antalgique lombaire. Bassin en bascule.",
                'diagnostic' => "Sciatalgie L5 ou S1, conflit discoradiculaires léger, à surveiller.",
                'protocole' => "Antalgie par thérapie manuelle et positions de décharge. Pas de manipulation en phase aiguë. Rééducation progressive. Contrôle si aggravation.",
                'reason_short' => "Sciatalgie L5-S1",
            ],
            [
                'examen_clinique' => "Bilan postural et rachis.",
                'examen_subjectif' => "Douleurs diffuses rachis et épaules, fatigue en fin de journée, travail bureau. EVA 3/10.",
                'examen_objectif' => "Posture en antéposition tête et épaules. Rachis peu mobile en fin de course. Contractures trapèzes et paravertébraux. Pas de déficit.",
                'examen_dynamique' => "Mobilité globale conservée mais amplitudes sous-optimales.",
                'examen_statique' => "Déséquilibre antérieur du tronc. Hypercyphose dorsale, lordose lombaire aplatie.",
                'diagnostic' => "Douleurs posturales, syndrome de déconditionnement léger.",
                'protocole' => "Rééducation posturale, renforcement axial, étirements et conseils d’hygiène de vie. 6 séances.",
                'reason_short' => "Douleurs posturales",
            ],
            [
                'examen_clinique' => "Examen articulaire et musculaire membre supérieur.",
                'examen_subjectif' => "Raideur épaule droite, difficulté pour se coiffer et atteindre le dos. Pas de traumatisme. EVA 4/10.",
                'examen_objectif' => "Épaule : élévation antérieure 100°, rotation externe limitée. Arc douloureux 60-120°. Pas de force. Coiffe non testée en force (douleur).",
                'examen_dynamique' => "Limitation en rotation externe et abduction. Mobilisation passive douloureuse en fin de course.",
                'examen_statique' => "Pas d’amyotrophie. Épaule symétrique au repos.",
                'diagnostic' => "Limitation de mobilité d’épaule droite, capsulite rétractile ou tendinopathie de coiffe en phase douloureuse.",
                'protocole' => "Mobilisations progressives, étirements capsulaires, renforcement excentrique après diminution de la douleur. 8 à 10 séances.",
                'reason_short' => "Épaule droite",
            ],
            [
                'examen_clinique' => "Bilan fonctionnel rachis et membres.",
                'examen_subjectif' => "Sensation de raideur générale, difficulté à rester debout longtemps. Pas de douleur aiguë. EVA 2/10.",
                'examen_objectif' => "Mobilité rachidienne globale diminuée sans blocage. Hanches et épaules souples. Force conservée.",
                'examen_dynamique' => "Amplitudes articulaires en deçà des normes pour l’âge. Pas de douleur majeure.",
                'examen_statique' => "Posture légèrement en flexion. Souplesse globale réduite.",
                'diagnostic' => "Limitation de mobilité globale, raideur articulaire bénigne.",
                'protocole' => "Assouplissements globaux, renforcement doux, activités de mobilité. 4 à 5 séances.",
                'reason_short' => "Limitation mobilité",
            ],
            [
                'examen_clinique' => "Examen musculaire et récupération.",
                'examen_subjectif' => "Courbatures et fatigue musculaire après reprise du sport. Pas de douleur au repos. EVA 3/10.",
                'examen_objectif' => "Pas de lésion. Quadriceps et ischio-jambiers sensibles à la palpation. Mobilité conservée.",
                'examen_dynamique' => "Mouvements complets, légère gêne en étirement.",
                'examen_statique' => "Pas d’œdème ni d’hématome.",
                'diagnostic' => "Fatigue musculaire et courbatures post-effort, récupération incomplète.",
                'protocole' => "Drainage, étirements légers, récupération active. Conseils hydratation et progressivité.",
                'reason_short' => "Fatigue musculaire",
            ],
        ];
    }

    private function createDiagnosticFish(Patient $patient, array $template, Carbon $examDate): void
    {
        DiagnosticFish::create([
            'patient_id' => $patient->id,
            'user_id' => $this->doctor->id,
            'examen_clinique' => $template['examen_clinique'],
            'examen_subjectif' => $template['examen_subjectif'],
            'examen_objectif' => $template['examen_objectif'],
            'examen_dynamique' => $template['examen_dynamique'],
            'examen_statique' => $template['examen_statique'],
            'diagnostic' => $template['diagnostic'],
            'protocole' => $template['protocole'],
            'date_examen' => $examDate->format('Y-m-d'),
        ]);
    }

    private function getUserForPrestation(Prestation $prestation): User
    {
        $name = $prestation->name ?? '';
        $isOsteo = stripos($name, 'ostéopathie') !== false || stripos($name, 'osteopathie') !== false;

        return $isOsteo ? $this->doctor : $this->assistante;
    }

    private function randomTimeSlot(array &$usedSlots, string $dateKey): ?string
    {
        $slots = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'];
        $used = $usedSlots[$dateKey] ?? [];
        $available = array_values(array_diff($slots, $used));
        if (empty($available)) {
            return null;
        }
        $pick = $available[array_rand($available)];

        return $pick;
    }

    private function randomNoteClinique(): string
    {
        $notes = [
            "Séance bien tolérée. Patient coopérant.",
            "Amélioration des amplitudes. À revoir dans 1 semaine.",
            "Conseils posturaux donnés. Exercices à domicile expliqués.",
            "Douleur diminuée en fin de séance. Poursuite du protocole.",
        ];

        return $notes[array_rand($notes)];
    }

    private function createMedicalEntry(Patient $patient, Appointment $appointment, User $user, array $template): void
    {
        $types = ['note_clinique', 'diagnostic'];
        $type = $types[array_rand($types)];
        $content = $type === 'note_clinique'
            ? "Consultation " . ($appointment->prestation?->name ?? 'kiné') . ". " . $this->randomNoteClinique()
            : "Rappel diagnostic : " . ($template['diagnostic'] ?? '') . ". Protocole en cours.";

        MedicalEntry::create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'type' => $type,
            'content' => $content,
            'entry_date' => $appointment->appointment_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
        ]);
    }
}
