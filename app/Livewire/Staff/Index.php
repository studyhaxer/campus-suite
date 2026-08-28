<?php

namespace App\Livewire\Staff;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')] class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public ?int $editingProfileId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public string $designation = '';
    public string $department = '';
    public string $joining_date = '';
    public string $base_salary = '';
    public string $employment_status = 'active';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function assignableRoles(): array
    {
        return auth()->user()->hasRole('Manager')
            ? ['Campus Admin', 'Teacher', 'Accountant']
            : ['Teacher', 'Accountant'];
    }

    protected function rules(): array
    {
        $userId = $this->editingProfileId
            ? StaffProfile::find($this->editingProfileId)?->user_id
            : null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $this->editingProfileId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'role' => ['required', Rule::in($this->assignableRoles())],
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'base_salary' => 'nullable|numeric|min:0',
            'employment_status' => 'required|in:active,on_leave,terminated',
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', StaffProfile::class);
    }

    public function render()
    {
        $staff = StaffProfile::with('user.roles')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('employment_status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.staff.index', [
            'staff' => $staff,
            'assignableRoles' => $this->assignableRoles(),
        ]);
    }

    public function openCreate(): void
    {
        $this->authorize('create', StaffProfile::class);
        $this->reset([
            'editingProfileId', 'name', 'email', 'password', 'password_confirmation',
            'role', 'designation', 'department', 'base_salary',
        ]);
        $this->joining_date = now()->format('Y-m-d');
        $this->employment_status = 'active';
        $this->showModal = true;
    }

    public function openEdit(int $profileId): void
    {
        $profile = StaffProfile::with('user.roles')->findOrFail($profileId);
        $this->authorize('update', $profile);

        $this->editingProfileId = $profile->id;
        $this->name = $profile->user->name;
        $this->email = $profile->user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = $profile->user->roles->first()?->name ?? '';
        $this->designation = $profile->designation;
        $this->department = $profile->department ?? '';
        $this->joining_date = $profile->joining_date->format('Y-m-d');
        $this->base_salary = $profile->base_salary !== null ? (string) $profile->base_salary : '';
        $this->employment_status = $profile->employment_status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingProfileId) {
            $profile = StaffProfile::findOrFail($this->editingProfileId);
            $this->authorize('update', $profile);
            $user = $profile->user;

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                ...(filled($data['password']) ? ['password' => Hash::make($data['password'])] : []),
            ]);
            $user->syncRoles([$data['role']]);

            $profile->update([
                'designation' => $data['designation'],
                'department' => $data['department'],
                'joining_date' => $data['joining_date'],
                'base_salary' => $data['base_salary'] !== '' ? $data['base_salary'] : null,
                'employment_status' => $data['employment_status'],
            ]);
        } else {
            $this->authorize('create', StaffProfile::class);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'organization_id' => auth()->user()->organization_id,
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$data['role']]);

            $profile = StaffProfile::create([
                'user_id' => $user->id,
                'designation' => $data['designation'],
                'department' => $data['department'],
                'joining_date' => $data['joining_date'],
                'base_salary' => $data['base_salary'] !== '' ? $data['base_salary'] : null,
                'employment_status' => $data['employment_status'],
            ]);
        }

        // Keep the campus_user pivot in sync with the profile's one campus,
        // so existing role/campus-scoped checks (CampusSwitcher etc.) keep working.
        $user->campuses()->sync([$profile->campus_id]);

        $this->showModal = false;
        session()->flash('status', 'Staff member saved successfully.');
    }

    public function toggleStatus(int $profileId): void
    {
        $profile = StaffProfile::findOrFail($profileId);
        $this->authorize('update', $profile);
        $profile->update([
            'employment_status' => $profile->employment_status === 'terminated' ? 'active' : 'terminated',
        ]);
    }
}