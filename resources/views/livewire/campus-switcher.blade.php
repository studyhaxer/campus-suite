<div class="px-4 pt-5" x-data="{ open: false }">
    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between px-2 mb-2 text-[11px] uppercase tracking-wider text-white/35 font-mono hover:text-white/60">
        <span>Campuses</span>
        <svg :class="open ? 'rotate-90' : ''" class="w-3 h-3 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
    <div x-show="open" x-transition x-cloak class="space-y-1">
        @php
            $palette = ['#E7C089', '#6FCF97', '#7FB3D5', '#E08585', '#B39DDB', '#80CBC4', '#F2A65A', '#9FA8DA'];
        @endphp

        @if (auth()->user()->hasRole('Manager'))
            <a href="{{ route('dashboard') }}" wire:navigate
               class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-sm {{ request()->routeIs('dashboard') ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                <span class="seal w-6 h-6 text-[10px] text-white">All</span>
                All Campuses
                <span class="ml-auto text-[10px] font-mono text-white/40">{{ $campuses->count() }}</span>
            </a>
        @endif

        @forelse ($campuses as $campus)
            @php
                $initials = mb_strtoupper(mb_substr(collect(explode(' ', $campus->name))->map(fn ($w) => mb_substr($w, 0, 1))->join(''), 0, 2));
                $color = $palette[$campus->id % count($palette)];
                $isActive = $campus->id === $currentCampusId;
            @endphp
            <button wire:click="switchTo({{ $campus->id }})"
                class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-sm {{ $isActive ? 'bg-white/10 font-medium' : 'hover:bg-white/5 text-white/70' }}">
                <span class="seal w-6 h-6 text-[10px]" style="color: {{ $color }}">{{ $initials }}</span>
                {{ $campus->name }}
            </button>
        @empty
            <p class="px-2.5 text-xs text-white/40">No campuses yet</p>
        @endforelse
    </div>
</div>