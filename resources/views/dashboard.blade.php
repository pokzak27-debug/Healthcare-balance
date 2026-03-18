<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Healthcare Balance') }} — {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-8 text-gray-600">
                Bonjour <strong>{{ Auth::user()->name }}</strong>, vous êtes connecté(e) en tant que <strong>{{ Auth::user()->role?->name ?? 'utilisateur' }}</strong>.
                @if(config('webpush.vapid_public'))
                    <button type="button" id="btn-push-subscribe" class="ml-4 px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">
                        Activer les notifications
                    </button>
                @endif
            </p>

            {{-- Accès rapide : Cabinet live + Calendrier + Facturation (assistants seulement) + Logs (super admin) --}}
            <section class="mb-10">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Accès rapide</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 {{ Auth::user()->hasRole('staff') ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4">
                    <a href="{{ route('live-cabinet.index') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-amber-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Cabinet live</p>
                            <p class="text-sm text-gray-500 truncate">Vue en direct</p>
                        </div>
                    </a>
                    <a href="{{ route('appointments.calendar') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-emerald-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Calendrier</p>
                            <p class="text-sm text-gray-500 truncate">Rendez-vous</p>
                        </div>
                    </a>
                    @if(can_access_dossier())
                    <a href="{{ route('reports.stats') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-violet-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-violet-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Répartition</p>
                            <p class="text-sm text-gray-500 truncate">Par mois / année</p>
                        </div>
                    </a>
                    @endif
                    @if(Auth::user()->hasRole('staff'))
                    <a href="{{ route('billing.dashboard') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-teal-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Facturation</p>
                            <p class="text-sm text-gray-500 truncate">Devis & factures · PDF / Word</p>
                        </div>
                    </a>
                    <a href="{{ route('cabinet-expenses.index') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-slate-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Besoins cabinet</p>
                            <p class="text-sm text-gray-500 truncate">Fournitures & charges</p>
                        </div>
                    </a>
                    @endif
                    @if(Auth::user()->hasRole('super_admin'))
                    <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 p-5 min-h-[88px] bg-white shadow-sm sm:rounded-lg hover:shadow-md transition border-l-4 border-red-500">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.54 4.54l14.92 14.92M12 2a10 10 0 1010 10A10.011 10.011 0 0012 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">Logs activité</p>
                            <p class="text-sm text-gray-500 truncate">Traçabilité complète (Super Admin)</p>
                        </div>
                    </a>
                    @endif
                </div>
            </section>

            <section class="mb-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @if(!Auth::user()->hasRole('staff'))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm font-medium text-gray-500">Patients</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totalPatients) }}</p>
                        <a href="{{ route('patients.index') }}" class="mt-2 text-sm text-indigo-600 hover:underline">Voir la liste</a>
                    </div>
                    @endif
                    <a href="{{ route('appointments.calendar', ['view' => 'day', 'date' => now()->toDateString()]) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                        <p class="text-sm font-medium text-gray-500">Aujourd'hui</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $todayAppointments->count() }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ now()->translatedFormat('l d F Y') }}</p>
                    </a>
                    @if(can_access_dossier())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm font-medium text-gray-500">CA ce mois</p>
                        <p class="mt-1 text-2xl font-semibold text-emerald-600">{{ format_dh($caThisMonth) }}</p>
                        @if(isset($caEvolution) && $caEvolution != 0)
                            <p class="mt-1 text-xs {{ $caEvolution > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $caEvolution > 0 ? '+' : '' }}{{ $caEvolution }}% vs mois dernier
                            </p>
                        @endif
                        <a href="{{ route('invoices.ca-by-practitioner') }}" class="mt-2 text-sm text-indigo-600 hover:underline">Voir le CA</a>
                    </div>
                    @endif
                    <a href="{{ route('cabinet-expenses.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                        <p class="text-sm font-medium text-gray-500">Besoins cabinet (ce mois)</p>
                        <p class="mt-1 text-2xl font-semibold text-teal-600">{{ format_dh($suppliesThisMonth ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Fournitures : huiles, bandages…</p>
                        <span class="mt-2 text-sm text-indigo-600 hover:underline">Voir / Saisir</span>
                    </a>
                    <a href="{{ route('cabinet-expenses.index') }}?type=charge" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                        <p class="text-sm font-medium text-gray-500">Charges à payer</p>
                        <p class="mt-1 text-2xl font-semibold text-amber-600">{{ format_dh($chargesToPay ?? 0) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Loyer, électricité…</p>
                        <span class="mt-2 text-sm text-indigo-600 hover:underline">Voir / Saisir</span>
                    </a>
                </div>
            </section>

            {{-- Chat flottant type WhatsApp / Facebook --}}
            @if(Auth::user()->hasRole('staff') || can_access_dossier())
            <div class="fixed bottom-6 right-6 z-50" x-data="{
                open: false,
                messages: [],
                body: '',
                loading: false,
                lastReadId: parseInt(localStorage.getItem('chatLastReadId') || 0),
                get unreadCount() {
                    return this.messages.filter(m => !m.is_me && m.id > this.lastReadId).length;
                },
                init() {
                    this.fetchMessages();
                    setInterval(() => this.fetchMessages(), 5000);
                },
                async fetchMessages() {
                    try {
                        const r = await fetch('{{ route('chat.index') }}', { headers: { 'Accept': 'application/json' } });
                        const d = await r.json();
                        this.messages = d.messages || [];
                        if (this.open) {
                            const maxId = Math.max(...this.messages.map(m => m.id), 0);
                            this.lastReadId = maxId;
                            localStorage.setItem('chatLastReadId', maxId);
                        }
                        this.$nextTick(() => {
                            const el = this.$refs.messagesList;
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    } catch(e) {}
                },
                async send() {
                    if (!this.body.trim()) return;
                    this.loading = true;
                    try {
                        const fd = new FormData();
                        fd.append('body', this.body.trim());
                        fd.append('_token', '{{ csrf_token() }}');
                        const r = await fetch('{{ route('chat.store') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: fd
                        });
                        const d = await r.json();
                        if (d.message) this.messages.push(d.message);
                        this.body = '';
                        this.$nextTick(() => {
                            const el = this.$refs.messagesList;
                            if (el) el.scrollTop = el.scrollHeight;
                        });
                    } catch(e) {}
                    this.loading = false;
                },
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.fetchMessages();
                }
            }">
                {{-- Bouton flottant avec badge nombre de messages non lus --}}
                <button @click="toggle()" class="relative w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg flex items-center justify-center transition">
                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.77.46 3.44 1.27 4.91L2 22l5.27-1.37C8.56 21.54 10.23 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.38 0-2.63-.35-3.76-.97l-.27-.15-2.78.72.74-2.75-.18-.28C4.96 16.23 4 14.19 4 12c0-4.42 3.58-8 8-8s8 3.58 8 8-3.58 8-8 8z"/></svg>
                    <span x-show="unreadCount > 0" x-cloak x-text="unreadCount > 99 ? '99+' : unreadCount" class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold"></span>
                </button>
                {{-- Fenêtre chat (style WhatsApp) --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak class="absolute bottom-16 right-0 w-[360px] h-[480px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
                    <div class="px-4 py-3 bg-emerald-600 text-white flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm">Docteur — Staff</h3>
                                <p class="text-xs text-emerald-100">En ligne</p>
                            </div>
                        </div>
                        <button @click="open = false" class="p-1 hover:bg-white/20 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div ref="messagesList" x-ref="messagesList" class="flex-1 overflow-y-auto p-3 space-y-2" style="background-color: #e5ddd5;">
                        <template x-for="m in messages" :key="m.id">
                            <div class="flex flex-col" :class="m.is_me ? 'items-end' : 'items-start'">
                                <span x-show="!m.is_me" class="text-[10px] text-gray-600 mb-0.5 px-1" x-text="m.user_name"></span>
                                <div class="max-w-[85%] rounded-lg px-3 py-2 shadow-sm" :class="m.is_me ? 'bg-[#dcf8c6] rounded-tr-none' : 'bg-white rounded-tl-none shadow'">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap break-words" x-text="m.body"></p>
                                    <p class="text-[10px] text-gray-500 mt-1 text-right" x-text="m.created_at"></p>
                                </div>
                            </div>
                        </template>
                        <p x-show="messages.length === 0 && !loading" class="text-gray-500 text-sm text-center py-12">Aucun message.<br><span class="text-xs">Écrivez ci-dessous pour démarrer.</span></p>
                    </div>
                    <form @submit.prevent="send()" class="p-2 bg-gray-100 shrink-0 flex gap-2 items-end">
                        <input type="text" x-model="body" placeholder="Écrire un message..." class="flex-1 rounded-lg border-0 bg-white px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" maxlength="5000" />
                        <button type="submit" :disabled="loading || !body.trim()" class="w-10 h-10 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Rendez-vous du jour --}}
            <section class="mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Bloc rendez-vous --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ !can_access_dossier() ? 'lg:col-span-2' : '' }}">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">Rendez-vous du jour</h3>
                    </div>
                    <div class="p-6">
                        @if($todayAppointments->isEmpty())
                            <p class="text-gray-500">Aucun rendez-vous aujourd'hui.</p>
                        @else
                            <ul class="divide-y divide-gray-200">
                                @foreach($todayAppointments as $apt)
                                    <li class="py-3 flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('appointments.show', $apt) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                                {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }} — {{ $apt->patient->fullName() }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ $apt->prestation?->name ?? $apt->pole?->name ?? '—' }}
                                                @if($apt->room)<span class="text-gray-400">· {{ $apt->room->name }}</span>@endif
                                                @if($apt->user) · {{ $apt->user->name }}@endif
                                            </p>
                                        </div>
                                        @php $statusLabels = ['scheduled' => 'Prévu', 'completed' => 'Honoré', 'cancelled' => 'Annulé', 'no_show' => 'Absent']; @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            @if($apt->status === 'scheduled') bg-blue-100 text-blue-800
                                            @elseif($apt->status === 'completed') bg-green-100 text-green-800
                                            @elseif($apt->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $statusLabels[$apt->status] ?? $apt->status }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                </div>
            </section>

            @if(config('webpush.vapid_public'))
            <script>
                document.getElementById('btn-push-subscribe')?.addEventListener('click', async function() {
                    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
                    try {
                        const reg = await navigator.serviceWorker.ready;
                        const perm = await Notification.requestPermission();
                        if (perm !== 'granted') return;
                        const sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(@json(config('webpush.vapid_public')))
                        });
                        const json = sub.toJSON();
                        await fetch('/push-subscribe', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
                            body: JSON.stringify({ endpoint: json.endpoint, keys: { p256dh: json.keys?.p256dh, auth: json.keys?.auth } })
                        });
                        this.textContent = 'Notifications activées';
                        this.disabled = true;
                    } catch (e) { console.warn('Push subscribe error', e); }
                });
                function urlBase64ToUint8Array(base64) {
                    const pad = (s) => s + '='.repeat((4 - s.length % 4) % 4);
                    const bin = atob(pad(base64).replace(/-/g,'+').replace(/_/g,'/'));
                    const arr = new Uint8Array(bin.length);
                    for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
                    return arr;
                }
            </script>
            @endif
            @if(Auth::user()->hasRole('super_admin'))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Administration</h3>
                    <p class="text-sm text-gray-500">Gestion des utilisateurs et paramètres du cabinet (à venir).</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
