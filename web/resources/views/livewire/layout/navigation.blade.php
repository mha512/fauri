<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white/90 dark:bg-[#06241A]/90 backdrop-blur-md border-b border-slate-200 dark:border-brand-900/50 sticky top-0 z-30">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="transition transform hover:scale-105">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="text-sm font-semibold">
                        <span x-show="lang === 'en'">Dashboard</span>
                        <span x-show="lang === 'ur'" class="font-nastaliq text-base">ڈیش بورڈ</span>
                    </x-nav-link>
                    <x-nav-link :href="route('customers')" :active="request()->routeIs('customers*')" wire:navigate class="text-sm font-semibold">
                        <span x-show="lang === 'en'">Customers</span>
                        <span x-show="lang === 'ur'" class="font-nastaliq text-base">گاہک</span>
                    </x-nav-link>
                </div>
            </div>

            <!-- Header Controls: Language, Theme & Profile -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <!-- Active Business Badge -->
                @if(auth()->check())
                    <div class="px-3 py-1 bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800/60 rounded-full flex items-center gap-2 text-xs font-semibold text-brand-800 dark:text-brand-300">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                        <span>{{ auth()->user()->currentBusiness()->name }}</span>
                    </div>
                @endif

                <!-- Language Switcher Toggle -->
                <button type="button" 
                        @click="toggleLang()"
                        class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-xs font-bold transition flex items-center gap-1.5"
                        title="Switch Language">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                    <span x-text="lang === 'en' ? 'اردو' : 'EN'" class="uppercase"></span>
                </button>

                <!-- Dark / Light Mode Switcher -->
                <button type="button" 
                        @click="toggleTheme()"
                        class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 transition"
                        title="Toggle Dark/Light Mode">
                    <!-- Sun (when dark) -->
                    <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon (when light) -->
                    <svg x-show="!darkMode" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition ease-in-out duration-150">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                            <svg class="ms-1.5 fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden gap-2">
                <button type="button" @click="toggleTheme()" class="p-2 text-slate-600 dark:text-slate-300">
                    <span x-show="darkMode">☀️</span>
                    <span x-show="!darkMode">🌙</span>
                </button>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#06241A] px-4 pt-2 pb-4 space-y-2">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('customers')" :active="request()->routeIs('customers*')" wire:navigate>
            {{ __('Customers') }}
        </x-responsive-nav-link>

        <div class="pt-4 pb-1 border-t border-slate-200 dark:border-slate-700">
            <div class="px-4 font-medium text-base text-slate-800 dark:text-slate-200">{{ auth()->user()->name }}</div>
            <div class="px-4 font-medium text-sm text-slate-500">{{ auth()->user()->email }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
