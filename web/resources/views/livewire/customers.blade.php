<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span x-show="lang === 'en'">Customer Profiles & Summary</span>
                <span x-show="lang === 'ur'" class="font-nastaliq text-3xl">گاہکوں کی تفصیل اور تاریخ</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Customer-level triage summary, order volume, return disputes, and communication timeline.
            </p>
        </div>

        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Search customer name or phone..." 
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500">
        </div>
    </div>

    <div class="grid grid-cols-1 {{ $selectedCustomer ? 'lg:grid-cols-3' : '' }} gap-6">
        <!-- Customer Cards Grid -->
        <div class="{{ $selectedCustomer ? 'lg:col-span-2' : '' }} grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($customers as $c)
            <div wire:click="selectCustomer({{ $c->id }})" 
                 class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/40 border cursor-pointer transition transform hover:scale-[1.01] shadow-sm {{ $selectedCustomerId === $c->id ? 'border-brand-500 ring-2 ring-brand-500/50 bg-brand-50/20 dark:bg-brand-950/20' : 'border-slate-200 dark:border-brand-900/40 hover:border-slate-300 dark:hover:border-slate-700' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-emerald-400 text-white font-bold flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($c->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $c->name }}</h3>
                            <div class="text-xs text-slate-400">{{ $c->phone ?: 'No phone logged' }}</div>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">
                        {{ $c->total_messages }} msgs
                    </span>
                </div>

                <!-- Stats summary tags -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-medium">
                        {{ $c->orders_count }} Orders
                    </span>
                    <span class="px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-medium">
                        {{ $c->complaints_count }} Complaints
                    </span>
                    <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-medium">
                        {{ $c->refunds_count }} Refunds
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400">
                No customer records found.
            </div>
            @endforelse
        </div>

        <!-- Customer Drill-down Drawer (Right Column) -->
        @if($selectedCustomer)
        <div class="p-5 rounded-2xl bg-white dark:bg-[#06241A]/50 border border-brand-500/40 shadow-xl space-y-4 animate-scale-up">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="font-extrabold text-base text-slate-900 dark:text-white">{{ $selectedCustomer->name }}</h2>
                    <p class="text-xs text-slate-400">{{ $selectedCustomer->phone }} &bull; {{ $selectedCustomer->email ?: 'Direct Client' }}</p>
                </div>
                <button wire:click="$set('selectedCustomerId', null)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                    ✕
                </button>
            </div>

            @if($selectedCustomer->notes)
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-xs text-slate-600 dark:text-slate-300">
                <span class="font-bold text-slate-400">Notes:</span> {{ $selectedCustomer->notes }}
            </div>
            @endif

            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Conversation History ({{ $selectedCustomer->messages->count() }})</h4>
                <div class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1">
                    @foreach($selectedCustomer->messages as $cm)
                    <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="px-2 py-0.5 rounded-full font-bold uppercase
                                @if($cm->category === 'order') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                @elseif($cm->category === 'complaint') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                                @elseif($cm->category === 'refund') bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300
                                @elseif($cm->category === 'urgent') bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300
                                @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300
                                @endif">
                                {{ $cm->category }}
                            </span>
                            <span class="text-slate-400">{{ $cm->created_at->format('M d, h:i A') }}</span>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-300 {{ $cm->isUrdu() ? 'urdu-text' : '' }}" dir="{{ $cm->direction() }}">
                            {{ $cm->body }}
                        </div>
                        @if($cm->summary)
                            <div class="text-[11px] text-brand-600 dark:text-brand-400 font-medium">
                                {{ $cm->summary }}
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $customers->links() }}
    </div>

</div>
