@php
use Illuminate\Support\Str; 
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <!-- Logo (Imagen) -->
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                        
                        <!-- Texto (Visible SOLO aquí adentro) -->
                        <div class="flex flex-col leading-tight">
                            <span class="font-extrabold text-xl text-blue-900 tracking-wide">PPP-FIIS</span>
                            <span class="text-[0.6rem] font-bold text-slate-500 uppercase tracking-wider">Sistema de Prácticas</span>
                        </div>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- ============ INICIO: NUEVO ENLACE DE NOTIFICACIONES (Escritorio) ============ --}}
                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.index')">
                        {{ __('Notificaciones') }}
                    </x-nav-link>
                    {{-- ============ FIN: NUEVO ENLACE DE NOTIFICACIONES (Escritorio) ============ --}}

                    @can('isCPPP')
                        <x-nav-link :href="route('cppp.dashboard.index')" :active="request()->routeIs('cppp.*')">
                            {{ __('Dashboard CPPP') }}
                        </x-nav-link>
                    @endcan
                    @can('isAsesor')
                        <x-nav-link :href="route('asesor.dashboard.index')" :active="request()->routeIs('asesor.*')">
                            {{ __('Dashboard Asesor') }}
                        </x-nav-link>
                    @endcan
                    @can('isJurado')
                        <x-nav-link :href="route('jury.dashboard.index')" :active="request()->routeIs('jury.*')">
                            {{ __('Dashboard Jurado') }}
                        </x-nav-link>
                    @endcan
                    @can('isDecano')
                        <x-nav-link :href="route('decano.dashboard.index')" :active="request()->routeIs('decano.*')">
                            {{ __('Dashboard Decano') }}
                        </x-nav-link>
                    @endcan
                    </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="96">
                    <x-slot name="trigger">
                        <button class="relative inline-flex items-center p-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            {{-- Icono de Campana SVG --}}
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            
                            {{-- Contador de no leídas --}}
                            @if($unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-500 rounded-full">
                                    {{ $unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Notificaciones') }}
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @if($unreadNotifications->isEmpty())
                                <div class="px-4 py-3 text-sm text-gray-500">No hay notificaciones nuevas.</div>
                            @else
                                @foreach ($unreadNotifications as $notification)
                                    <a href="{{ route('notifications.read', $notification->id) }}" 
                                       class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out border-b border-gray-100 last:border-b-0">
                                        
                                        <p class="font-medium text-gray-900 truncate">
                                            {{ $notification->data['message'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                        
                        <div class="border-t border-gray-200"></div>
                        <x-dropdown-link :href="route('notifications.index')" class="text-center !text-sm !font-medium">
                            {{ __('Ver Todas las Notificaciones') }}
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ms-6">
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

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- ============ INICIO: NUEVO ENLACE DE NOTIFICACIONES (Móvil) ============ --}}
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.index')">
                {{ __('Notificaciones') }}
            </x-responsive-nav-link>
            {{-- ============ FIN: NUEVO ENLACE DE NOTIFICACIONES (Móvil) ============ --}}

            @can('isCPPP')
                <x-responsive-nav-link :href="route('cppp.dashboard.index')" :active="request()->routeIs('cppp.*')">
                    {{ __('Dashboard CPPP') }}
                </x-responsive-nav-link>
            @endcan
            @can('isAsesor')
                <x-responsive-nav-link :href="route('asesor.dashboard.index')" :active="request()->routeIs('asesor.*')">
                    {{ __('Dashboard Asesor') }}
                </x-responsive-nav-link>
            @endcan
            @can('isJurado')
                <x-responsive-nav-link :href="route('jury.dashboard.index')" :active="request()->routeIs('jury.*')">
                    {{ __('Dashboard Jurado') }}
                </x-responsive-nav-link>
            @endcan
            @can('isDecano')
                <x-responsive-nav-link :href="route('decano.dashboard.index')" :active="request()->routeIs('decano.*')">
                    {{ __('Dashboard Decano') }}
                </x-responsive-nav-link>
            @endcan
            </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">

                <div class="flex justify-between items-center px-4 py-2 text-xs text-gray-400">
                    <span>{{ __('Notificaciones') }}</span>
                    @if($unreadNotifications->count() > 0)
                        <span class="ms-1 inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-500 rounded-full">
                            {{ $unreadNotifications->count() }}
                        </span>
                    @endif
                </div>
                
                @if($unreadNotifications->isEmpty())
                    <div class="px-4 py-2 text-sm text-gray-500">No hay notificaciones nuevas.</div>
                @else
                    @foreach ($unreadNotifications as $notification)
                        <a href="{{ route('notifications.read', $notification->id) }}" 
                           class="block w-full ps-3 pe-4 py-3 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out border-b border-gray-100 last:border-b-0">
                            <p class="font-medium">{{ $notification->data['message'] }}</p>
                            <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                        </a>
                    @endforeach
                @endif
                
                <x-responsive-nav-link :href="route('notifications.index')">
                    {{ __('Ver Todas') }}
                </x-responsive-nav-link>
                <div class="border-t border-gray-200"></div>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

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