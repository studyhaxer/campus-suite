<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Users</h2>
                @can('create', \App\Models\User::class)
                    <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        + Add User
                    </button>
                @endcan
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campuses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $user->roles->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if ($user->roles->pluck('name')->contains('Manager'))
                                        <span class="text-gray-400">All campuses</span>
                                    @else
                                        {{ $user->campuses->pluck('name')->join(', ') ?: '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @can('update', $user)
                                        <button wire:click="openEdit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            Edit
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- User Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $editingUserId ? 'Edit User' : 'Add User' }}</h3>

                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Password {{ $editingUserId ? '(leave blank to keep current)' : '' }}
                            </label>
                            <input type="password" wire:model="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" wire:model="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select wire:model.live="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select a role...</option>
                                <option value="Manager">Manager</option>
                                <option value="Campus Admin">Campus Admin</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Accountant">Accountant</option>
                            </select>
                            @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if ($role && $role !== 'Manager')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Campuses</label>
                                <div class="space-y-1 max-h-32 overflow-y-auto border border-gray-200 rounded-md p-2">
                                    @forelse ($campuses as $campus)
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" wire:model="selectedCampuses" value="{{ $campus->id }}"
                                                class="rounded border-gray-300">
                                            {{ $campus->name }}
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-400">No campuses available yet.</p>
                                    @endforelse
                                </div>
                                @error('selectedCampuses') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                <span wire:loading.remove wire:target="save">Save User</span>
                                <span wire:loading wire:target="save">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>