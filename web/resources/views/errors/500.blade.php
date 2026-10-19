<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | Fauri (فوری)</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Noto+Nastaliq+Urdu:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-[#06241A] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950 flex items-center justify-center text-rose-600 text-3xl font-black">
            500
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Server Hiccup</h1>
            <p class="font-nastaliq text-xl text-rose-500 mt-1" dir="rtl">سرور میں عارضی خرابی پیش آئی ہے</p>
            <p class="text-sm text-slate-500 mt-2">Our resilient background queue preserves all customer messages. Please refresh or try again in a moment.</p>
        </div>
        <div>
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm transition">
                Back to Dashboard &rarr;
            </a>
        </div>
    </div>
</body>
</html>
