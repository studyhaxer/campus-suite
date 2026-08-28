<div>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <h2 class="font-semibold text-xl text-gray-800 mb-6">Student Attendance</h2>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Class / Section</label>
                        <select wire:model.live="classSectionId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select a section</option>
                            @foreach ($classSections as $section)
                                <option value="{{ $section->id }}">{{ $section->fullName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model.live="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>

            @if ($loaded && $students->count())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center px-6 py-4 border-b">
                        <span class="text-sm text-gray-500">{{ $students->count() }} students</span>
                        <div class="space-x-3 text-sm">
                            <button wire:click="markAll('present')" type="button" class="text-green-600 hover:text-green-800">Mark all Present</button>
                            <button wire:click="markAll('absent')" type="button" class="text-red-600 hover:text-red-800">Mark all Absent</button>
                        </div>
                    </div>
                    <div class="divide-y">
                        @foreach ($students as $student)
                            <div class="flex justify-between items-center px-6 py-3" wire:key="student-{{ $student->id }}">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ $student->full_name }}</span>
                                    <span class="text-gray-400 font-mono ml-2">{{ $student->admission_number }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('statuses.{{ $student->id }}', 'present')"
                                        @class(['px-3 py-1 text-xs rounded-full', 'bg-green-600 text-white' => ($statuses[$student->id] ?? '') === 'present', 'bg-gray-100 text-gray-500' => ($statuses[$student->id] ?? '') !== 'present'])>
                                        Present
                                    </button>
                                    <button type="button" wire:click="$set('statuses.{{ $student->id }}', 'absent')"
                                        @class(['px-3 py-1 text-xs rounded-full', 'bg-red-600 text-white' => ($statuses[$student->id] ?? '') === 'absent', 'bg-gray-100 text-gray-500' => ($statuses[$student->id] ?? '') !== 'absent'])>
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
            @elseif ($loaded)
                <div class="bg-white shadow-sm rounded-lg px-6 py-8 text-center text-sm text-gray-500">
                    No active students in this section.
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg px-6 py-8 text-center text-sm text-gray-500">
                    Select a class/section and date to mark attendance.
                </div>
            @endif

        </div>
    </div>
</div>