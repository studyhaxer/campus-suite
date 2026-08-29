<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Campuses</h2>
                <button wire:click="openCreate"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    + Add Campus
                </button>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Currency
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($campuses as $campus)
                                <tr wire:key="campus-{{ $campus->id }}">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $campus->code }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $campus->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $campus->city ?: '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $campus->currency }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $campus->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $campus->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <button wire:click="openEdit({{ $campus->id }})"
                                            class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button wire:click="toggleActive({{ $campus->id }})"
                                            wire:confirm="Are you sure?" class="text-gray-500 hover:text-gray-700">
                                            {{ $campus->is_active ? 'Deactivate' : 'Reactivate' }}
                                        </button>
                                        @can('delete', $campus)
                                            <button wire:click="delete({{ $campus->id }})"
                                                wire:confirm="Delete {{ $campus->name }} permanently? This will also delete ALL of its students, classes, sections, and staff records (and staff login accounts with no other campus). This cannot be undone."
                                                wire:loading.attr="disabled" wire:target="delete({{ $campus->id }})"
                                                class="text-red-600 hover:text-red-800 disabled:opacity-50">
                                                Delete
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No campuses yet. Click "Add Campus" to create your first one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $editingCampusId ? 'Edit Campus' : 'Add Campus' }}</h3>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Campus Name</label>
                                <input type="text" wire:model="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" wire:model="address"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <input type="text" wire:model="city"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <input type="text" wire:model="region"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                                <input type="text" wire:model="contact_phone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                                <input type="email" wire:model="contact_email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('contact_email')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                                <input type="text" wire:model="timezone" placeholder="e.g. Asia/Karachi"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency</label>
                                <input type="text" wire:model="currency" maxlength="3" placeholder="e.g. USD"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm uppercase">
                                @error('currency')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                                <input type="text" wire:model="academic_year" placeholder="e.g. 2026-2027"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pay Cycle</label>
                                <select wire:model="pay_cycle"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="biweekly">Biweekly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                Save Campus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>