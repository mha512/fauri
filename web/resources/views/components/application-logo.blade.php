<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <!-- Logomark Icon -->
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#06241A] to-[#0F4C3A] border border-brand-500/30 flex items-center justify-center shadow-sm relative overflow-hidden flex-shrink-0">
        <svg viewBox="0 0 100 100" class="w-7 h-7" fill="none">
            <circle cx="58" cy="38" r="10" stroke="#10B981" stroke-width="4.5" fill="none"/>
            <circle cx="58" cy="22" r="4" fill="#F59E0B"/>
            <path d="M58 48 C56 62 44 68 32 68 C24 68 20 64 22 60 C24 56 30 56 36 58 C42 60 50 58 52 46" 
                  stroke="#10B981" stroke-width="4.5" stroke-linecap="round" fill="none"/>
            <path d="M68 56 L78 56 L72 68 L82 68 L66 84 L70 72 L62 72 Z" fill="#F59E0B"/>
        </svg>
    </div>
    <!-- Brand Name + Urdu Script -->
    <div class="flex items-baseline gap-2">
        <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Fauri</span>
        <span class="font-bold text-xl text-brand-600 dark:text-brand-400 font-nastaliq" dir="rtl">فوری</span>
    </div>
</div>
