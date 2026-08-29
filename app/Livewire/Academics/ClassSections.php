<?php

namespace App\Livewire\Academics;

use App\Exports\ClassExport;
use App\Exports\ClassTemplateExport;
use App\Imports\ClassImport;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')] class ClassSections extends Component
{
    use WithFileUploads;

    // Class modal
    public bool $showClassModal = false;
    public ?int $editingClassId = null;
    public string $className = '';
    public int $classSortOrder = 0;
    public string $classMonthlyFee = '0';

    // Section modal
    public bool $showSectionModal = false;
    public ?int $editingSectionId = null;
    public ?int $sectionClassId = null;
    public string $sectionName = '';
    public ?int $sectionCapacity = null;

    // Import modal
    public bool $showImportModal = false;
    public $importFile;
    public ?int $importedSectionCount = null;
    public ?int $importedClassCount = null;
    public array $importErrors = [];

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
        $this->reset(['editingClassId', 'className', 'classSortOrder', 'classMonthlyFee']);
        $this->showClassModal = true;
    }

    public function openEditClass(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $this->authorize('update', $class);
        $this->editingClassId = $class->id;
        $this->className = $class->name;
        $this->classSortOrder = $class->sort_order;
        $this->classMonthlyFee = (string) $class->monthly_fee;
        $this->showClassModal = true;
    }

    public function saveClass(): void
    {
        $data = $this->validate([
            'className' => 'required|string|max:255',
            'classSortOrder' => 'nullable|integer|min:0',
            'classMonthlyFee' => 'nullable|numeric|min:0',
        ]);

        if ($this->editingClassId) {
            $class = SchoolClass::findOrFail($this->editingClassId);
            $this->authorize('update', $class);
            $class->update([
                'name' => $data['className'],
                'sort_order' => $data['classSortOrder'] ?? 0,
                'monthly_fee' => $data['classMonthlyFee'] ?? 0,
            ]);
        } else {
            $this->authorize('create', SchoolClass::class);
            SchoolClass::create([
                'name' => $data['className'],
                'sort_order' => $data['classSortOrder'] ?? 0,
                'monthly_fee' => $data['classMonthlyFee'] ?? 0,
            ]);
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

    public function deleteClass(int $id): void
    {
        $class = SchoolClass::withCount('sections')->findOrFail($id);
        $this->authorize('delete', $class);

        if ($class->sections_count > 0) {
            session()->flash('error', 'Remove all sections from this class before deleting it.');
            return;
        }

        $class->delete();
        session()->flash('status', 'Class deleted.');
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

    public function deleteSection(int $id): void
    {
        $section = ClassSection::findOrFail($id);
        $this->authorize('delete', $section);

        $section->delete();
        session()->flash('status', 'Section deleted.');
    }

    // --- Export / Template / Import ---
    public function exportClasses()
    {
        $this->authorize('viewAny', SchoolClass::class);
        [$isManager, $organizationId, $lockedCampusId] = $this->resolveScope();

        return (new ClassExport($isManager, $organizationId, $lockedCampusId))
            ->download('classes-export-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $this->authorize('create', SchoolClass::class);
        [$isManager, $organizationId, ] = $this->resolveScope();

        return (new ClassTemplateExport($isManager, $organizationId))
            ->download('class-import-template.xlsx');
    }

    public function openImport(): void
    {
        $this->authorize('create', SchoolClass::class);
        $this->reset(['importFile', 'importedSectionCount', 'importedClassCount', 'importErrors']);
        $this->showImportModal = true;
    }

    public function import(): void
    {
        $this->authorize('create', SchoolClass::class);

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        [$isManager, $organizationId, $lockedCampusId] = $this->resolveScope();

        $import = new ClassImport($organizationId, $isManager, $lockedCampusId);

        Excel::import($import, $this->importFile->getRealPath());

        $this->importedSectionCount = $import->importedCount();
        $this->importedClassCount = $import->importedClassCount();
        $this->importErrors = $import->errorMessages();
        $this->importFile = null;
    }

    /**
     * NOTE: mirrors the scope resolution the Staff feature uses
     * (organization_id directly on the user, Manager role via
     * hasRole(), and a campuses() pivot for non-managers). Adjust
     * here if Staff actually resolves this differently.
     *
     * @return array{0: bool, 1: int, 2: ?int} [isManager, organizationId, lockedCampusId]
     */
    protected function resolveScope(): array
    {
        $user = Auth::user();
        $isManager = $user->hasRole('Manager');

        return [
            $isManager,
            $user->organization_id,
            $isManager ? null : $user->campuses()->value('campuses.id'),
        ];
    }
}