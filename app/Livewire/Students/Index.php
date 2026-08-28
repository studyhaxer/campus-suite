<?php

namespace App\Livewire\Students;

use App\Models\ClassSection;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')] class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $classSectionFilter = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public ?int $editingStudentId = null;

    public string $admission_number = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $admission_date = '';
    public string $class_section_id = '';
    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_email = '';
    public string $address = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingClassSectionFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'admission_number' => [
                'required', 'string', 'max:50',
                Rule::unique('students', 'admission_number')
                    ->where(fn ($q) => $q->where('campus_id', session('current_campus_id')))
                    ->ignore($this->editingStudentId),
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'admission_date' => 'required|date',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Student::class);
    }

    public function render()
    {
        $students = Student::with('classSection.schoolClass')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('admission_number', 'like', "%{$this->search}%")
            ))
            ->when($this->classSectionFilter, fn ($q) => $q->where('class_section_id', $this->classSectionFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('first_name')
            ->paginate(15);

        $classSections = ClassSection::with('schoolClass')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => $s->schoolClass->sort_order . $s->name);

        return view('livewire.students.index', compact('students', 'classSections'));
    }

    public function openCreate(): void
    {
        $this->authorize('create', Student::class);
        $this->reset([
            'editingStudentId', 'first_name', 'last_name', 'date_of_birth', 'gender',
            'class_section_id', 'guardian_name', 'guardian_phone', 'guardian_email', 'address',
        ]);
        $this->admission_number = $this->generateAdmissionNumber();
        $this->admission_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $student = Student::findOrFail($id);
        $this->authorize('update', $student);

        $this->editingStudentId = $student->id;
        $this->admission_number = $student->admission_number;
        $this->first_name = $student->first_name;
        $this->last_name = $student->last_name;
        $this->date_of_birth = optional($student->date_of_birth)->format('Y-m-d') ?? '';
        $this->gender = $student->gender ?? '';
        $this->admission_date = $student->admission_date->format('Y-m-d');
        $this->class_section_id = (string) ($student->class_section_id ?? '');
        $this->guardian_name = $student->guardian_name ?? '';
        $this->guardian_phone = $student->guardian_phone ?? '';
        $this->guardian_email = $student->guardian_email ?? '';
        $this->address = $student->address ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['class_section_id'] = $data['class_section_id'] ?: null;
        $data['gender'] = $data['gender'] ?: null;

        if ($this->editingStudentId) {
            $student = Student::findOrFail($this->editingStudentId);
            $this->authorize('update', $student);
            $student->update($data);
        } else {
            $this->authorize('create', Student::class);
            $data['status'] = 'active';
            Student::create($data);
        }

        $this->showModal = false;
        session()->flash('status', 'Student saved successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $student = Student::findOrFail($id);
        $this->authorize('update', $student);
        $student->update(['status' => $student->status === 'active' ? 'inactive' : 'active']);
    }

    protected function generateAdmissionNumber(): string
    {
        $campus = \App\Models\Campus::find(session('current_campus_id'));
        $prefix = ($campus->code ?? 'STU') . '-' . now()->format('Y') . '-';
        $count = Student::withoutGlobalScopes()->where('campus_id', session('current_campus_id'))->count();

        do {
            $count++;
            $candidate = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        } while (Student::withoutGlobalScopes()->where('campus_id', session('current_campus_id'))->where('admission_number', $candidate)->exists());

        return $candidate;
    }
}