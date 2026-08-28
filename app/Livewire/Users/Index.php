<?php

namespace App\Livewire\Users;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')] class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public array $selectedCampuses = [];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'role' => 'required|in:Manager,Campus Admin,Teacher,Accountant',
            'selectedCampuses' => 'array',
            'selectedCampuses.*' => 'exists:campuses,id',
        ];

        // Password required only when creating; optional (but validated) when editing.
        $rules['password'] = $this->editingUserId
            ? 'nullable|min:8|confirmed'
            : 'required|min:8|confirmed';

        return $rules;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function render()
    {
        $users = User::where('organization_id', auth()->user()->organization_id)
            ->with('roles', 'campuses')
            ->orderBy('name')
            ->paginate(15);

        $campuses = Campus::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get();

        return view('livewire.users.index', compact('users', 'campuses'));
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'role', 'selectedCampuses']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = $user->roles->first()?->name ?? '';
        $this->selectedCampuses = $user->campuses->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $this->authorize('update', $user);

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                ...(filled($data['password']) ? ['password' => Hash::make($data['password'])] : []),
            ]);
        } else {
            $this->authorize('create', User::class);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'organization_id' => auth()->user()->organization_id,
                'email_verified_at' => now(),
            ]);
        }

        $user->syncRoles([$data['role']]);

        // Manager sees every campus in their organization via role check,
        // so explicit campus assignment only matters for the other roles.
        $user->campuses()->sync($data['role'] === 'Manager' ? [] : $data['selectedCampuses']);

        $this->showModal = false;
        session()->flash('status', 'User saved successfully.');
    }
}