<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Staff</h2>
                <div class="flex items-center gap-2">
                    @can('viewAny', \App\Models\StaffProfile::class)
                        <button wire:click="exportStaff"
                            class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            Export
                        </button>
                    @endcan
                    @can('create', \App\Models\StaffProfile::class)
                        <button wire:click="downloadTemplate"
                            class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            Download Template
                        </button>
                        <button wire:click="openImport"
                            class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            Import
                        </button>
                        <button wire:click="openCreate"
                            style="background-color: #4f46e5 !important; color: #ffffff !important;"
                            class="px-4 py-2 text-sm font-medium rounded-md hover:bg-indigo-700">
                            + Add Staff
                        </button>
                    @endcan
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email"
                    class="rounded-md border-gray-300 shadow-sm text-sm">
                <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($staff as $profile)
                                <tr wire:key="staff-{{ $profile->id }}">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $profile->user->name }}
                                        <div class="text-xs text-gray-400">{{ $profile->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $profile->user->roles->first()?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->designation }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $profile->department ?: '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $profile->joining_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span @class([
                                            'px-2 py-1 text-xs rounded-full',
                                            'bg-green-100 text-green-700' => $profile->employment_status === 'active',
                                            'bg-yellow-100 text-yellow-700' =>
                                                $profile->employment_status === 'on_leave',
                                            'bg-gray-100 text-gray-500' => $profile->employment_status === 'terminated',
                                        ])>
                                            {{ ucfirst(str_replace('_', ' ', $profile->employment_status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        @can('update', $profile)
                                            <button wire:click="openEdit({{ $profile->id }})"
                                                class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                            <button wire:click="toggleStatus({{ $profile->id }})"
                                                wire:confirm="Are you sure?" class="text-gray-500 hover:text-gray-700">
                                                {{ $profile->employment_status === 'terminated' ? 'Reactivate' : 'Terminate' }}
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No staff yet. Click "Add Staff" to create your first record.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">{{ $staff->links() }}</div>
            </div>

        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        {{ $editingProfileId ? 'Edit Staff Member' : 'Add Staff Member' }}</h3>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" wire:model="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('email')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Password {{ $editingProfileId ? '(leave blank to keep current)' : '' }}
                                </label>
                                <input type="password" wire:model="password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('password')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" wire:model="password_confirmation"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <select wire:model="role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select role</option>
                                    @foreach ($assignableRoles as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Employment Status</label>
                                <select wire:model="employment_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="active">Active</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Designation</label>
                                <input type="text" wire:model="designation" placeholder="e.g. Math Teacher"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('designation')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Department</label>
                                <input type="text" wire:model="department" placeholder="e.g. Science Wing"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Joining Date</label>
                                <input type="date" wire:model="joining_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('joining_date')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Base Salary</label>
                                <input type="number" step="0.01" wire:model="base_salary"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('base_salary')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save
                                Staff Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showImportModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            wire:click.self="$set('showImportModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2">Import Staff</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Download the template, fill in staff details, then upload it here.
                        New accounts are created with the temporary password <span
                            class="font-mono font-medium">11223344</span> —
                        staff should change it after first login.
                    </p>

                    <form wire:submit="import" class="space-y-4">
                        <div>
                            <input type="file" wire:model="importFile" accept=".xlsx,.xls"
                                class="block w-full text-sm text-gray-700">
                            <div wire:loading wire:target="importFile" class="text-xs text-gray-400 mt-1">Uploading…
                            </div>
                            @error('importFile')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($importedCount !== null)
                            <div class="p-3 bg-green-50 text-green-700 text-sm rounded-md">
                                {{ $importedCount }} staff member(s) imported successfully.
                            </div>
                        @endif

                        @if (!empty($importErrors))
                            <div class="p-3 bg-red-50 text-red-700 text-sm rounded-md max-h-48 overflow-y-auto">
                                <p class="font-medium mb-1">{{ count($importErrors) }} row(s) skipped:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($importErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showImportModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Close</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="import"
                                style="background-color: #4f46e5 !important; color: #ffffff !important;"
                                class="px-4 py-2 text-sm font-medium rounded-md disabled:opacity-50">
                                <span wire:loading.remove wire:target="import">Upload &amp; Import</span>
                                <span wire:loading wire:target="import">Importing…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
