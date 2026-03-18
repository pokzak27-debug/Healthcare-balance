<nav x-data="{ open: false, cabinetOpen: false, facturationOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-8 w-auto text-indigo-600" />
                        <span class="hidden sm:inline text-sm font-semibold text-gray-800 tracking-tight">
                            Healthcare Balance
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('patients.index')" :active="request()->routeIs('patients.*')">
                        {{ __('Patients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')">
                        {{ __('Rendez-vous') }}
                    </x-nav-link>
                    <x-nav-link :href="route('search')" :active="request()->routeIs('search')">Recherche</x-nav-link>
                    <x-nav-link :href="route('cabinet-expenses.index')" :active="request()->routeIs('cabinet-expenses.*')">Besoins cabinet</x-nav-link>
                    @if(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('doctor'))
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition
                                        {{ request()->routeIs(['rooms.*', 'machines.*', 'prestations.*', 'live-cabinet.*']) ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                        Cabinet
                                        <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('rooms.index')">Salles</x-dropdown-link>
                                    <x-dropdown-link :href="route('machines.index')">Machines</x-dropdown-link>
                                    <x-dropdown-link :href="route('prestations.index')">Prestations</x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                        </div>
                    @endif
                    @if(!Auth::user()->hasRole('staff'))
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition
                                    {{ request()->routeIs(['invoices.*', 'quotes.*', 'billing.*']) ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Facturation
                                    <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('billing.dashboard')">Dashboard facturation</x-dropdown-link>
                                <x-dropdown-link :href="route('invoices.index')">Factures</x-dropdown-link>
                                <x-dropdown-link :href="route('quotes.index')">Devis</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @else
                    <x-nav-link :href="route('billing.dashboard')" :active="request()->routeIs('billing.dashboard')">Facturation</x-nav-link>
                    @endif
                    @if(can_access_dossier())
                        <x-nav-link :href="route('reports.stats')" :active="request()->routeIs('reports.stats')">Répartition (mois / année)</x-nav-link>
                        <x-nav-link :href="route('reports.by-room-prestation')" :active="request()->routeIs('reports.by-room-prestation')">Rapport par prestation</x-nav-link>
                        <x-nav-link :href="route('ai-assistant.logs')" :active="request()->routeIs('ai-assistant.logs')">Logs Assistant IA</x-nav-link>
                    @endif
                    @if(Auth::user()->hasRole('super_admin'))
                        <x-nav-link :href="route('availability-slots.index')" :active="request()->routeIs('availability-slots.*')">
                            Créneaux
                        </x-nav-link>
                        <x-nav-link :href="route('poles.index')" :active="request()->routeIs('poles.*')">
                            Pôles
                        </x-nav-link>
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Utilisateurs
                        </x-nav-link>
                        <x-nav-link :href="route('practitioner-absences.index')" :active="request()->routeIs('practitioner-absences.*')">
                            Absences
                        </x-nav-link>
                        <x-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">
                            Logs
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- User menu -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('about')">
                            À propos
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('patients.index')" :active="request()->routeIs('patients.*')">
                {{ __('Patients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')">
                {{ __('Rendez-vous') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('search')" :active="request()->routeIs('search')">Recherche</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cabinet-expenses.index')" :active="request()->routeIs('cabinet-expenses.*')">Besoins cabinet</x-responsive-nav-link>
            @if(Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('doctor'))
                <div class="border-t border-gray-100 pt-2 mt-2">
                    <button @click="cabinetOpen = !cabinetOpen" class="w-full flex items-center justify-between px-4 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                        Cabinet (Salles, Machines, Prestations)
                        <svg class="h-4 w-4 transition" :class="cabinetOpen ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="cabinetOpen" x-cloak class="pl-4 space-y-0.5 mt-1">
                        <a href="{{ route('rooms.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('rooms.*') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Salles</a>
                        <a href="{{ route('machines.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('machines.*') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Machines</a>
                        <a href="{{ route('prestations.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('prestations.*') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Prestations</a>
                    </div>
                </div>
            @endif
            @if(Auth::user()->hasRole('staff'))
            <x-responsive-nav-link :href="route('billing.dashboard')" :active="request()->routeIs('billing.dashboard')">Facturation</x-responsive-nav-link>
            @else
            <div class="border-t border-gray-100 pt-2 mt-2">
                <button @click="facturationOpen = !facturationOpen" class="w-full flex items-center justify-between px-4 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                    Facturation
                    <svg class="h-4 w-4 transition" :class="facturationOpen ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="facturationOpen" x-cloak class="pl-4 space-y-0.5 mt-1">
                    <a href="{{ route('billing.dashboard') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('billing.dashboard') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Dashboard facturation</a>
                    <a href="{{ route('invoices.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('invoices.*') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Factures</a>
                    <a href="{{ route('quotes.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('quotes.*') ? 'text-indigo-700 font-medium' : 'text-gray-600' }}">Devis</a>
                </div>
            </div>
            @endif
            @if(can_access_dossier())
                <x-responsive-nav-link :href="route('ai-assistant.logs')" :active="request()->routeIs('ai-assistant.logs')">Logs Assistant IA</x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('availability-slots.index')" :active="request()->routeIs('availability-slots.*')">Créneaux</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('poles.index')" :active="request()->routeIs('poles.*')">Pôles</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">Utilisateurs</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">Logs</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @if(Auth::user()->role)
                    <div class="text-xs text-gray-400 mt-0.5">{{ Auth::user()->role->name }}</div>
                @endif
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
