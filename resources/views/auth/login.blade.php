<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Campus Suite</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,700;1,600&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        parchment: '#FAF9F6',
                        ink: '#14213D',
                        indigo: '#1B2A4A',
                        gold: '#C9A227',
                        slate: '#5B6472',
                        signal: '#2F7A4D',
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                        mono: ['IBM Plex Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        ::selection { background: #C9A227; color: #14213D; }
        :focus-visible { outline: 2px solid #C9A227; outline-offset: 2px; }
        .bg-grid {
            background-image:
                linear-gradient(to right, rgba(250,249,246,0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(250,249,246,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; transition-duration: 0.001ms !important; }
        }
    </style>
</head>
<body class="bg-parchment text-ink font-sans antialiased">

    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- ============ LEFT: BRAND PANEL ============ --}}
        <div class="relative hidden lg:flex flex-col justify-between bg-ink text-parchment px-14 py-12 overflow-hidden">
            <div class="absolute inset-0 bg-grid opacity-40 pointer-events-none"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-gold/10 blur-3xl pointer-events-none"></div>

            <a href="/" class="relative flex items-center gap-2.5">
                <span class="grid place-items-center w-8 h-8 rounded-md bg-gold text-ink font-mono font-semibold text-sm">CS</span>
                <span class="font-display font-semibold text-lg tracking-tight">Campus Suite</span>
            </a>

            <div class="relative">
                <p class="font-mono text-xs uppercase tracking-[0.2em] text-gold mb-5">Welcome back</p>
                <h1 class="font-display text-4xl leading-[1.15] font-semibold tracking-tight max-w-md">
                    Every campus,<br><em class="text-gold font-medium not-italic">one</em> dashboard.
                </h1>
                <p class="mt-5 text-parchment/60 max-w-sm leading-relaxed">
                    Sign in to manage students, staff, attendance, fees and payroll across your entire school network.
                </p>

                {{-- echo of the dashboard signature element --}}
                <div class="mt-10 rounded-xl border border-white/10 bg-indigo max-w-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-white/10 bg-ink/40">
                        <span class="w-2 h-2 rounded-full bg-white/15"></span>
                        <span class="w-2 h-2 rounded-full bg-white/15"></span>
                        <span class="w-2 h-2 rounded-full bg-white/15"></span>
                        <span class="ml-3 text-[11px] font-mono text-parchment/40">campus-suite/dashboard</span>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-3">
                            <p class="text-[10px] uppercase tracking-wide text-parchment/40 font-mono">Attendance</p>
                            <p class="mt-1 font-display text-lg font-semibold text-signal">94%</p>
                        </div>
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-3">
                            <p class="text-[10px] uppercase tracking-wide text-parchment/40 font-mono">Campuses</p>
                            <p class="mt-1 font-display text-lg font-semibold">3 live</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="relative text-xs text-parchment/30 font-mono">© {{ date('Y') }} Campus Suite. All rights reserved.</p>
        </div>

        {{-- ============ RIGHT: LOGIN FORM ============ --}}
        <div class="flex items-center justify-center px-6 py-16 sm:px-10">
            <div class="w-full max-w-sm">

                {{-- mobile-only brand mark --}}
                <a href="/" class="lg:hidden flex items-center gap-2.5 mb-10">
                    <span class="grid place-items-center w-8 h-8 rounded-md bg-gold text-ink font-mono font-semibold text-sm">CS</span>
                    <span class="font-display font-semibold text-lg tracking-tight text-ink">Campus Suite</span>
                </a>

                <p class="font-mono text-xs uppercase tracking-[0.2em] text-slate mb-3">Sign in</p>
                <h2 class="font-display text-3xl font-semibold tracking-tight text-ink">Welcome back.</h2>
                <p class="mt-2 text-sm text-slate">Enter your details to access your campus dashboard.</p>

                {{-- Session status (e.g. password reset link sent) --}}
                @if (session('status'))
                    <div class="mt-6 rounded-md border border-signal/30 bg-signal/10 px-4 py-3 text-sm text-signal">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="mt-6 rounded-md border border-red-300 bg-red-50 px-4 py-3">
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-medium uppercase tracking-wide text-slate mb-2">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="w-full rounded-md border border-ink/15 bg-white px-4 py-3 text-sm text-ink placeholder:text-slate/50 focus:border-gold focus:ring-1 focus:ring-gold transition-colors"
                            placeholder="you@school.edu">
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-medium uppercase tracking-wide text-slate">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-slate hover:text-gold transition-colors">Forgot password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                                class="w-full rounded-md border border-ink/15 bg-white px-4 py-3 pr-11 text-sm text-ink placeholder:text-slate/50 focus:border-gold focus:ring-1 focus:ring-gold transition-colors"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate/50 hover:text-slate" aria-label="Toggle password visibility">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.243M9.878 9.878L3 3m6.878 6.878L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" name="remember" class="rounded border-ink/25 text-gold focus:ring-gold">
                        <span class="text-sm text-slate">Keep me signed in</span>
                    </label>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-ink text-parchment font-medium px-6 py-3.5 rounded-md hover:bg-ink/90 transition-colors">
                        Sign in
                    </button>
                </form>

                @if (Route::has('register'))
                    <p class="mt-8 text-center text-sm text-slate">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-ink hover:text-gold transition-colors">Create one</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

</body>
</html>