<?php

namespace App\Livewire\Campuses;

use App\Models\Campus;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingCampusId = null;
    public string $name = '';
    public string $address = '';
    public string $city = '';
    public string $region = '';
    public string $contact_phone = '';
    public string $contact_email = '';
    public string $timezone = 'UTC';
    public string $currency = 'USD';
    public string $academic_year = '';
    public string $pay_cycle = 'monthly';
    protected function rules(): array
    {
        return ['name' => 'required|string|max:255', 'address' => 'nullable|string|max:255', 'city' => 'nullable|string|max:255', 'region' => 'nullable|string|max:255', 'contact_phone' => 'nullable|string|max:50', 'contact_email' => 'nullable|email|max:255', 'timezone' => 'required|string|max:100', 'currency' => 'required|string|size:3', 'academic_year' => 'nullable|string|max:20', 'pay_cycle' => 'required|in:monthly,biweekly,weekly',];
    }
    public function mount(): void
    {
        $this->authorize('viewAny', Campus::class);
    }
    public function openCreate(): void
    {
        $this->authorize('create', Campus::class);
        $this->reset(['editingCampusId', 'name', 'address', 'city', 'region', 'contact_phone', 'contact_email', 'academic_year']);
        $this->timezone = 'UTC';
        $this->currency = 'USD';
        $this->pay_cycle = 'monthly';
        $this->showModal = true;
    }
    public function openEdit(int $campusId): void
    {
        $campus = Campus::findOrFail($campusId);
        $this->authorize('update', $campus);
        $this->editingCampusId = $campus->id;
        $this->name = $campus->name;
        $this->address = $campus->address ?? '';
        $this->city = $campus->city ?? '';
        $this->region = $campus->region ?? '';
        $this->contact_phone = $campus->contact_phone ?? '';
        $this->contact_email = $campus->contact_email ?? '';
        $this->timezone = $campus->timezone;
        $this->currency = $campus->currency;
        $this->academic_year = $campus->academic_year ?? '';
        $this->pay_cycle = $campus->pay_cycle;
        $this->showModal = true;
    }
    public function save(): void
    {
        $data = $this->validate();
        $user = auth()->user();
        if ($this->editingCampusId) {
            $campus = Campus::findOrFail($this->editingCampusId);
            $this->authorize('update', $campus);
            $campus->update($data);
        } else {
            $this->authorize('create', Campus::class);
            $data['organization_id'] = $user->organization_id;
            $data['code'] = $this->generateUniqueCode($this->name);
            $data['is_active'] = true;
            Campus::create($data);
        }
        $this->showModal = false;
        session()->flash('status', 'Campus saved successfully.');
    }
    public function toggleActive(int $campusId): void
    {
        $campus = Campus::findOrFail($campusId);
        $this->authorize('update', $campus);
        $campus->update(['is_active' => ! $campus->is_active]);
    }
    public function delete(int $campusId): void
    {
        $campus = Campus::findOrFail($campusId);
        $this->authorize('delete', $campus);

        DB::transaction(function () use ($campus) {
            // staff_profiles.user_id is unique, so each of these users has exactly
            // one profile — the one about to cascade away with this campus.
            $staffUserIds = StaffProfile::where('campus_id', $campus->id)->pluck('user_id');

            // Only delete the User account if this was their only campus assignment.
            // The campus_user pivot (multi-campus assignment for non-Manager roles)
            // is checked here, before the campus row (and its pivot rows) disappear.
            $deletableUserIds = collect();

            if ($staffUserIds->isNotEmpty()) {
                if (Schema::hasTable('campus_user')) {
                    $deletableUserIds = $staffUserIds->filter(function ($userId) use ($campus) {
                        return ! DB::table('campus_user')
                            ->where('user_id', $userId)
                            ->where('campus_id', '!=', $campus->id)
                            ->exists();
                    });
                } else {
                    // No multi-campus pivot to check against — this is their only campus.
                    $deletableUserIds = $staffUserIds;
                }
            }

            // Never delete yourself this way, and never delete a Manager
            // (Managers aren't scoped to a single campus).
            $deletableUserIds = $deletableUserIds
                ->reject(fn ($id) => $id === auth()->id())
                ->filter(fn ($id) => ! (User::find($id)?->hasRole('Manager')));

            // Deleting the campus cascades students, school_classes, class_sections,
            // and staff_profiles at the DB level (cascadeOnDelete on campus_id).
            $campus->delete();

            // Remove the now-orphaned staff accounts whose only campus was this one.
            if ($deletableUserIds->isNotEmpty()) {
                User::whereIn('id', $deletableUserIds)->delete();
            }
        });

        session()->flash('status', 'Campus and its related records deleted successfully.');
    }
    protected function generateUniqueCode(string $name): string
    {
        $base = strtoupper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $base = $base ?: 'CMP';
        do {
            $code = $base . rand(100, 999);
        } while (Campus::where('code', $code)->exists());
        return $code;
    }
    public function render()
    {
        $campuses = Campus::where('organization_id', auth()->user()->organization_id)->orderBy('name')->get();
        return view('livewire.campuses.index', ['campuses' => $campuses]);
    }
}