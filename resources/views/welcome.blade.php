<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Suite — Multi-Campus School Management</title>
    <meta name="description" content="Manage students, staff, attendance, fees and payroll across every campus from one consolidated dashboard.">

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
        html { scroll-behavior: smooth; }
        body { font-feature-settings: "ss01"; }
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

    {{-- ============ NAV ============ --}}
    <header x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 bg-ink/95 backdrop-blur border-b border-white/10">
        <nav class="mx-auto max-w-7xl px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 shrink-0">
                <span class="grid place-items-center w-8 h-8 rounded-md bg-gold text-ink font-mono font-semibold text-sm">CS</span>
                <span class="text-parchment font-display font-semibold text-lg tracking-tight">Campus Suite</span>
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm text-parchment/80">
                <a href="#features" class="hover:text-gold transition-colors">Features</a>
                <a href="#roles" class="hover:text-gold transition-colors">Roles</a>
                <a href="#contact" class="hover:text-gold transition-colors">Contact</a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-parchment/90 hover:text-gold transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-parchment/90 hover:text-gold transition-colors">Sign in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-medium bg-gold text-ink px-4 py-2 rounded-md hover:bg-gold/90 transition-colors">Create account</a>
                        @endif
                    @endauth
                @endif
            </div>

            <button @click="open = !open" class="md:hidden text-parchment" aria-label="Toggle menu">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        <div x-show="open" x-cloak x-transition class="md:hidden bg-ink border-t border-white/10 px-6 py-4 space-y-3">
            <a href="#features" @click="open=false" class="block text-parchment/80 text-sm">Features</a>
            <a href="#roles" @click="open=false" class="block text-parchment/80 text-sm">Roles</a>
            <a href="#contact" @click="open=false" class="block text-parchment/80 text-sm">Contact</a>
            <div class="pt-3 border-t border-white/10 flex flex-col gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-parchment">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-parchment">Sign in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-medium bg-gold text-ink px-4 py-2 rounded-md text-center">Create account</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="relative bg-ink text-parchment pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden">
        <div class="absolute inset-0 bg-grid opacity-40 pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-gold/10 blur-3xl pointer-events-none"></div>

        <div class="relative mx-auto max-w-7xl px-6 grid lg:grid-cols-2 gap-16 items-center">
            {{-- Copy --}}
            <div>
                <p class="font-mono text-xs uppercase tracking-[0.2em] text-gold mb-5">Multi-campus school management</p>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] leading-[1.08] font-semibold tracking-tight">
                    Every campus,<br>
                    <em class="text-gold font-medium not-italic">one</em> dashboard.
                </h1>
                <p class="mt-6 text-parchment/70 text-lg leading-relaxed max-w-xl">
                    Campus Suite brings students, staff, attendance, fees and payroll for your entire school network into a single, consolidated view — no more re-logging in per campus, no more spreadsheets that don't talk to each other.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-gold text-ink font-medium px-6 py-3.5 rounded-md hover:bg-gold/90 transition-colors">
                            Create an account
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 border border-parchment/25 text-parchment font-medium px-6 py-3.5 rounded-md hover:border-gold hover:text-gold transition-colors">
                            Sign in
                        </a>
                    @endif
                </div>
                <p class="mt-5 text-xs text-parchment/40 font-mono">Set up in a day. No spreadsheets, no per-campus logins.</p>
            </div>

            {{-- Signature: live campus-switcher dashboard mockup --}}
            <div x-data="{
                    active: 0,
                    campuses: [
                        { name: 'Main Campus', code: 'MAIN', students: '1,240', attendance: '94%', fees: '$128,400', staff: '86' },
                        { name: 'North Campus', code: 'NRTH', students: '860', attendance: '91%', fees: '$79,200', staff: '54' },
                        { name: 'City Campus', code: 'CITY', students: '1,510', attendance: '96%', fees: '$164,900', staff: '102' },
                    ]
                 }" class="relative">
                <div class="rounded-xl border border-white/10 bg-indigo shadow-2xl shadow-black/40 overflow-hidden">
                    {{-- window chrome --}}
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-white/10 bg-ink/40">
                        <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
                        <span class="ml-3 text-[11px] font-mono text-parchment/40">campus-suite/dashboard</span>
                        <span class="ml-auto flex items-center gap-1.5 text-[11px] font-mono text-signal">
                            <span class="w-1.5 h-1.5 rounded-full bg-signal animate-pulse"></span> live
                        </span>
                    </div>

                    {{-- campus tabs --}}
                    <div class="flex border-b border-white/10 px-2 pt-2 gap-1">
                        <template x-for="(c, i) in campuses" :key="c.code">
                            <button @click="active = i"
                                :class="active === i ? 'bg-parchment/5 text-parchment border-gold' : 'text-parchment/40 border-transparent hover:text-parchment/70'"
                                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors">
                                <span x-text="c.name"></span>
                            </button>
                        </template>
                    </div>

                    {{-- stat grid --}}
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-4">
                            <p class="text-[11px] uppercase tracking-wide text-parchment/40 font-mono">Students</p>
                            <p class="mt-2 font-display text-2xl font-semibold" x-text="campuses[active].students"></p>
                        </div>
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-4">
                            <p class="text-[11px] uppercase tracking-wide text-parchment/40 font-mono">Attendance today</p>
                            <p class="mt-2 font-display text-2xl font-semibold text-signal" x-text="campuses[active].attendance"></p>
                        </div>
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-4">
                            <p class="text-[11px] uppercase tracking-wide text-parchment/40 font-mono">Fees collected</p>
                            <p class="mt-2 font-display text-2xl font-semibold" x-text="campuses[active].fees"></p>
                        </div>
                        <div class="rounded-lg bg-ink/40 border border-white/5 p-4">
                            <p class="text-[11px] uppercase tracking-wide text-parchment/40 font-mono">Staff on payroll</p>
                            <p class="mt-2 font-display text-2xl font-semibold" x-text="campuses[active].staff"></p>
                        </div>
                    </div>

                    <div class="px-6 pb-6 flex items-center justify-between text-[11px] font-mono text-parchment/30">
                        <span>Campus code: <span class="text-gold" x-text="campuses[active].code"></span></span>
                        <span>Synced just now</span>
                    </div>
                </div>
                <p class="text-center text-xs text-parchment/30 mt-4">Same login. Different campus, one tap away.</p>
            </div>
        </div>
    </section>

    {{-- ============ TRUST STRIP ============ --}}
    <section class="bg-parchment border-b border-ink/10">
        <div class="mx-auto max-w-7xl px-6 py-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-3 text-center">
            <span class="text-[11px] uppercase tracking-[0.2em] text-slate font-mono">Made for school networks that outgrew spreadsheets</span>
        </div>
    </section>

    {{-- ============ FEATURES ============ --}}
    <section id="features" class="mx-auto max-w-7xl px-6 py-24">
        <div class="max-w-2xl">
            <p class="font-mono text-xs uppercase tracking-[0.2em] text-slate mb-4">Everything, one login</p>
            <h2 class="font-display text-3xl sm:text-4xl font-semibold tracking-tight text-ink">Six systems that used to live in six spreadsheets.</h2>
        </div>

        <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    ['title' => 'Students', 'desc' => 'One registry of enrollment, records and academic history — searchable across every campus, not just the one you happen to be logged into.', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.42L12 14z'],
                    ['title' => 'Staff & HR', 'desc' => 'Profiles, roles and campus assignments managed centrally, so a staff transfer between campuses takes an edit, not a migration.', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['title' => 'Attendance', 'desc' => 'Daily capture for students and staff, campus by campus, rolled up into one attendance picture for the whole network.', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['title' => 'Fees & billing', 'desc' => 'Set fee structures per campus, invoice, and track collections — reconciled centrally instead of chased down at term end.', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z'],
                    ['title' => 'Payroll', 'desc' => 'Run payroll per campus or all at once, with every run feeding into one consolidated payroll register.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2'],
                    ['title' => 'Multi-campus control', 'desc' => 'Switch campuses without switching accounts. Permissions stay scoped to what each person is actually responsible for.', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ];
            @endphp

            @foreach ($features as $f)
                <div class="group rounded-xl border border-ink/10 bg-white p-6 hover:border-gold/60 hover:shadow-lg hover:shadow-ink/5 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-ink/5 grid place-items-center group-hover:bg-gold/10 transition-colors">
                        <svg class="w-5 h-5 text-ink group-hover:text-gold transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $f['icon'] }}"/></svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $f['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ ROLES ============ --}}
    <section id="roles" class="bg-ink text-parchment py-24">
        <div class="mx-auto max-w-7xl px-6">
            <div class="max-w-2xl">
                <p class="font-mono text-xs uppercase tracking-[0.2em] text-gold mb-4">Built around who's logging in</p>
                <h2 class="font-display text-3xl sm:text-4xl font-semibold tracking-tight">Every role sees a different Campus Suite.</h2>
            </div>

            <div x-data="{
                    tab: 0,
                    roles: [
                        { name: 'Admin', desc: 'The whole network, at a glance.', points: ['Enrollment, attendance and collections side by side, campus by campus', 'Create and configure new campuses without touching code', 'Full audit trail across every module'] },
                        { name: 'Registrar', desc: 'One campus, fully in hand.', points: ['Manage enrollment and student records for their own campus', 'Nothing from other campuses clutters the view', 'Bulk import and update student data'] },
                        { name: 'Accountant', desc: 'Money, reconciled centrally.', points: ['Run fee collection for one campus or every campus at once', 'Process payroll runs with a consolidated ledger', 'Export statements per campus or network-wide'] },
                        { name: 'Teacher', desc: 'Just their classroom.', points: ['Take attendance for assigned classes in seconds', 'View rosters scoped to their own campus only', 'No admin clutter, no other campuses in sight'] },
                    ]
                 }" class="mt-14 grid md:grid-cols-[220px_1fr] gap-8 md:gap-12">

                <div class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                    <template x-for="(r, i) in roles" :key="r.name">
                        <button @click="tab = i"
                            :class="tab === i ? 'bg-white/5 text-parchment border-gold' : 'text-parchment/40 border-transparent hover:text-parchment/70'"
                            class="shrink-0 text-left px-4 py-3 rounded-md border-l-2 md:border-l-2 transition-colors">
                            <span class="block font-display font-semibold" x-text="r.name"></span>
                        </button>
                    </template>
                </div>

                <div class="rounded-xl border border-white/10 bg-indigo p-8">
                    <p class="font-display text-2xl font-semibold text-gold" x-text="roles[tab].desc"></p>
                    <ul class="mt-6 space-y-4">
                        <template x-for="p in roles[tab].points" :key="p">
                            <li class="flex items-start gap-3 text-parchment/75 text-sm leading-relaxed">
                                <svg class="w-4 h-4 mt-1 shrink-0 text-signal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="p"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ FOOTER ============ --}}
    <footer id="contact" class="bg-ink text-parchment/60">
        <div class="mx-auto max-w-7xl px-6 py-6 text-center text-xs text-parchment/30">
            <span>© {{ date('Y') }} Campus Suite. All rights reserved.</span>
        </div>
    </footer>

</body>
</html>