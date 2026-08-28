<?php

namespace App\Livewire\Academics;

use App\Models\ClassSection;
use App\Models\SchoolClass;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] class ClassSections extends Component
{
    // Class modal
    public bool $showClassModal = false;
    public ?int $editingClassId = null;
    public string $className = '';
    public int $classSortOrder = 0;

    // Section modal
    public bool $showSectionModal = false;
    public ?int $editingSectionId = null;
    public ?int $sectionClassId = null;
    public string $sectionName = '';
    public ?int $sectionCapacity = null;

    public function mount(): void
    {
        $this->authorize('viewAny', SchoolClass::class);
    }

    public function render()
    {
        $classes = SchoolClass::with(['sections' => fn ($q) => $q->orderBy('name')])
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.academics.class-sections', ['classes' => $classes]);
    }

    // --- Class CRUD ---
    public function openCreateClass(): void
    {
        $this->authorize('create', SchoolClass::class);
        $this->reset(['editingClassId', 'className', 'classSortOrder']);
        $this->showClassModal = true;
    }

    public function openEditClass(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $this->authorize('update', $class);
        $this->editingClassId = $class->id;
        $this->className = $class->name;
        $this->classSortOrder = $class->sort_order;
        $this->showClassModal = true;
    }

    public function saveClass(): void
    {
        $data = $this->validate([
            'className' => 'required|string|max:255',
            'classSortOrder' => 'nullable|integer|min:0',
        ]);

        if ($this->editingClassId) {
            $class = SchoolClass::findOrFail($this->editingClassId);
            $this->authorize('update', $class);
            $class->update(['name' => $data['className'], 'sort_order' => $data['classSortOrder'] ?? 0]);
        } else {
            $this->authorize('create', SchoolClass::class);
            SchoolClass::create(['name' => $data['className'], 'sort_order' => $data['classSortOrder'] ?? 0]);
        }

        $this->showClassModal = false;
        session()->flash('status', 'Class saved successfully.');
    }

    public function toggleClassActive(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $this->authorize('update', $class);
        $class->update(['is_active' => ! $class->is_active]);
    }

    // --- Section CRUD ---
    public function openCreateSection(int $classId): void
    {
        $this->authorize('create', ClassSection::class);
        $this->reset(['editingSectionId', 'sectionName', 'sectionCapacity']);
        $this->sectionClassId = $classId;
        $this->showSectionModal = true;
    }

    public function openEditSection(int $id): void
    {
        $section = ClassSection::findOrFail($id);
        $this->authorize('update', $section);
        $this->editingSectionId = $section->id;
        $this->sectionClassId = $section->school_class_id;
        $this->sectionName = $section->name;
        $this->sectionCapacity = $section->capacity;
        $this->showSectionModal = true;
    }

    public function saveSection(): void
    {
        $data = $this->validate([
            'sectionName' => 'required|string|max:255',
            'sectionCapacity' => 'nullable|integer|min:1',
        ]);

        if ($this->editingSectionId) {
            $section = ClassSection::findOrFail($this->editingSectionId);
            $this->authorize('update', $section);
            $section->update(['name' => $data['sectionName'], 'capacity' => $data['sectionCapacity']]);
        } else {
            $this->authorize('create', ClassSection::class);
            ClassSection::create([
                'school_class_id' => $this->sectionClassId,
                'name' => $data['sectionName'],
                'capacity' => $data['sectionCapacity'],
            ]);
        }

        $this->showSectionModal = false;
        session()->flash('status', 'Section saved successfully.');
    }

    public function toggleSectionActive(int $id): void
    {
        $section = ClassSection::findOrFail($id);
        $this->authorize('update', $section);
        $section->update(['is_active' => ! $section->is_active]);
    }
}