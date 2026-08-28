<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-md px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h1 class="text-xl font-semibold text-gray-800">{{ __('Students') }}</h1>

                <button wire:click="openCreate" type="button"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150">
                    {{ __('Add Student') }}
                </button>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('Name or admission number...') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Class Section') }}</label>
                        <select wire:model.live="classSectionFilter"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('All Sections') }}</option>
                            @foreach ($classSections as $section)
                                <option value="{{ $section->id }}">
                                    {{ $section->schoolClass->name }} - {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select wire:model.live="statusFilter"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Admission #') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Class Section') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Guardian') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($students as $student)
                            <tr wire:key="student-{{ $student->id }}">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $student->admission_number }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $student->classSection?->schoolClass?->name }}
                                    @if ($student->classSection)
                                        - {{ $student->classSection->name }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $student->guardian_name }}
                                    @if ($student->guardian_phone)
                                        <div class="text-xs text-gray-400">{{ $student->guardian_phone }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span @class([
                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $student->status === 'active',
                                        'bg-gray-100 text-gray-600' => $student->status !== 'active',
                                    ])>
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-2 whitespace-nowrap">
                                    <button wire:click="openEdit({{ $student->id }})" type="button"
                                        class="text-gray-600 hover:text-gray-900 font-medium">
                                        {{ __('Edit') }}
                                    </button>
                                    <button wire:click="toggleStatus({{ $student->id }})" type="button"
                                        wire:confirm="{{ __('Are you sure you want to change this student\'s status?') }}"
                                        class="text-gray-600 hover:text-gray-900 font-medium">
                                        {{ $student->status === 'active' ? __('Deactivate') : __('Activate') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('No students found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-on:keydown.escape.window="$wire.showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-500/75" wire:click="$set('showModal', false)"></div>

                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        {{ $editingStudentId ? __('Edit Student') : __('Add Student') }}
                    </h2>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Admission Number') }}</label>
                                <input type="text" wire:model="admission_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('admission_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Class Section') }}</label>
                                <select wire:model="class_section_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">{{ __('Unassigned') }}</option>
                                    @foreach ($classSections as $section)
                                        <option value="{{ $section->id }}">
                                            {{ $section->schoolClass->name }} - {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_section_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('First Name') }}</label>
                                <input type="text" wire:model="first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('first_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Last Name') }}</label>
                                <input type="text" wire:model="last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('last_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Date of Birth') }}</label>
                                <input type="date" wire:model="date_of_birth" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('date_of_birth') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Gender') }}</label>
                                <select wire:model="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">{{ __('Select...') }}</option>
                                    <option value="male">{{ __('Male') }}</option>
                                    <option value="female">{{ __('Female') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                @error('gender') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Admission Date') }}</label>
                                <input type="date" wire:model="admission_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('admission_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Guardian Name') }}</label>
                                <input type="text" wire:model="guardian_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('guardian_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Guardian Phone') }}</label>
                                <input type="text" wire:model="guardian_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('guardian_phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Guardian Email') }}</label>
                                <input type="email" wire:model="guardian_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                @error('guardian_email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                                <textarea wire:model="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"></textarea>
                                @error('address') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>