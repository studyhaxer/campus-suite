<div>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <h2 class="font-semibold text-xl text-gray-800 mb-6">Staff Attendance</h2>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" wire:model.live="date" class="mt-1 block w-full sm:w-64 rounded-md border-gray-300 shadow-sm">
            </div>

            @if ($staff->count())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center px-6 py-4 border-b">
                        <span class="text-sm text-gray-500">{{ $staff->count() }} staff</span>
                        <div class="space-x-3 text-sm">
                            <button wire:click="markAll('present')" type="button" class="text-green-600 hover:text-green-800">Mark all Present</button>
                            <button wire:click="markAll('absent')" type="button" class="text-red-600 hover:text-red-800">Mark all Absent</button>
                        </div>
                    </div>
                    <div class="divide-y">
                        @foreach ($staff as $profile)
                            <div class="flex justify-between items-center px-6 py-3" wire:key="staffatt-{{ $profile->user_id }}">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ $profile->user->name }}</span>
                                    <span class="text-gray-400 ml-2">{{ $profile->designation }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('statuses.{{ $profile->user_id }}', 'present')"
                                        @class(['px-3 py-1 text-xs rounded-full', 'bg-green-600 text-white' => ($statuses[$profile->user_id] ?? '') === 'present', 'bg-gray-100 text-gray-500' => ($statuses[$profile->user_id] ?? '') !== 'present'])>
                                        Present
                                    </button>
                                    <button type="button" wire:click="$set('statuses.{{ $profile->user_id }}', 'absent')"
                                        @class(['px-3 py-1 text-xs rounded-full', 'bg-red-600 text-white' => ($statuses[$profile->user_id] ?? '') === 'absent', 'bg-gray-100 text-gray-500' => ($statuses[$profile->user_id] ?? '') !== 'absent'])>
                                        Absent
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-4 border-t flex justify-end">
                        <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Save Attendance
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg px-6 py-8 text-center text-sm text-gray-500">
                    No active staff in this campus.
                </div>
            @endif

        </div>
    </div>
</div>