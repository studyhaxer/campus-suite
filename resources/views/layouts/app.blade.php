<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Campus Suite') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,wght@0,500;0,600;1,500&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Newsreader', Georgia, serif; }
        .font-mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }
        .seal {
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 9999px; font-family: 'IBM Plex Mono', monospace; font-weight: 600;
            border: 1.5px solid currentColor; flex-shrink: 0;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #C7CCD6; border-radius: 3px; }
    </style>
</head>
<body class="font-sans antialiased bg-[#F6F7FA] text-[#1C2541]">

    <div class="flex min-h-screen" x-data="{ mobileNavOpen: false }">

        <!-- Mobile nav backdrop -->
        <div x-show="mobileNavOpen" x-cloak @click="mobileNavOpen = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="w-64 shrink-0 bg-[#1C2541] text-white flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-200 lg:static lg:translate-x-0">

            <div class="px-6 py-6 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <div class="seal w-8 h-8 text-[#E7C089] text-xs">CS</div>
                    <div>
                        <a href="{{ route('dashboard') }}" wire:navigate class="font-display text-lg leading-none block">Campus Suite</a>
                        <p class="text-[11px] text-white/40 font-mono tracking-wide mt-0.5">MC-SMS · Registry</p>
                    </div>
                </div>
            </div>

            @livewire('campus-switcher')

            <nav class="px-4 pt-6 space-y-0.5 text-sm">
                <p class="text-[11px] uppercase tracking-wider text-white/35 font-mono px-2 mb-2">Manage</p>

                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                    <span class="w-4 text-center">▦</span> Dashboard
                </a>

                @role('Manager')
                    <a href="{{ route('campuses.index') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('campuses.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">⌂</span> Campuses
                    </a>
                    <a href="{{ route('users.index') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('users.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">◐</span> Users
                    </a>
                @endrole

                @role('Manager|Campus Admin|Teacher')
                    <a href="{{ route('students.index') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('students.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">◎</span> Students
                    </a>
                @endrole

                @role('Manager|Campus Admin')
                    <a href="{{ route('academics.classes') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('academics.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">▥</span> Classes
                    </a>
                    <a href="{{ route('staff.index') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('staff.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">✎</span> Staff
                    </a>
                @endrole

                @role('Manager|Campus Admin|Teacher')
                    <a href="{{ route('attendance.students') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('attendance.students') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">✓</span> Student Attendance
                    </a>
                @endrole
                @role('Manager|Campus Admin')
                    <a href="{{ route('attendance.staff') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('attendance.staff') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">⏱</span> Staff Attendance
                    </a>
                @endrole

                @role('Manager|Campus Admin|Accountant')
                    <a href="{{ route('fees.invoices') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('fees.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">◈</span> Fees
                    </a>
                @endrole

                @role('Manager|Campus Admin')
                    <a href="{{ route('payroll.index') }}" wire:navigate
                       class="flex items-center gap-2.5 px-2.5 py-2 rounded-md {{ request()->routeIs('payroll.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                        <span class="w-4 text-center">◫</span> Payroll
                    </a>
                @endrole
            </nav>

            <div class="mt-auto px-6 py-5 border-t border-white/10">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center text-xs font-medium">
                        {{ mb_strtoupper(collect(explode(' ', auth()->user()->name))->map(fn ($w) => mb_substr($w, 0, 1))->join('')) }}
                    </div>
                    <div class="text-xs">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-white/40">{{ auth()->user()->getRoleNames()->first() ?? 'No role' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <a href="{{ route('profile') }}" wire:navigate class="text-white/50 hover:text-white">Profile</a>
                    <span class="text-white/20">·</span>
                    <livewire:layout.navigation />
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 flex flex-col min-w-0 lg:ml-0">

            <!-- Topbar -->
            <header class="h-16 flex items-center justify-between px-4 sm:px-8 border-b border-[#E2E5EC] bg-white">
                <div class="flex items-center gap-3">
                    <button @click="mobileNavOpen = true" class="lg:hidden text-[#1C2541]">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <p class="font-display text-xl">
                        @php
                            $routeName = (string) request()->route()?->getName();
                            $pageTitle = match(true) {
                                $routeName === 'dashboard' => 'Overview',
                                str_starts_with($routeName, 'campuses.') => 'Campuses',
                                str_starts_with($routeName, 'users.') => 'Users',
                                str_starts_with($routeName, 'students.') => 'Students',
                                str_starts_with($routeName, 'academics.') => 'Classes & Sections',
                                str_starts_with($routeName, 'staff.') => 'Staff',
                                $routeName === 'attendance.students' => 'Student Attendance',
                                $routeName === 'attendance.staff' => 'Staff Attendance',
                                str_starts_with($routeName, 'fees.') => 'Fees',
                                str_starts_with($routeName, 'payroll.') => 'Payroll',
                                $routeName === 'profile' => 'Profile',
                                default => config('app.name', 'Campus Suite'),
                            };
                        @endphp
                        {{ $pageTitle }}
                    </p>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    @php
                        $activeCampus = auth()->user()->hasRole('Manager')
                            ? \App\Models\Campus::find(session('current_campus_id'))
                            : auth()->user()->campuses->first();
                    @endphp
                    @if ($activeCampus)
                        <span class="hidden sm:inline text-xs font-mono text-[#6B7280] border border-[#E2E5EC] rounded px-2 py-1">
                            {{ $activeCampus->name }}{{ $activeCampus->academic_year ? ' · ' . $activeCampus->academic_year : '' }}
                        </span>
                    @endif
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto">
                {{ $slot }}
            </div>
        </main>
    </div>

</body>
</html>