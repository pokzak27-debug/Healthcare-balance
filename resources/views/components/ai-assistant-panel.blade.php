@if(can_access_dossier())
<div x-data="aiAssistantPanel()" x-cloak class="fixed bottom-6 left-6 z-40" aria-label="Assistant IA aide à la décision">
    <button x-show="!open" @click="open = true; loadConfig()" type="button" class="flex items-center justify-center w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" title="Assistant IA (aide à la décision)">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-x-4" class="absolute bottom-16 left-0 w-[420px] max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col max-h-[85vh]" @click.outside="open = false">
        <div class="px-4 py-3 bg-indigo-600 text-white flex items-center justify-between shrink-0">
            <span class="font-semibold text-sm">Assistant IA — Aide à la décision</span>
            <button @click="open = false" type="button" class="p-1 rounded hover:bg-white/20"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <p class="px-4 py-2 bg-amber-50 text-amber-800 text-xs border-b border-amber-100">Aucune suggestion ne remplace le jugement du médecin. Validation humaine obligatoire.</p>

        <div class="p-3 flex-1 overflow-y-auto space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mode</label>
                <select x-model="mode" class="block w-full text-sm rounded-md border-gray-300">
                    <option value="protocol">Protocole</option>
                    <option value="consultation">Consultation</option>
                    <option value="ask">Question libre</option>
                    <option value="rapport_cabinet">Rapport cabinet</option>
                </select>
            </div>
            <template x-if="mode === 'protocol'">
                <div class="space-y-2">
                    <div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Patient (optionnel)</label>
                        <input type="text" x-model="patientQuery" @input="searchPatientsDebounced()" @focus="patientDropdownOpen = patientSuggestions.length > 0" @keydown.escape="patientDropdownOpen = false" placeholder="Nom ou prénom (1 car. min)" class="block w-full text-sm rounded-md border-gray-300">
                        <p x-show="selectedPatientLabel" class="mt-1 text-xs text-indigo-600" x-text="'→ ' + selectedPatientLabel"></p>
                        <ul x-show="patientDropdownOpen && patientSuggestions.length" @click.outside="patientDropdownOpen = false" class="absolute z-10 mt-0.5 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                            <template x-for="p in patientSuggestions" :key="p.id">
                                <li @click="selectPatient(p.id, p.label)" class="px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50" x-text="p.label"></li>
                            </template>
                        </ul>
                        <button type="button" x-show="selectedPatientLabel" @click="clearPatient()" class="mt-1 text-xs text-red-600 hover:underline">Effacer</button>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pôle / indication (optionnel)</label>
                        <input type="text" x-model="poleHint" placeholder="ex. Kiné, Esthétique" class="block w-full text-sm rounded-md border-gray-300">
                    </div>
                </div>
            </template>
            {{-- Mode tarification supprimé --}}
            <template x-if="mode === 'consultation'">
                <div class="space-y-2">
                    <input type="text" x-model="consultation.symptomes" placeholder="Symptômes" class="block w-full text-sm rounded-md border-gray-300">
                    <input type="text" x-model="consultation.examen" placeholder="Examen" class="block w-full text-sm rounded-md border-gray-300">
                    <input type="text" x-model="consultation.antecedents" placeholder="Antécédents" class="block w-full text-sm rounded-md border-gray-300">
                </div>
            </template>
            <template x-if="mode === 'ask'">
                <div class="space-y-2">
                    <div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Patient (optionnel)</label>
                        <input type="text" x-model="patientQuery" @input="searchPatientsDebounced()" @focus="patientDropdownOpen = patientSuggestions.length > 0" @keydown.escape="patientDropdownOpen = false" placeholder="Nom ou prénom" class="block w-full text-sm rounded-md border-gray-300">
                        <p x-show="selectedPatientLabel" class="mt-1 text-xs text-indigo-600" x-text="'→ ' + selectedPatientLabel"></p>
                        <ul x-show="patientDropdownOpen && patientSuggestions.length" @click.outside="patientDropdownOpen = false" class="absolute z-10 mt-0.5 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                            <template x-for="p in patientSuggestions" :key="p.id">
                                <li @click="selectPatient(p.id, p.label)" class="px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50" x-text="p.label"></li>
                            </template>
                        </ul>
                        <button type="button" x-show="selectedPatientLabel" @click="clearPatient()" class="mt-1 text-xs text-red-600 hover:underline">Effacer</button>
                    </div>
                    <textarea x-model="question" rows="3" placeholder="Votre question..." class="block w-full text-sm rounded-md border-gray-300"></textarea>
                </div>
            </template>
            <template x-if="mode === 'rapport_cabinet'">
                <p class="text-xs text-gray-600">Rapport stratégique : analyse des machines et prestations, suggestions de nouvelles offres et équipements (rôle praticien expert tous pôles).</p>
            </template>

            <button type="button" @click="send()" :disabled="loading || !enabled" class="w-full py-2 px-4 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="loading ? 'Génération...' : (mode === 'rapport_cabinet' ? 'Générer le rapport cabinet' : 'Obtenir une suggestion')"></span>
            </button>

            <div x-show="error" class="p-2 rounded bg-red-50 text-red-700 text-sm" x-text="error"></div>
            <div x-show="formattedReportHtml" class="ai-report p-3 rounded-lg border border-indigo-100 bg-indigo-50/50 text-sm overflow-y-auto max-h-[50vh]" x-html="formattedReportHtml"></div>
            <div x-show="!formattedReportHtml && responseText" class="p-3 rounded bg-gray-50 border border-gray-200 text-sm text-gray-800 leading-relaxed whitespace-pre-wrap" x-html="responseText"></div>
        </div>
    </div>
