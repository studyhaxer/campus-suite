<?php

namespace App\Livewire\Attendance;

use App\Models\ClassSection;
use App\Models\Student;
use App\Models\StudentAttendance;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] class Students extends Component
{
    public string $date = '';
    public string $classSectionId = '';
    public array $statuses = []; // student_id => 'present'|'absent'
    public bool $loaded = false;

    public function mount(): void
    {
        $this->authorize('viewAny', StudentAttendance::class);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $classSections = ClassSection::with('schoolClass')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => $s->schoolClass->sort_order . $s->name);

        $students = $this->classSectionId
            ? Student::where('class_section_id', $this->classSectionId)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get()
            : collect();

        return view('livewire.attendance.students', compact('classSections', 'students'));
    }

    public function updatedClassSectionId(): void
    {
        $this->loadAttendance();
    }

    public function updatedDate(): void
    {
        $this->loadAttendance();
    }

    public function loadAttendance(): void
    {
        $this->statuses = [];
        $this->loaded = false;

        if (! $this->classSectionId || ! $this->date) {
            return;
        }

        $students = Student::where('class_section_id', $this->classSectionId)
            ->where('status', 'active')
            ->get();

        $existing = StudentAttendance::where('class_section_id', $this->classSectionId)
            ->where('date', $this->date)
            ->pluck('status', 'student_id');

        foreach ($students as $student) {
            $this->statuses[$student->id] = $existing[$student->id] ?? 'present';
        }

        $this->loaded = true;
    }

    public function markAll(string $status): void
    {
        foreach ($this->statuses as $studentId => $current) {
            $this->statuses[$studentId] = $status;
        }
    }

    public function save(): void
    {
        $this->authorize('create', StudentAttendance::class);

        if (! $this->classSectionId || ! $this->date) {
            return;
        }

        foreach ($this->statuses as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $this->date],
                [
                    'class_section_id' => $this->classSectionId,
                    'status' => $status,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        session()->flash('status', 'Attendance saved successfully.');
    }
}