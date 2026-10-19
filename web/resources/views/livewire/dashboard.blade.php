<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span x-show="lang === 'en'">Triage Dashboard</span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-3xl">پیغامات کا کنٹرول روم</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800 dark:bg-brand-900/60 dark:text-brand-300">
                    {{ $business->name }}
                </span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <span x-show="lang === 'en'">Automated classification and urgency triage for Urdu, Roman Urdu & WhatsApp messages.</span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-base">اردو، رومن اردو اور واٹس ایپ پیغامات کی فوری اور خودکار درجہ بندی۔</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="$refresh" 
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition" 
                    title="Refresh Feed">
                <svg wire:loading.class="animate-spin" class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>

            <!-- New Message CTA Button -->
            <button @click="$wire.set('showNewMessageModal', true)" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-700 hover:to-brand-600 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span x-show="lang === 'en'">New Message / Import</span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-base">نیا پیغام درج کریں</span>
            </button>
        </div>
    </div>

    <!-- Onboarding Checklist Widget (if messages < 25) -->
    @if($messages->total() <= 20)
    <div x-data="{ dismissed: false }" x-show="!dismissed" class="p-4 rounded-2xl bg-gradient-to-r from-brand-900/10 via-emerald-500/10 to-amber-500/10 border border-brand-500/20 dark:border-brand-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-500/20 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-lg flex-shrink-0">
                ✨
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Quick Start Checklist: Triage WhatsApp in 3 Steps</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">1. Paste customer text or upload voice note &bull; 2. Let Fauri detect urgency &bull; 3. Cycle status to In Progress / Resolved.</p>
            </div>
        </div>
        <button @click="dismissed = true" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Dismiss</button>
    </div>
    @endif

    <!-- 4 Stat Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Card 1: Today's Messages -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm relative overflow-hidden group hover:border-brand-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <span x-show="lang === 'en'">Today's Feed</span>
                    <span x-show="lang === 'ur'" class="font-nastaliq text-sm">آج کے پیغامات</span>
                </span>
                <span class="p-2 rounded-xl bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $totalToday }}</span>
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Active</span>
            </div>
            <div class="mt-2 text-xs text-slate-400">Total customer incoming queries</div>
        </div>

        <!-- Card 2: Urgent Attention -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm relative overflow-hidden group hover:border-rose-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                    <span x-show="lang === 'en'">Urgent Attention</span>
                    <span x-show="lang === 'ur'" class="font-nastaliq text-sm">فوری توجہ</span>
                </span>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ $totalUrgent }}</span>
                @if($totalUrgent > 0)
                    <span class="text-xs font-semibold text-rose-600 animate-pulse">Needs reply</span>
                @else
                    <span class="text-xs font-semibold text-slate-400">All clear</span>
                @endif
            </div>
            <div class="mt-2 text-xs text-slate-400">High priority disputes & emergencies</div>
        </div>

        <!-- Card 3: Pending Triage -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm relative overflow-hidden group hover:border-amber-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                    <span x-show="lang === 'en'">Pending</span>
                    <span x-show="lang === 'ur'" class="font-nastaliq text-sm">زیرِ غور</span>
                </span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $totalPending }}</span>
                <span class="text-xs font-semibold text-amber-600">Open queue</span>
            </div>
            <div class="mt-2 text-xs text-slate-400">Awaiting owner response</div>
        </div>

        <!-- Card 4: Resolved -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                    <span x-show="lang === 'en'">Resolved</span>
                    <span x-show="lang === 'ur'" class="font-nastaliq text-sm">مکمل شدہ</span>
                </span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $totalResolved }}</span>
                <span class="text-xs font-semibold text-emerald-600">Completed</span>
            </div>
            <div class="mt-2 text-xs text-slate-400">Customer requests satisfied</div>
        </div>
    </div>

    <!-- Urgent Top Alert Carousel (When High Urgency messages exist) -->
    @if($urgentList->isNotEmpty())
    <div class="rounded-2xl bg-gradient-to-br from-rose-950/40 via-slate-900/60 to-rose-950/20 border-2 border-rose-500/40 p-5 shadow-lg shadow-rose-950/20">
        <div class="flex items-center justify-between pb-3 border-b border-rose-500/20">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 rounded-lg bg-rose-600 text-white animate-bounce">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <h3 class="font-bold text-sm sm:text-base text-white tracking-wide flex items-center gap-2">
                    <span>Critical Messages Requiring Immediate Response</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-rose-600 text-white">{{ $urgentList->count() }}</span>
                </h3>
            </div>
            <span class="text-xs text-rose-300 font-medium">Sorted by High Urgency</span>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($urgentList as $um)
            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-rose-500/30 flex flex-col justify-between gap-3 hover:border-rose-400 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-400">{{ $um->customer?->name ?? 'Guest Customer' }}</span>
                        <span class="text-[11px] text-slate-400">{{ $um->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-white line-clamp-2">
                        {{ $um->summary ?: $um->body }}
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <button wire:click="viewMessage({{ $um->id }})" class="text-xs text-brand-400 hover:text-brand-300 font-medium flex items-center gap-1">
                        View Details &rarr;
                    </button>
                    <button wire:click="updateMessageStatus({{ $um->id }}, 'resolved')" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition">
                        ✓ Resolve
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Category Breakdown Chart & Filter Bar -->
    <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/40 border border-slate-200 dark:border-brand-900/40 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4">
            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                    <span x-show="lang === 'en'">Category Breakdown & Triage Spectrum</span>
                    <span x-show="lang === 'ur'" class="font-nastaliq text-xl">اقسام اور تناسب</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Click any category badge to filter the inbox below instantly.</p>
            </div>
            @if($selectedCategory !== 'all' || $selectedUrgency !== 'all' || $selectedStatus !== 'all' || $search)
                <button wire:click="resetFilters" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear Active Filters
                </button>
            @endif
        </div>

        <!-- Visual Multi-segment Progress Bar -->
        <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex shadow-inner">
            <div style="width: {{ $chartData['order']['percentage'] }}%" class="bg-emerald-500 transition-all duration-500" title="Orders: {{ $chartData['order']['count'] }} ({{ $chartData['order']['percentage'] }}%)"></div>
            <div style="width: {{ $chartData['complaint']['percentage'] }}%" class="bg-rose-500 transition-all duration-500" title="Complaints: {{ $chartData['complaint']['count'] }} ({{ $chartData['complaint']['percentage'] }}%)"></div>
            <div style="width: {{ $chartData['refund']['percentage'] }}%" class="bg-amber-500 transition-all duration-500" title="Refunds: {{ $chartData['refund']['count'] }} ({{ $chartData['refund']['percentage'] }}%)"></div>
            <div style="width: {{ $chartData['urgent']['percentage'] }}%" class="bg-red-600 transition-all duration-500" title="Urgent: {{ $chartData['urgent']['count'] }} ({{ $chartData['urgent']['percentage'] }}%)"></div>
            <div style="width: {{ $chartData['inquiry']['percentage'] }}%" class="bg-blue-500 transition-all duration-500" title="Inquiries: {{ $chartData['inquiry']['count'] }} ({{ $chartData['inquiry']['percentage'] }}%)"></div>
            <div style="width: {{ $chartData['other']['percentage'] }}%" class="bg-slate-400 transition-all duration-500" title="Other: {{ $chartData['other']['count'] }} ({{ $chartData['other']['percentage'] }}%)"></div>
        </div>

        <!-- Interactive Category Cards -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
            <!-- Order -->
            <button wire:click="filterByCategory('order')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'order' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-2 ring-emerald-500' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Orders
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['order']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['order']['percentage'] }}% of total</div>
            </button>

            <!-- Complaint -->
            <button wire:click="filterByCategory('complaint')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'complaint' ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-2 ring-rose-500' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Complaints
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['complaint']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['complaint']['percentage'] }}% of total</div>
            </button>

            <!-- Refund -->
            <button wire:click="filterByCategory('refund')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'refund' ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-2 ring-amber-500' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Refunds
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['refund']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['refund']['percentage'] }}% of total</div>
            </button>

            <!-- Urgent -->
            <button wire:click="filterByCategory('urgent')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'urgent' ? 'border-red-600 bg-red-50/70 dark:bg-red-950/40 ring-2 ring-red-600' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-red-600 dark:text-red-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span> Urgent
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['urgent']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['urgent']['percentage'] }}% of total</div>
            </button>

            <!-- Inquiry -->
            <button wire:click="filterByCategory('inquiry')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'inquiry' ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40 ring-2 ring-blue-500' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Inquiries
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['inquiry']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['inquiry']['percentage'] }}% of total</div>
            </button>

            <!-- Other -->
            <button wire:click="filterByCategory('other')" 
                    class="p-2.5 rounded-xl border text-left transition transform hover:scale-[1.02] {{ $selectedCategory === 'other' ? 'border-slate-500 bg-slate-100 dark:bg-slate-800/80 ring-2 ring-slate-400' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Other / ChitChat
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $chartData['other']['count'] }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">{{ $chartData['other']['percentage'] }}% of total</div>
            </button>
        </div>
    </div>

    <!-- Message Inbox Section -->
    <div class="space-y-4">
        <!-- Search, Filter & Bulk Actions Bar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search by customer name, phone, or message body in Urdu / English..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 placeholder-slate-400">
            </div>

            <!-- Filters Dropdowns -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Category Filter -->
                <select wire:model.live="selectedCategory" class="text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:ring-brand-500 focus:border-brand-500">
                    <option value="all">Category: All</option>
                    <option value="order">Orders</option>
                    <option value="complaint">Complaints</option>
                    <option value="refund">Refunds</option>
                    <option value="urgent">Urgent</option>
                    <option value="inquiry">Inquiries</option>
                    <option value="other">Other</option>
                    <option value="unclassified">Unclassified</option>
                </select>

                <!-- Urgency Filter -->
                <select wire:model.live="selectedUrgency" class="text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:ring-brand-500 focus:border-brand-500">
                    <option value="all">Urgency: All</option>
                    <option value="high">High (Critical)</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>

                <!-- Status Filter -->
                <select wire:model.live="selectedStatus" class="text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:ring-brand-500 focus:border-brand-500">
                    <option value="all">Status: All</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>

                <!-- Sort Order -->
                <select wire:model.live="sortOrder" class="text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 py-2 pl-3 pr-8 focus:ring-brand-500 focus:border-brand-500">
                    <option value="desc">Newest First</option>
                    <option value="asc">Oldest First</option>
                </select>
            </div>
        </div>

        <!-- Bulk Action Bar (when messages selected) -->
        @if(!empty($selectedMessages))
        <div class="p-3 rounded-xl bg-slate-900 text-white flex items-center justify-between gap-4 animate-fade-in shadow-md">
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-brand-600 text-white">
                    {{ count($selectedMessages) }} Selected
                </span>
                <span class="text-xs text-slate-300">Apply bulk action:</span>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkUpdateStatus('in_progress')" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-xs font-semibold transition">
                    Mark In Progress
                </button>
                <button wire:click="bulkUpdateStatus('resolved')" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold transition">
                    Mark Resolved
                </button>
                <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected messages?" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-xs font-semibold transition">
                    Delete
                </button>
            </div>
        </div>
        @endif

        <!-- Messages List Table / Cards -->
        <div class="rounded-2xl bg-white dark:bg-[#06241A]/50 border border-slate-200 dark:border-brand-900/40 shadow-sm overflow-hidden">
            <!-- Header Checkbox & Column Titles (Desktop) -->
            <div class="hidden md:flex items-center justify-between px-6 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 text-xs font-bold uppercase text-slate-400">
                <div class="flex items-center gap-4 w-1/4">
                    <input type="checkbox" wire:model.live="selectAll" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 bg-transparent">
                    <span>Customer & Source</span>
                </div>
                <div class="w-1/2">
                    <span>Message & AI Summary</span>
                </div>
                <div class="w-1/4 text-right">
                    <span>Category, Urgency & Status</span>
                </div>
            </div>

            <!-- Skeleton Loaders during Livewire updates -->
            <div wire:loading.delay class="divide-y divide-slate-100 dark:divide-slate-800 animate-pulse">
                @for($i = 0; $i < 4; $i++)
                <div class="p-4 sm:px-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3 md:w-1/4">
                        <div class="w-4 h-4 rounded bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        <div class="space-y-2 flex-1">
                            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-28"></div>
                            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-20"></div>
                        </div>
                    </div>
                    <div class="md:w-1/2 space-y-2">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-full"></div>
                    </div>
                    <div class="md:w-1/4 flex md:justify-end gap-2">
                        <div class="h-6 w-16 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                        <div class="h-6 w-14 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- List Rows -->
            <div wire:loading.remove.delay class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($messages as $msg)
                <div class="p-4 sm:px-6 hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $msg->urgency === 'high' && $msg->status !== 'resolved' ? 'border-l-4 border-l-rose-500 bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                    <!-- Customer Column -->
                    <div class="flex items-start gap-3 md:w-1/4">
                        <input type="checkbox" 
                               value="{{ $msg->id }}" 
                               wire:model.live="selectedMessages" 
                               class="mt-1 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 bg-transparent">
                        
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ $msg->customer?->name ?? 'WhatsApp Guest' }}</span>
                                @if($msg->source === 'voice')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-semibold" title="Voice Note">
                                        🎤 Voice
                                    </span>
                                @elseif($msg->source === 'whatsapp_export')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold" title="WhatsApp Export">
                                        💬 WhatsApp
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                                <span>{{ $msg->customer?->phone ?? 'Direct Paste' }}</span>
                                <span>&bull;</span>
                                <span>{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body & Summary Column -->
                    <div class="md:w-1/2 space-y-1.5">
                        <!-- Summary Capsule -->
                        @if($msg->summary)
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg inline-block border border-slate-200 dark:border-slate-700/60">
                                <span class="text-brand-600 dark:text-brand-400">Summary:</span> {{ $msg->summary }}
                            </div>
                        @endif

                        <!-- Raw Text with Urdu / RTL formatting -->
                        @if($msg->body === 'Transcription failed, retry' || str_contains($msg->body ?? '', 'Transcription failed'))
                            <div class="p-2.5 rounded-xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5">
                                    <span>⚠️</span>
                                    <span>Transcription failed, retry</span>
                                </span>
                                <button wire:click="retryAi({{ $msg->id }})" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold transition shadow-sm">
                                    Retry
                                </button>
                            </div>
                        @else
                            <div class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 {{ $msg->isUrdu() ? 'urdu-text text-base' : '' }}" dir="{{ $msg->direction() }}">
                                {{ $msg->body }}
                            </div>
                        @endif

                        <!-- Audio Player if Voice Note -->
                        @if($msg->source === 'voice' && $msg->audio_path)
                            <div class="mt-2">
                                <audio controls class="h-8 w-full max-w-sm rounded">
                                    <source src="{{ asset('storage/' . $msg->audio_path) }}" type="audio/mpeg">
                                    Your browser does not support audio element.
                                </audio>
                            </div>
                        @endif

                        <!-- AI Status / Confidence -->
                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            @if($msg->ai_status === 'completed')
                                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Confidence: {{ number_format(($msg->confidence ?? 0.85) * 100, 1) }}%
                                </span>
                            @elseif($msg->ai_status === 'processing' || $msg->ai_status === 'pending')
                                <span class="flex items-center gap-1 text-amber-500 animate-pulse">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    AI Triaging...
                                </span>
                            @elseif($msg->ai_status === 'failed')
                                <span class="flex items-center gap-1 text-rose-500 font-medium">
                                    @if($msg->source === 'voice' && ($msg->body === 'Transcription failed, retry' || str_contains($msg->body ?? '', 'Transcription failed')))
                                        <span class="text-rose-600 dark:text-rose-400 font-bold">Transcription failed</span>
                                    @else
                                        <span>⚠️ AI Offline</span>
                                    @endif
                                    <button wire:click="retryAi({{ $msg->id }})" class="underline hover:text-rose-400 ml-1 font-semibold">Retry</button>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Category, Urgency, Status & Actions Column -->
                    <div class="md:w-1/4 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            <!-- Category Badge -->
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
                                @if($msg->category === 'order') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300
                                @elseif($msg->category === 'complaint') bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300
                                @elseif($msg->category === 'refund') bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300
                                @elseif($msg->category === 'urgent') bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300
                                @elseif($msg->category === 'inquiry') bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300
                                @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300
                                @endif">
                                {{ $msg->category }}
                            </span>

                            <!-- Urgency Badge -->
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
                                @if($msg->urgency === 'high') bg-red-600 text-white animate-pulse
                                @elseif($msg->urgency === 'medium') bg-amber-500/20 text-amber-600 dark:text-amber-400
                                @else bg-emerald-500/20 text-emerald-600 dark:text-emerald-400
                                @endif">
                                {{ $msg->urgency }}
                            </span>
                        </div>

                        <!-- One-Click Quick Status Cycle Button -->
                        <div class="flex items-center gap-2 mt-1">
                            <button wire:click="quickCycleStatus({{ $msg->id }})" 
                                    class="px-3 py-1 rounded-xl text-xs font-bold border transition flex items-center gap-1.5
                                    @if($msg->status === 'resolved') bg-emerald-50 dark:bg-emerald-950/50 border-emerald-500/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100
                                    @elseif($msg->status === 'in_progress') bg-blue-50 dark:bg-blue-950/50 border-blue-500/40 text-blue-700 dark:text-blue-300 hover:bg-blue-100
                                    @else bg-amber-50 dark:bg-amber-950/50 border-amber-500/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100
                                    @endif"
                                    title="Click to cycle status: Pending -> In Progress -> Resolved">
                                <span class="w-2 h-2 rounded-full 
                                    @if($msg->status === 'resolved') bg-emerald-500
                                    @elseif($msg->status === 'in_progress') bg-blue-500
                                    @else bg-amber-500
                                    @endif"></span>
                                <span>{{ ucfirst(str_replace('_', ' ', $msg->status)) }}</span>
                            </button>

                            <!-- Detail Drawer Button -->
                            <button wire:click="viewMessage({{ $msg->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Full Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-brand-50 dark:bg-brand-950/60 flex items-center justify-center text-brand-600 dark:text-brand-400 text-2xl font-bold mb-3">
                        💬
                    </div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">No messages match your filters</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Try clearing search terms or resetting the category dropdown to see more customer queries.</p>
                    <button wire:click="resetFilters" class="mt-4 px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-semibold hover:bg-slate-800 transition">
                        Reset All Filters
                    </button>
                </div>
                @endforelse
            </div>

            <!-- Pagination Bar -->
            @if($messages->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                {{ $messages->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL: Add New Message / Import / Voice Note -->
    @if($showNewMessageModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#06241A] border border-slate-200 dark:border-brand-900/60 rounded-3xl max-w-lg w-full p-6 shadow-2xl relative animate-scale-up" @click.away="$wire.set('showNewMessageModal', false)">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Add New Customer Message</span>
                </h3>
                <button wire:click="$set('showNewMessageModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="mt-4 flex rounded-xl bg-slate-100 dark:bg-slate-900 p-1 text-xs font-bold">
                <button wire:click="$set('inputTab', 'paste')" 
                        class="flex-1 py-2 rounded-lg transition {{ $inputTab === 'paste' ? 'bg-white dark:bg-brand-950 text-brand-600 dark:text-brand-300 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    ✏️ Paste Text
                </button>
                <button wire:click="$set('inputTab', 'export')" 
                        class="flex-1 py-2 rounded-lg transition {{ $inputTab === 'export' ? 'bg-white dark:bg-brand-950 text-brand-600 dark:text-brand-300 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    📁 WhatsApp .txt
                </button>
                <button wire:click="$set('inputTab', 'voice')" 
                        class="flex-1 py-2 rounded-lg transition {{ $inputTab === 'voice' ? 'bg-white dark:bg-brand-950 text-brand-600 dark:text-brand-300 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    🎙️ Voice Note
                </button>
            </div>

            <!-- TAB 1: PASTE TEXT FORM -->
            @if($inputTab === 'paste')
            <form wire:submit="submitPaste" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Customer Name</label>
                        <input type="text" wire:model="pasteCustomerName" placeholder="e.g. Fatima Khan" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                        <input type="text" wire:model="pasteCustomerPhone" placeholder="03001234567" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Customer Message (Roman Urdu, Urdu, or English) *</label>
                    <textarea wire:model="pasteBody" rows="4" placeholder="Paste customer message here (e.g. 'bhai mera parcel kab tak dispatch hoga? tracking number share kr den')..." required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-3 focus:ring-brand-500"></textarea>
                    @error('pasteBody') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showNewMessageModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="submitPaste">Classify & Save</span>
                        <span wire:loading wire:target="submitPaste" class="animate-spin">🔄</span>
                    </button>
                </div>
            </form>
            @endif

            <!-- TAB 2: WHATSAPP EXPORT FORM -->
            @if($inputTab === 'export')
            <form wire:submit="submitExport" class="mt-4 space-y-4">
                <div x-data="{ isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">WhatsApp Chat Export File (.txt) *</label>
                    <input type="file" wire:model="exportFile" accept=".txt" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950 dark:file:text-brand-300">
                    <div x-show="isUploading" class="mt-2 space-y-1">
                        <div class="flex justify-between text-[11px] text-brand-600 dark:text-brand-400 font-semibold">
                            <span>Uploading chat transcript...</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-brand-600 h-1.5 rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Export chat from WhatsApp without media, upload the generated .txt file.</p>
                    @error('exportFile') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Sender Name in Chat (Optional)</label>
                    <input type="text" wire:model="exportOwnerName" placeholder="e.g. Ali (to exclude your own replies)" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-brand-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showNewMessageModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="submitExport">Import & Triage Chat</span>
                        <span wire:loading wire:target="submitExport" class="animate-spin">🔄 Importing...</span>
                    </button>
                </div>
            </form>
            @endif

            <!-- TAB 3: VOICE NOTE FORM -->
            @if($inputTab === 'voice')
            <form wire:submit="submitVoice" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Customer Name (Optional)</label>
                        <input type="text" wire:model="voiceCustomerName" placeholder="e.g. Voice Note Guest" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number (Optional)</label>
                        <input type="text" wire:model="voiceCustomerPhone" placeholder="03001234567" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-brand-500">
                    </div>
                </div>

                <!-- Language Selector (Urdu default, English, Auto) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Audio Language</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border text-xs font-semibold cursor-pointer transition {{ $voiceLanguage === 'ur' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <input type="radio" wire:model.live="voiceLanguage" value="ur" class="sr-only">
                            <span>🇵🇰 Urdu (Default)</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border text-xs font-semibold cursor-pointer transition {{ $voiceLanguage === 'en' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <input type="radio" wire:model.live="voiceLanguage" value="en" class="sr-only">
                            <span>🇬🇧 English</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border text-xs font-semibold cursor-pointer transition {{ $voiceLanguage === 'auto' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                            <input type="radio" wire:model.live="voiceLanguage" value="auto" class="sr-only">
                            <span>🌐 Auto</span>
                        </label>
                    </div>
                </div>

                <div x-data="{ isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Audio Voice Note (ogg, opus, mp3, m4a, wav) *</label>
                    <input type="file" wire:model="voiceFile" accept="audio/*,.ogg,.opus,.mp3,.m4a,.wav" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950 dark:file:text-brand-300">
                    <div x-show="isUploading" class="mt-2 space-y-1">
                        <div class="flex justify-between text-[11px] text-brand-600 dark:text-brand-400 font-semibold">
                            <span>Uploading voice note...</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-brand-600 to-emerald-500 h-2 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Converts via FFmpeg and transcribes automatically with Urdu support.</p>
                    @error('voiceFile') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showNewMessageModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="submitVoice">Upload & Transcribe</span>
                        <span wire:loading wire:target="submitVoice" class="animate-spin">🎙️ Transcribing...</span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
    @endif

    <!-- MODAL: Message Detail & 1-Click Category Correction -->
    @if($activeMessage)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#06241A] border border-slate-200 dark:border-brand-900/60 rounded-3xl max-w-xl w-full p-6 shadow-2xl relative animate-scale-up" @click.away="closeMessageModal">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Message #{{ $activeMessage->id }} Details</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-brand-100 text-brand-800 dark:bg-brand-950 dark:text-brand-300">
                            {{ $activeMessage->source }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $activeMessage->created_at->format('F d, Y \a\t h:i A') }}</p>
                </div>
                <button wire:click="closeMessageModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Customer Meta & Urgency Ribbon -->
            <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <div class="text-xs text-slate-400">Customer</div>
                    <div class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ $activeMessage->customer?->name ?? 'Guest' }} ({{ $activeMessage->customer?->phone ?? 'N/A' }})</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-400">Urgency Level</div>
                    <div class="font-bold text-sm uppercase {{ $activeMessage->urgency === 'high' ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-700 dark:text-slate-300' }}">
                        {{ $activeMessage->urgency }}
                    </div>
                </div>
            </div>

            <!-- Full Message Body -->
            <div class="mt-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Message Content</label>
                @if($activeMessage->body === 'Transcription failed, retry' || str_contains($activeMessage->body ?? '', 'Transcription failed'))
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="font-bold flex items-center gap-1.5">
                                <span>⚠️</span>
                                <span>Transcription failed, retry</span>
                            </div>
                            @if($activeMessage->ai_error)
                                <div class="text-xs font-normal text-rose-500 mt-0.5">{{ $activeMessage->ai_error }}</div>
                            @endif
                        </div>
                        <button wire:click="retryAi({{ $activeMessage->id }})" class="px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition shadow-sm self-start sm:self-auto">
                            Retry Transcription
                        </button>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm leading-relaxed {{ $activeMessage->isUrdu() ? 'urdu-text text-base' : '' }}" dir="{{ $activeMessage->direction() }}">
                        {{ $activeMessage->body }}
                    </div>
                @endif
            </div>

            <!-- Audio Player if audio present -->
            @if($activeMessage->source === 'voice' && $activeMessage->audio_path)
            <div class="mt-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Original Voice Note Audio</label>
                <audio controls class="w-full rounded">
                    <source src="{{ asset('storage/' . $activeMessage->audio_path) }}" type="audio/mpeg">
                </audio>
            </div>
            @endif

            <!-- AI Summary Card -->
            @if($activeMessage->summary)
            <div class="mt-4 p-3 rounded-xl bg-brand-50/60 dark:bg-brand-950/40 border border-brand-200 dark:border-brand-900/60">
                <div class="text-xs font-bold text-brand-700 dark:text-brand-300">Rule-Based AI Summary</div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $activeMessage->summary }}</div>
            </div>
            @endif

            <!-- 1-Click Category Correction Feed -->
            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                    Correct Category (1-Click Training Ground Truth)
                </label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['order', 'complaint', 'refund', 'urgent', 'inquiry', 'other'] as $catOption)
                    <button wire:click="correctCategory({{ $activeMessage->id }}, '{{ $catOption }}')" 
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition
                            {{ $activeMessage->category === $catOption ? 'bg-brand-600 text-white border-brand-500 shadow-sm' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        {{ ucfirst($catOption) }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <button wire:click="quickCycleStatus({{ $activeMessage->id }})" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200">
                    Cycle Status: {{ ucfirst(str_replace('_', ' ', $activeMessage->status)) }}
                </button>
                <button wire:click="closeMessageModal" class="px-5 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold hover:bg-brand-500">
                    Done
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