</div>
<script>
function aiAssistantPanel() {
    return {
        open: false,
        enabled: false,
        loading: false,
        mode: 'protocol',
        patientId: '',
        patientQuery: '',
        patientSuggestions: [],
        selectedPatientLabel: '',
        patientDropdownOpen: false,
        patientSearchTimer: null,
        poleHint: '',
        question: '',
        consultation: { symptomes: '', examen: '', antecedents: '' },
        error: '',
        responseText: '',
        responseJson: null,
        get responseJsonLabel() { return this.responseJson ? JSON.stringify(this.responseJson, null, 2) : ''; },
        escapeHtml(s) {
            if (s == null || s === '') return '';
            var t = String(s);
            return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        },
        formatReportHtml() {
            var j = this.responseJson;
            if (!j || typeof j !== 'object') return '';
            var esc = this.escapeHtml.bind(this);
            if (j.suggested_min_dh != null || j.suggested_max_dh != null) {
                var html = '<div class="space-y-4"><p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide border-b border-indigo-200 pb-1">Tarification suggérée</p>';
                var min = j.suggested_min_dh != null ? String(j.suggested_min_dh) : '';
                var max = j.suggested_max_dh != null ? String(j.suggested_max_dh) : '';
                html += '<div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-4">';
                html += '<p class="text-2xl font-semibold text-gray-900 tracking-tight">' + esc(min) + ' – ' + esc(max) + ' <span class="text-lg font-medium text-gray-600">DH</span></p>';
                html += '<p class="text-sm text-gray-500 mt-1">Fourchette indicative pour la prestation demandée</p></div>';
                if (j.reasoning) {
                    html += '<div><p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Contexte et justification</p>';
                    html += '<p class="text-sm text-gray-700 leading-relaxed">' + esc(j.reasoning) + '</p></div>';
                }
                if (j.facteurs_cles && j.facteurs_cles.length) {
                    html += '<div><p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Facteurs clés</p><ul class="list-disc list-inside text-sm text-gray-700 space-y-1">';
                    j.facteurs_cles.forEach(function(f) { html += '<li>' + esc(f) + '</li>'; });
                    html += '</ul></div>';
                }
                if (j.disclaimer) {
                    html += '<div class="pt-3 border-t border-gray-200"><p class="text-xs text-gray-500 leading-relaxed italic">' + esc(j.disclaimer) + '</p></div>';
                }
                html += '</div>';
                return html;
            }
            if (j.titre_rapport || j.introduction || j.analyse_efficacite_machines || j.suggestions_nouvelles_prestations) {
                var r = '<div class="space-y-4"><p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide border-b border-indigo-200 pb-1">Rapport stratégique cabinet</p>';
                if (j.titre_rapport) r += '<h3 class="text-base font-semibold text-gray-900">' + esc(j.titre_rapport) + '</h3>';
                if (j.introduction) r += '<div class="text-sm text-gray-700 leading-relaxed">' + esc(j.introduction) + '</div>';
                if (j.analyse_efficacite_machines) {
                    r += '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Efficacité des machines</p><p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">' + esc(j.analyse_efficacite_machines) + '</p></div>';
                }
                if (j.analyse_efficacite_prestations) {
                    r += '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Efficacité des prestations</p><p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">' + esc(j.analyse_efficacite_prestations) + '</p></div>';
                }
                if (j.suggestions_nouvelles_prestations && j.suggestions_nouvelles_prestations.length) {
                    r += '<div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-3"><p class="text-xs font-medium text-emerald-800 uppercase tracking-wide mb-2">Suggestions de nouvelles prestations</p><ul class="space-y-2">';
                    j.suggestions_nouvelles_prestations.forEach(function(s) {
                        r += '<li class="text-sm"><strong>' + esc(s.nom || s.name || '') + '</strong>' + (s.pole ? ' <span class="text-gray-500">(' + esc(s.pole) + ')</span>' : '') + '<p class="text-gray-700 mt-0.5">' + esc(s.description || '') + '</p>' + (s.justification ? '<p class="text-xs text-gray-600 mt-0.5">' + esc(s.justification) + '</p>' : '') + '</li>';
                    });
                    r += '</ul></div>';
                }
                if (j.suggestions_nouvelles_machines && j.suggestions_nouvelles_machines.length) {
                    r += '<div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3"><p class="text-xs font-medium text-blue-800 uppercase tracking-wide mb-2">Suggestions de nouvelles machines</p><ul class="space-y-2">';
                    j.suggestions_nouvelles_machines.forEach(function(m) {
                        r += '<li class="text-sm"><strong>' + esc(m.nom || m.name || '') + '</strong>' + (m.usage_principal ? ' — ' + esc(m.usage_principal) : '') + (m.justification ? '<p class="text-gray-700 mt-0.5">' + esc(m.justification) + '</p>' : '') + '</li>';
                    });
                    r += '</ul></div>';
                }
                if (j.recommandations_generales) {
                    r += '<div class="rounded-lg border border-amber-200 bg-amber-50/50 p-3"><p class="text-xs font-medium text-amber-800 uppercase tracking-wide mb-2">Recommandations générales</p><p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">' + esc(j.recommandations_generales) + '</p></div>';
                }
                if (j.conclusion) r += '<div class="pt-2 border-t border-gray-200 text-sm text-gray-700 leading-relaxed">' + esc(j.conclusion) + '</div>';
                r += '</div>';
                return r;
            }
            var hasPatient = j.patient_age_ans != null || j.patient_etat_notes || (j.patient_fiches_diagnostiques && j.patient_fiches_diagnostiques.length);
            var hasProtocol = j.title && Array.isArray(j.steps);
            if (!hasPatient && !hasProtocol) {
                if (j.prestations && Array.isArray(j.prestations)) {
                    var out = '<div class="space-y-3">';
                    if (j.titre) out += '<h3 class="text-base font-semibold text-indigo-800">' + esc(j.titre) + '</h3>';
                    out += '<ul class="space-y-2 text-xs">';
                    j.prestations.forEach(function(p) {
                        out += '<li class="flex justify-between"><span>' + esc(p.nom || p.name || p) + '</span>';
                        if (p.prix != null || p.prix_dh != null) out += ' <strong>' + esc(p.prix != null ? p.prix : p.prix_dh) + ' DH</strong>';
                        out += '</li>';
                    });
                    out += '</ul></div>';
                    return out;
                }
                return this.jsonToReadableReport(j, esc);
            }
            var html = '<div class="space-y-4"><p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide border-b border-indigo-200 pb-1">Rapport complet</p>';
            if (hasPatient) {
                html += '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-2">';
                html += '<p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Patient</p>';
                if (j.patient_age_ans != null) html += '<p class="text-sm text-gray-800"><span class="text-gray-500">Âge :</span> ' + esc(j.patient_age_ans) + ' ans</p>';
                if (j.patient_etat_notes) html += '<p class="text-sm text-gray-800"><span class="text-gray-500">État / notes :</span> ' + esc(j.patient_etat_notes) + '</p>';
                if (j.patient_fiches_diagnostiques && j.patient_fiches_diagnostiques.length) {
                    html += '<p class="text-xs font-medium text-gray-600 mt-2 mb-1">Fiches diagnostiques</p>';
                    j.patient_fiches_diagnostiques.forEach(function(f, idx) {
                        html += '<div class="text-xs border-l-2 border-gray-300 pl-2 py-1 my-1 bg-white rounded pr-2">';
                        if (f.date) html += '<span class="text-gray-500">' + esc(f.date) + '</span> ';
                        if (f.diagnostic) html += '<p class="text-gray-800 mt-0.5">' + esc(f.diagnostic) + '</p>';
                        if (f.protocole) html += '<p class="text-gray-700 mt-0.5"><span class="text-gray-500">Protocole :</span> ' + esc(f.protocole) + '</p>';
                        if (f.examen_objectif) html += '<p class="text-gray-600 mt-0.5">' + esc(f.examen_objectif) + '</p>';
                        html += '</div>';
                    });
                }
                html += '</div>';
            }
            if (hasProtocol) {
                html += '<div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 space-y-3">';
                html += '<p class="text-xs font-medium text-indigo-700 uppercase tracking-wide">Rapport — Protocole suggéré</p>';
                html += '<h3 class="text-base font-semibold text-indigo-800">' + esc(j.title) + '</h3>';
                if (j.resume_rapport) {
                    html += '<div class="text-sm text-gray-700 leading-relaxed bg-white p-2 rounded border border-indigo-100">' + esc(j.resume_rapport) + '</div>';
                }
                if (j.nombre_seances_total != null || (j.machines_suggerees && j.machines_suggerees.length) || (j.salles_utilisees && j.salles_utilisees.length)) {
                    html += '<div class="flex flex-wrap gap-3 text-xs">';
                    if (j.nombre_seances_total != null) html += '<span class="px-2 py-1 rounded bg-indigo-100 text-indigo-800"><strong>' + esc(j.nombre_seances_total) + '</strong> séance(s) au total</span>';
                    if (j.machines_suggerees && j.machines_suggerees.length) html += '<span class="px-2 py-1 rounded bg-gray-100 text-gray-700">Machines : ' + esc(j.machines_suggerees.join(', ')) + '</span>';
                    if (j.salles_utilisees && j.salles_utilisees.length) html += '<span class="px-2 py-1 rounded bg-gray-100 text-gray-700">Salles : ' + esc(j.salles_utilisees.join(', ')) + '</span>';
                    html += '</div>';
                }
                if (j.a_faire && j.a_faire.length) {
                    html += '<div class="bg-green-50 border border-green-100 rounded p-2"><p class="text-xs font-medium text-green-800 mb-1">À faire</p><ul class="list-disc list-inside text-xs text-green-900 space-y-0.5">';
                    j.a_faire.forEach(function(x) { html += '<li>' + esc(x) + '</li>'; });
                    html += '</ul></div>';
                }
                if (j.a_eviter && j.a_eviter.length) {
                    html += '<div class="bg-red-50 border border-red-100 rounded p-2"><p class="text-xs font-medium text-red-800 mb-1">À éviter</p><ul class="list-disc list-inside text-xs text-red-900 space-y-0.5">';
                    j.a_eviter.forEach(function(x) { html += '<li>' + esc(x) + '</li>'; });
                    html += '</ul></div>';
                }
                html += '<div class="space-y-2"><p class="text-xs font-medium text-gray-600">Détail des étapes</p>';
                j.steps.forEach(function(s) {
                    var num = s.etape != null ? s.etape : (j.steps.indexOf(s) + 1);
                    html += '<div class="pl-3 border-l-2 border-indigo-200 py-2 bg-white rounded pr-2">';
                    html += '<p class="font-medium text-gray-800">Étape ' + num + ' — ' + esc(s.prestation || '') + '</p>';
                    if (s.nb_seances_etape != null) html += '<p class="text-xs text-indigo-600">' + esc(s.nb_seances_etape) + ' séance(s)</p>';
                    if (s.duree != null) html += '<p class="text-xs text-gray-600">Durée : ' + esc(s.duree) + ' min</p>';
                    if (s.salle) html += '<p class="text-xs text-gray-600">Salle : ' + esc(s.salle) + '</p>';
                    if (s.machines && s.machines.length) html += '<p class="text-xs text-gray-600">Machines : ' + esc(s.machines.join(', ')) + '</p>';
                    if (s.description) html += '<p class="text-xs text-gray-700 mt-1">' + esc(s.description) + '</p>';
                    html += '</div>';
                });
                html += '</div>';
                if (j.precautions && j.precautions.length) {
                    html += '<p class="text-xs font-medium text-gray-600 mt-2">Précautions</p><ul class="list-disc list-inside text-xs text-gray-700 space-y-0.5">';
                    j.precautions.forEach(function(p) { html += '<li>' + esc(p) + '</li>'; });
                    html += '</ul>';
                }
                if (j.note_humain_requise) {
                    html += '<div class="mt-2 p-2 rounded bg-amber-50 border border-amber-200"><p class="text-xs font-medium text-amber-800">Validation humaine requise</p><p class="text-xs text-amber-900 mt-0.5">' + esc(j.note_humain_requise) + '</p></div>';
                }
                html += '</div>';
            }
            html += '</div>';
            return html;
        },
        jsonToReadableReport(j, esc) {
            if (!j || typeof j !== 'object') return '';
            var labels = { title: 'Titre', titre: 'Titre', resume_rapport: 'Résumé', reasoning: 'Justification', disclaimer: 'À noter', orientations: 'Orientations', suggestions_actes: 'Suggestions d\'actes', rappels_contre_indications: 'Contre-indications', note_validation_medecin: 'Validation médecin', error: 'Message' };
            var html = '<div class="space-y-3"><p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide border-b border-indigo-200 pb-1">Rapport</p>';
            function renderVal(v) {
                if (v == null) return '';
                if (typeof v === 'string') return esc(v);
                if (typeof v === 'number') return esc(String(v));
                if (Array.isArray(v)) {
                    if (v.length === 0) return '';
                    var s = '<ul class="list-disc list-inside text-sm text-gray-700 space-y-0.5">';
                    v.forEach(function(item) {
                        if (typeof item === 'object' && item !== null) {
                            s += '<li class="pl-2">';
                            for (var k in item) if (Object.prototype.hasOwnProperty.call(item, k)) s += '<span class="font-medium">' + esc(String(k)) + ' : </span>' + renderVal(item[k]) + ' ';
                            s += '</li>';
                        } else s += '<li>' + esc(String(item)) + '</li>';
                    });
                    return s + '</ul>';
                }
                if (typeof v === 'object') return '<span class="text-gray-700">' + JSON.stringify(v).replace(/[{}"[\]]/g, ' ') + '</span>';
                return esc(String(v));
            }
            for (var key in j) {
                if (!Object.prototype.hasOwnProperty.call(j, key)) continue;
                var val = j[key];
                if (val === null || val === '') continue;
                var label = labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                html += '<div class="pb-2"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">' + esc(label) + '</p>';
                html += '<div class="text-sm text-gray-800 leading-relaxed">' + (typeof val === 'string' ? '<p>' + esc(val) + '</p>' : renderVal(val)) + '</div></div>';
            }
            html += '</div>';
            return html;
        },
        get formattedReportHtml() { return this.formatReportHtml(); },
        searchPatientsDebounced() {
            var self = this;
            if (self.patientSearchTimer) clearTimeout(self.patientSearchTimer);
            self.patientSearchTimer = setTimeout(function() {
                if (self.patientQuery.trim().length < 1) { self.patientSuggestions = []; self.patientDropdownOpen = false; return; }
                fetch('{{ route("ai-assistant.patients") }}?q=' + encodeURIComponent(self.patientQuery.trim()), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) {
                        if (r.status === 401) { self.patientSuggestions = []; return { patients: [] }; }
                        return r.json();
                    })
                    .then(function(d) {
                        var list = (d && d.patients) ? d.patients : [];
                        self.patientSuggestions = list;
                        self.patientDropdownOpen = true;
                        if (list.length === 1) self.selectPatient(list[0].id, list[0].label);
                    });
            }, 300);
        },
        selectPatient(id, label) {
            this.patientId = String(id);
            this.selectedPatientLabel = label;
            this.patientQuery = '';
            this.patientSuggestions = [];
            this.patientDropdownOpen = false;
        },
        clearPatient() {
            this.patientId = '';
            this.selectedPatientLabel = '';
            this.patientQuery = '';
            this.patientSuggestions = [];
        },
        async loadConfig() {
            if (typeof window.__aiAssistantPatient !== 'undefined' && window.__aiAssistantPatient) {
                this.patientId = String(window.__aiAssistantPatient.id);
                this.selectedPatientLabel = window.__aiAssistantPatient.label || ('Patient #' + window.__aiAssistantPatient.id);
            } else if (typeof window.__aiAssistantPatientId !== 'undefined') {
                this.patientId = String(window.__aiAssistantPatientId);
                this.selectedPatientLabel = 'Patient #' + window.__aiAssistantPatientId;
            }
            try {
                const r = await fetch('{{ route("ai-assistant.config") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.enabled = d.enabled === true;
                if (!this.enabled) this.error = d.message || 'Assistant IA non disponible (config ou clé API).';
            } catch (e) { this.enabled = false; this.error = 'Impossible de charger la config.'; }
        },
        async send() {
            this.error = ''; this.responseText = ''; this.responseJson = null; this.loading = true;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const body = new FormData();
            body.append('_token', csrf);
            if (this.patientId) body.append('patient_id', this.patientId);
            let url = ''; let ok = true;
            if (this.mode === 'protocol') { url = '{{ route("ai-assistant.protocol") }}'; if (this.poleHint) body.append('pole', this.poleHint); }
            else if (this.mode === 'consultation') { url = '{{ route("ai-assistant.consultation") }}'; body.append('symptomes', this.consultation.symptomes); body.append('examen', this.consultation.examen); body.append('antecedents', this.consultation.antecedents); }
            else if (this.mode === 'ask') { url = '{{ route("ai-assistant.ask") }}'; body.append('question', this.question || 'Question'); if (!this.question) ok = false; }
            else if (this.mode === 'rapport_cabinet') { url = '{{ route("ai-assistant.rapport-cabinet") }}'; }
            if (!ok) { this.error = 'Remplissez les champs requis.'; this.loading = false; return; }
            try {
                const r = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body });
                const d = await r.json();
                if (r.status === 401) { this.error = 'Session expirée. Rechargez la page ou reconnectez-vous.'; this.loading = false; return; }
                if (d.error) this.error = d.error;
                if (d.text) this.responseText = d.text;
                if (d.json) this.responseJson = d.json;
            } catch (e) { this.error = 'Erreur réseau.'; }
            this.loading = false;
        }
    };
}
</script>
@endif
