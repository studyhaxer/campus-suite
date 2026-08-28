<?php

namespace App\Livewire\Attendance;

use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] class Staff extends Component
{
    public string $date = '';
    public array $statuses = []; // user_id => status
    public bool $loaded = false;

    public function mount(): void
    {
        $this->authorize('viewAny', StaffAttendance::class);
        $this->date = now()->format('Y-m-d');
        $this->loadAttendance();
    }

    public function render()
    {
        $staff = StaffProfile::with('user')
            ->where('employment_status', '!=', 'terminated')
            ->get();

        return view('livewire.attendance.staff', compact('staff'));
    }

    public function updatedDate(): void
    {
        $this->loadAttendance();
    }

    public function loadAttendance(): void
    {
        $this->statuses = [];

        $staff = StaffProfile::where('employment_status', '!=', 'terminated')->get();

        $existing = StaffAttendance::where('date', $this->date)->pluck('status', 'user_id');

        foreach ($staff as $profile) {
            $this->statuses[$profile->user_id] = $existing[$profile->user_id] ?? 'present';
        }

        $this->loaded = true;
    }

    public function markAll(string $status): void
    {
        foreach ($this->statuses as $userId => $current) {
            $this->statuses[$userId] = $status;
        }
    }

    public function save(): void
    {
        $this->authorize('create', StaffAttendance::class);

        foreach ($this->statuses as $userId => $status) {
            StaffAttendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $this->date],
                [
                    'status' => $status,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        session()->flash('status', 'Staff attendance saved successfully.');
    }
}