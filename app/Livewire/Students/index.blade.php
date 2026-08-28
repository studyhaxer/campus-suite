<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Students</h2>
                @can('create', \App\Models\Student::class)
                    <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        + Add Student
                    </button>
                @endcan
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or admission #"
                       class="rounded-md border-gray-300 shadow-sm text-sm">
                <select wire:model.live="classSectionFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Classes</option>
                    @foreach ($classSections as $section)
                        <option value="{{ $section->id }}">{{ $section->fullName }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guardian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($students as $student)
                                <tr wire:key="student-{{ $student->id }}">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $student->admission_number }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $student->full_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $student->classSection?->fullName ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $student->guardian_name ?: '—' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        @can('update', $student)
                                            <button wire:click="openEdit({{ $student->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                            <button wire:click="toggleStatus({{ $student->id }})" wire:confirm="Are you sure?" class="text-gray-500 hover:text-gray-700">
                                                {{ $student->status === 'active' ? 'Deactivate' : 'Reactivate' }}
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $students->links() }}</div>
            </div>

        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $editingStudentId ? 'Edit Student' : 'Add Student' }}</h3>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Admission Number</label>
                                <input type="text" wire:model="admission_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('admission_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Admission Date</label>
                                <input type="date" wire:model="admission_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('admission_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" wire:model="first_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" wire:model="last_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                <input type="date" wire:model="date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('date_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Gender</label>
                                <select wire:model="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">—</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Class / Section</label>
                                <select wire:model="class_section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Unassigned</option>
                                    @foreach ($classSections as $section)
                                        <option value="{{ $section->id }}">{{ $section->fullName }}</option>
                                    @endforeach
                                </select>
                                @error('class_section_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guardian Name</label>
                                <input type="text" wire:model="guardian_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guardian Phone</label>
                                <input type="text" wire:model="guardian_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Guardian Email</label>
                                <input type="email" wire:model="guardian_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('guardian_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea wire:model="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>