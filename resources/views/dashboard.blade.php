<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout> <x-slot name="header"> <h2 class="font-semibold text-xl text-gray-800">Dashboard</h2> </x-slot> <div class="py-12"> <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"> <p>Welcome, {{ auth()->user()->name }}.</p> <p class="text-sm text-gray-500 mt-2">Role: {{ auth()->user()->getRoleNames()->join(', ') ?: 'No role assigned' }}</p> <p class="text-sm text-gray-500">Current campus ID in session: {{ session('current_campus_id') ?? 'none yet' }}</p> </div> </div> </div> </x-app-layout>