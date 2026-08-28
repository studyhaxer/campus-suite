<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Classes & Sections</h2>
                @can('create', \App\Models\SchoolClass::class)
                    <button wire:click="openCreateClass" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        + Add Class
                    </button>
                @endcan
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-md">{{ session('status') }}</div>
            @endif

            <div class="space-y-4">
                @forelse ($classes as $class)
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden" wire:key="class-{{ $class->id }}">
                        <div class="flex justify-between items-center px-6 py-4 border-b">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-900">{{ $class->name }}</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $class->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="text-sm space-x-3">
                                @can('create', \App\Models\ClassSection::class)
                                    <button wire:click="openCreateSection({{ $class->id }})" class="text-indigo-600 hover:text-indigo-800">+ Add Section</button>
                                @endcan
                                @can('update', $class)
                                    <button wire:click="openEditClass({{ $class->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                    <button wire:click="toggleClassActive({{ $class->id }})" wire:confirm="Are you sure?" class="text-gray-500 hover:text-gray-700">
                                        {{ $class->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="divide-y">
                            @forelse ($class->sections as $section)
                                <div class="flex justify-between items-center px-6 py-3" wire:key="section-{{ $section->id }}">
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-gray-700">Section {{ $section->name }}</span>
                                        @if ($section->capacity)
                                            <span class="text-gray-400">· cap {{ $section->capacity }}</span>
                                        @endif
                                        <span class="px-2 py-0.5 text-xs rounded-full {{ $section->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $section->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    @can('update', $section)
                                        <div class="text-sm space-x-3">
                                            <button wire:click="openEditSection({{ $section->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                            <button wire:click="toggleSectionActive({{ $section->id }})" wire:confirm="Are you sure?" class="text-gray-500 hover:text-gray-700">
                                                {{ $section->is_active ? 'Deactivate' : 'Reactivate' }}
                                            </button>
                                        </div>
                                    @endcan
                                </div>
                            @empty
                                <div class="px-6 py-4 text-sm text-gray-400">No sections yet.</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-sm rounded-lg px-6 py-8 text-center text-sm text-gray-500">
                        No classes yet. Click "Add Class" to create your first one.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- Class Modal --}}
    @if ($showClassModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showClassModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $editingClassId ? 'Edit Class' : 'Add Class' }}</h3>
                    <form wire:submit="saveClass" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Class Name</label>
                            <input type="text" wire:model="className" placeholder="e.g. Grade 5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('className') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <input type="number" wire:model="classSortOrder" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showClassModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Section Modal --}}
    @if ($showSectionModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showSectionModal', false)">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $editingSectionId ? 'Edit Section' : 'Add Section' }}</h3>
                    <form wire:submit="saveSection" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Section Name</label>
                            <input type="text" wire:model="sectionName" placeholder="e.g. A" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('sectionName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input type="number" wire:model="sectionCapacity" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('sectionCapacity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" wire:click="$set('showSectionModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>