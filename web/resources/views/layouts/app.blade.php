<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          lang: localStorage.getItem('lang') || 'en',
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          },
          toggleLang() {
              this.lang = this.lang === 'en' ? 'ur' : 'en';
              localStorage.setItem('lang', this.lang);
          }
      }"
      x-init="if (darkMode) document.documentElement.classList.add('dark');"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Fauri') }} - Intelligent Customer Triage</title>
        <meta name="description" content="AI customer-message triage tool for Pakistani SMBs. Urdu, Roman Urdu & English WhatsApp message classification and urgency prioritization.">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">

        <!-- Fonts (Plus Jakarta Sans & Noto Nastaliq Urdu) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-[#0B1219] dark:text-slate-100 min-h-screen selection:bg-brand-500 selection:text-white">
        <div class="min-h-screen flex flex-col">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white/80 dark:bg-[#06241A]/60 backdrop-blur-md border-b border-slate-200/80 dark:border-brand-900/60 sticky top-16 z-20">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Global Toast Container -->
            <div x-data="{ toasts: [] }"
                 @toast.window="toasts.push({ id: Date.now(), text: $event.detail.message, type: $event.detail.type || 'success' }); setTimeout(() => { toasts.shift(); }, 4000);"
                 class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
                <template x-for="t in toasts" :key="t.id">
                    <div class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all transform duration-300"
                         :class="{
                             'bg-emerald-600 text-white border-emerald-500': t.type === 'success',
                             'bg-rose-600 text-white border-rose-500': t.type === 'error',
                             'bg-amber-500 text-white border-amber-400': t.type === 'warning',
                             'bg-slate-800 text-white border-slate-700': t.type === 'info'
                         }">
                        <span x-text="t.text"></span>
                    </div>
                </template>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
