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
    <title>Fauri (فوری) - WhatsApp Message Triage for Pakistani Businesses</title>
    <meta name="description" content="Intelligent triage for WhatsApp messages & voice notes in Roman Urdu, Urdu, and English. Designed for Pakistani online sellers, clinics, academies, and real estate.">

    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-[#06241A] dark:text-slate-100 min-h-screen selection:bg-brand-500 selection:text-white transition-colors duration-200">

    <!-- Top Navigation -->
    <header class="border-b border-slate-200/80 dark:border-brand-900/40 bg-white/80 dark:bg-[#06241A]/80 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#06241A] to-[#0F4C3A] border border-brand-500/30 flex items-center justify-center shadow-md">
                    <svg viewBox="0 0 100 100" class="w-7 h-7" fill="none">
                        <circle cx="58" cy="38" r="10" stroke="#10B981" stroke-width="4.5" fill="none"/>
                        <circle cx="58" cy="22" r="4" fill="#F59E0B"/>
                        <path d="M58 48 C56 62 44 68 32 68 C24 68 20 64 22 60 C24 56 30 56 36 58 C42 60 50 58 52 46" 
                              stroke="#10B981" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                        <path d="M68 56 L78 56 L72 68 L82 68 L66 84 L70 72 L62 72 Z" fill="#F59E0B"/>
                    </svg>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Fauri</span>
                    <span class="font-bold text-xl text-brand-600 dark:text-brand-400 font-nastaliq" dir="rtl">فوری</span>
                </div>
            </a>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <a href="#how-it-works" class="hover:text-brand-600 dark:hover:text-brand-400 transition">How It Works</a>
                <a href="#features" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Features</a>
                <a href="#pricing" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Pricing (PKR)</a>
                <a href="#faq" class="hover:text-brand-600 dark:hover:text-brand-400 transition">FAQ</a>
            </div>

            <!-- Controls & Auth Actions -->
            <div class="flex items-center gap-3">
                <!-- Language Toggle -->
                <button type="button" @click="toggleLang()" class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span x-text="lang === 'en' ? 'اردو' : 'EN'"></span>
                </button>

                <!-- Theme Toggle -->
                <button type="button" @click="toggleTheme()" class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span x-show="darkMode">☀️</span>
                    <span x-show="!darkMode">🌙</span>
                </button>

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs sm:text-sm shadow-sm transition">
                            Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-700 hover:to-brand-600 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-500/20 transition">
                            Get Started Free
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-8">
            <!-- Pakistani SMB Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100/80 dark:bg-brand-950/80 border border-brand-500/30 text-xs font-bold text-brand-800 dark:text-brand-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span x-show="lang === 'en'">Built Specifically for Pakistani WhatsApp Commerce & Clinics</span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-sm">پاکستانی کاروبار اور کلینکس کے لیے خصوصی ڈیزائن</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 dark:text-white max-w-4xl mx-auto leading-tight">
                <span x-show="lang === 'en'">
                    Stop Drowning in WhatsApp. <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 via-emerald-500 to-amber-500">
                        Urgent Customer Triage in Seconds.
                    </span>
                </span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-4xl sm:text-6xl leading-relaxed">
                    واٹس ایپ کے ہجوم میں اہم گاہک مت کھوئیں۔ <br/>
                    <span class="text-brand-500">فوری جواب، مطمئن گاہک۔</span>
                </span>
            </h1>

            <p class="text-base sm:text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                <span x-show="lang === 'en'">
                    Fauri automatically reads WhatsApp voice notes and text in <strong>Roman Urdu, Urdu, and English</strong>. It flags angry refunds and urgent medical requests so you never lose high-value business.
                </span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-xl leading-relaxed">
                    رومن اردو، نستعلیق اردو اور آڈیو پیغامات کی فوری درجہ بندی۔ شکایات اور ہنگامی پیغامات خود بخود سرفہرست۔
                </span>
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-brand-600 via-emerald-600 to-brand-500 hover:from-brand-700 hover:to-brand-600 text-white font-extrabold text-base shadow-xl shadow-brand-600/25 transition transform hover:-translate-y-0.5">
                    Start Triaging Messages Free &rarr;
                </a>
                <a href="#demo" class="w-full sm:w-auto px-6 py-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-slate-200 font-bold text-base hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    See Live Demo Simulator
                </a>
            </div>

            <!-- Trust proof numbers -->
            <div class="pt-8 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto border-t border-slate-200 dark:border-brand-900/40 text-left">
                <div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">600+</div>
                    <div class="text-xs text-slate-500">Urdu/Roman Urdu Trained</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">100% CPU</div>
                    <div class="text-xs text-slate-500">Free Tier Friendly</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-amber-500">Zero LLM</div>
                    <div class="text-xs text-slate-500">Zero API Costs</div>
                </div>
                <div>
                    <div class="text-2xl font-black text-brand-600 dark:text-brand-400">&lt; 200ms</div>
                    <div class="text-xs text-slate-500">Classification Speed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTERACTIVE DEMO SIMULATOR (ID: demo) -->
    <section id="demo" class="py-16 bg-white dark:bg-[#03140E] border-y border-slate-200 dark:border-brand-900/40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Interactive Triage Simulator</span>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white">Test Real Pakistani Customer Messages</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Click any preset below to see how Fauri extracts intent and urgency.</p>
            </div>

            <div x-data="{
                activePreset: 0,
                presets: [
                    {
                        text: 'bhai please emergency hai kal meri shadi ka function hai parcel abhi tak dispatch nahi hua! Rider ko bolo foran deliver kare',
                        category: 'urgent',
                        urgency: 'high',
                        summary: 'CRITICAL / URGENT: Wedding tomorrow, bridal parcel delayed in transit',
                        lang: 'Roman Urdu'
                    },
                    {
                        text: 'مجھے یہ کالا سوٹ درمیانے سائز میں آرڈر کرنا ہے، کیش آن ڈلیوری بھیج دیں لاہور کے پتے پر',
                        category: 'order',
                        urgency: 'medium',
                        summary: 'New Order Request: کالا سوٹ درمیانہ سائز، لاہور کیش آن ڈلیوری',
                        lang: 'Urdu Script'
                    },
                    {
                        text: 'aap k rider ne double charge kia hai aur suit phatta hua nikla hai, mujhe exchange nahi direct refund chahiye',
                        category: 'refund',
                        urgency: 'medium',
                        summary: 'Refund/Return Request: Defective torn suit and rider double charge dispute',
                        lang: 'Roman Urdu'
                    },
                    {
                        text: 'AOA, Dr Asim clinic me kis time beth'te hain aur consultation fee kitni hai?',
                        category: 'inquiry',
                        urgency: 'low',
                        summary: 'Product/Service Inquiry: Clinic doctor timings and consultation fee schedule',
                        lang: 'Roman Urdu'
                    }
                ]
            }" class="rounded-3xl border border-slate-200 dark:border-brand-900/60 bg-slate-50 dark:bg-[#06241A] p-6 shadow-xl space-y-6">

                <!-- Preset Buttons -->
                <div class="flex flex-wrap gap-2 justify-center">
                    <template x-for="(p, idx) in presets" :key="idx">
                        <button @click="activePreset = idx" 
                                class="px-4 py-2 rounded-xl text-xs font-bold border transition"
                                :class="activePreset === idx ? 'bg-brand-600 text-white border-brand-500 shadow-md' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100'">
                            <span x-text="'Sample ' + (idx + 1) + ': ' + p.category.toUpperCase()"></span>
                        </button>
                    </template>
                </div>

                <!-- Simulation Visual Card -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0B1219] border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Incoming WhatsApp Message (<span x-text="presets[activePreset].lang"></span>)
                        </span>
                        <span>Auto-detected</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm font-medium leading-relaxed"
                         x-text="presets[activePreset].text"></div>

                    <!-- Extraction results -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60">
                            <div class="text-[11px] font-bold text-slate-400 uppercase">Category Detected</div>
                            <div class="mt-1 font-extrabold text-sm uppercase text-brand-600 dark:text-brand-400" x-text="presets[activePreset].category"></div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60">
                            <div class="text-[11px] font-bold text-slate-400 uppercase">Urgency Score</div>
                            <div class="mt-1 font-extrabold text-sm uppercase" 
                                 :class="presets[activePreset].urgency === 'high' ? 'text-rose-600 font-black' : 'text-amber-500'" 
                                 x-text="presets[activePreset].urgency"></div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60">
                            <div class="text-[11px] font-bold text-slate-400 uppercase">AI Rule Summary</div>
                            <div class="mt-1 font-semibold text-xs text-slate-700 dark:text-slate-300 line-clamp-2" x-text="presets[activePreset].summary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS SECTION -->
    <section id="how-it-works" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Streamlined Workflow</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">How Fauri Triages Your Chaos</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 relative shadow-sm hover:border-brand-500/40 transition">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 font-black text-xl flex items-center justify-center mb-6">
                        01
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Ingest Any WhatsApp Input</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Paste customer texts, drag-and-drop exported WhatsApp <code>.txt</code> chat transcripts, or upload audio voice notes in WhatsApp <code>.opus/.ogg</code> formats.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 relative shadow-sm hover:border-brand-500/40 transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 font-black text-xl flex items-center justify-center mb-6">
                        02
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Multilingual Triage Engine</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Multilingual MiniLM embeddings and faster-whisper STT parse Roman Urdu slang and Urdu Nastaliq. Urgency rules prioritize critical wedding deadlines and clinic emergencies.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 relative shadow-sm hover:border-brand-500/40 transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 font-black text-xl flex items-center justify-center mb-6">
                        03
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Clean Control Room</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Owners see an organized triage board with high-urgency alerts on top. Filter by orders or refunds, execute bulk status changes, and inspect customer history in 1 click.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING SECTION (PKR) -->
    <section id="pricing" class="py-20 bg-slate-100/60 dark:bg-[#03140E] border-y border-slate-200 dark:border-brand-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Transparent Pricing</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">Affordable Plans in Pakistani Rupees</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">No international credit card required. Pay with Nayapay, SadaPay, EasyPaisa, or JazzCash.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Free Plan -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A]/60 border border-slate-200 dark:border-brand-900/40 shadow-sm space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            Starter / Free
                        </span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900 dark:text-white">PKR 0</span>
                            <span class="text-xs text-slate-400">/ month</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ideal for solo Instagram sellers and boutique clinics starting out.</p>
                        <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <li class="flex items-center gap-2">✓ Up to 100 messages / month</li>
                            <li class="flex items-center gap-2">✓ Roman Urdu & Urdu classification</li>
                            <li class="flex items-center gap-2">✓ Up to 5 voice notes / month</li>
                            <li class="flex items-center gap-2">✓ WhatsApp chat export parser</li>
                            <li class="flex items-center gap-2">✓ Standard inbox filtering</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-center font-bold text-xs text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Get Started Free
                    </a>
                </div>

                <!-- Pro Plan (Highlighted) -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A] border-2 border-brand-500 shadow-xl space-y-6 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-brand-500 text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-bl-xl tracking-wider">
                        Most Popular
                    </div>
                    <div class="space-y-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-100 text-brand-800 dark:bg-brand-950 dark:text-brand-300">
                            Professional Store
                        </span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900 dark:text-white">PKR 4,500</span>
                            <span class="text-xs text-slate-400">/ month</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">For fast-growing e-commerce brands, busy clinics, and real estate agencies.</p>
                        <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <li class="flex items-center gap-2 font-semibold">✓ Unlimited text messages triage</li>
                            <li class="flex items-center gap-2 font-semibold">✓ Unlimited voice notes transcription</li>
                            <li class="flex items-center gap-2 font-semibold">✓ Real-time high-urgency SMS / alerts</li>
                            <li class="flex items-center gap-2 font-semibold">✓ 1-Click human training corrections</li>
                            <li class="flex items-center gap-2 font-semibold">✓ Customer historical drilldown</li>
                            <li class="flex items-center gap-2 font-semibold">✓ Up to 5 team members</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-700 hover:to-brand-600 text-center font-bold text-xs text-white shadow-md shadow-brand-500/30 transition">
                        Start 14-Day Pro Trial &rarr;
                    </a>
                </div>

                <!-- Enterprise / Clinic Plan -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#06241A]/60 border border-slate-200 dark:border-brand-900/40 shadow-sm space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            Enterprise / Academy
                        </span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-slate-900 dark:text-white">PKR 12,000</span>
                            <span class="text-xs text-slate-400">/ month</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">For hospital chains, academies, and multi-branch retail operations.</p>
                        <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <li class="flex items-center gap-2">✓ Everything in Pro</li>
                            <li class="flex items-center gap-2">✓ Official WhatsApp Business Cloud API</li>
                            <li class="flex items-center gap-2">✓ Custom domain & branding</li>
                            <li class="flex items-center gap-2">✓ Dedicated account manager in Lahore</li>
                            <li class="flex items-center gap-2">✓ 99.9% uptime SLA</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-center font-bold text-xs text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Contact Enterprise Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section id="faq" class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="text-center space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Frequently Answered Questions</span>
                <h2 class="text-3xl font-black text-slate-900 dark:text-white">Got Questions About Pakistani WhatsApp Triage?</h2>
            </div>

            <div class="space-y-4" x-data="{ openFaq: null }">
                <!-- FAQ 1 -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40">
                    <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full flex justify-between items-center text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Does Fauri understand Roman Urdu slang and typos (e.g., 'kya / kia / kiya', 'nai / nhi')?</span>
                        <span class="text-brand-500 font-bold text-xl" x-text="openFaq === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Yes! Fauri was trained specifically on Pakistani commerce conversations. Its multilingual embedding model and heuristic rule engine account for spelling variations across Roman Urdu, phonetic typos, mixed English sentences, and native Urdu Nastaliq.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40">
                    <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full flex justify-between items-center text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Can it transcribe WhatsApp voice notes sent by customers?</span>
                        <span class="text-brand-500 font-bold text-xl" x-text="openFaq === 2 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Absolutely. Customers frequently send voice notes instead of typing. Fauri accepts WhatsApp <code>.opus</code>, <code>.ogg</code>, <code>.m4a</code>, and <code>.mp3</code> files, converts them via FFmpeg, and transcribes them using OpenAI Faster-Whisper on CPU with Urdu auto-detection.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40">
                    <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full flex justify-between items-center text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Is my customer conversation data isolated and safe?</span>
                        <span class="text-brand-500 font-bold text-xl" x-text="openFaq === 3 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Yes. Each business account is strictly multi-tenant isolated. Furthermore, Fauri runs CPU-based local AI models without sending your customers' confidential messages or phone numbers to external proprietary third-party APIs.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40">
                    <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full flex justify-between items-center text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>What happens if the AI service temporarily restarts?</span>
                        <span class="text-brand-500 font-bold text-xl" x-text="openFaq === 4 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 4" x-collapse class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Fauri includes automatic fallback handling and database background queues. Messages are safely stored as "unclassified" and automatically retried with exponential backoff so nothing is ever lost.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-slate-200 dark:border-brand-900/40 bg-white dark:bg-[#06241A] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#06241A] to-[#0F4C3A] border border-brand-500/30 flex items-center justify-center">
                    <svg viewBox="0 0 100 100" class="w-5 h-5" fill="none">
                        <circle cx="58" cy="38" r="10" stroke="#10B981" stroke-width="4.5" fill="none"/>
                        <circle cx="58" cy="22" r="4" fill="#F59E0B"/>
                        <path d="M58 48 C56 62 44 68 32 68 C24 68 20 64 22 60 C24 56 30 56 36 58 C42 60 50 58 52 46" 
                              stroke="#10B981" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <span class="font-extrabold text-lg text-slate-900 dark:text-white">Fauri (فوری)</span>
                <span class="text-xs text-slate-400">&bull; Intelligent Message Triage</span>
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400 text-center md:text-right">
                <p>&copy; 2026 Fauri Technologies. Proudly built for Pakistani commerce.</p>
                <p class="mt-1">Lahore &bull; Karachi &bull; Islamabad</p>
            </div>
        </div>
    </footer>

</body>
</html>
