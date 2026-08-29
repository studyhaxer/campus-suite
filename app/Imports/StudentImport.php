<?php

namespace App\Imports;

use App\Models\Campus;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

/**
 * Same shape as StaffImport / ClassImport: wrapper routes sheet 0 (the real
 * data) to the row importer, leaving a manager-only "Campus Codes" lookup
 * sheet untouched.
 *
 * Class/Section are resolved by NAME (not ID) so the sheet stays readable —
 * "Grade 1" / "A" rather than raw foreign keys. Admission Number is
 * optional: leave it blank and a number is generated using the same
 * campus-code + year + sequence scheme as the "Add Student" button
 * (see Livewire\Students\Index::generateAdmissionNumber). If an admission
 * number IS supplied and already exists for that campus, the existing
 * student is updated instead of duplicated.
 *
 * NOTE: because a Manager can import rows targeting several campuses in one
 * file, all lookups here run withoutGlobalScopes() — Student/SchoolClass/
 * ClassSection appear to scope themselves to session('current_campus_id')
 * via BelongsToCampus, which would otherwise hide rows for every campus
 * except whichever one is "current" in the browser session doing the
 * upload.
 */
class StudentImport implements Import, WithMultipleSheets
{
    protected StudentDataSheetImport $dataSheetImport;

    public function __construct(int $organizationId, bool $isManager, ?int $lockedCampusId)
    {
        $this->dataSheetImport = new StudentDataSheetImport($organizationId, $isManager, $lockedCampusId);
    }

    public function sheets(): array
    {
        return [
            0 => $this->dataSheetImport,
        ];
    }

    public function importedCount(): int
    {
        return $this->dataSheetImport->importedCount();
    }

    /** @return array<int, string> */
    public function errorMessages(): array
    {
        return $this->dataSheetImport->errorMessages();
    }
}

class StudentDataSheetImport implements OnEachRow, SkipsOnError, SkipsOnFailure, WithHeadingRow, WithValidation
{
    protected int $organizationId;

    protected bool $isManager;

    protected ?int $lockedCampusId;

    protected int $importedCount = 0;

    /** @var array<int, string> */
    protected array $errorMessages = [];

    public function __construct(int $organizationId, bool $isManager, ?int $lockedCampusId)
    {
        $this->organizationId = $organizationId;
        $this->isManager = $isManager;
        $this->lockedCampusId = $lockedCampusId;
    }

    public function rules(): array
    {
        $rules = [
            'admission_number' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'admission_date' => 'nullable|date',
            'class_name' => 'nullable|string|max:255|required_with:section_name',
            'section_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ];

        if ($this->isManager) {
            $rules['campus_code'] = [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    $exists = Campus::where('organization_id', $this->organizationId)
                        ->where('code', $value)
                        ->exists();

                    if (! $exists) {
                        $fail("Campus code \"{$value}\" does not match any campus in your organization.");
                    }
                },
            ];
        }

        return $rules;
    }

    public function customValidationMessages(): array
    {
        return [
            'gender.in' => 'Gender must be male, female, or other.',
            'class_name.required_with' => 'Class Name is required when Section Name is given.',
        ];
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $campusId = $this->isManager
            ? Campus::where('organization_id', $this->organizationId)
                ->where('code', $data['campus_code'])
                ->value('id')
            : $this->lockedCampusId;

        if (! $campusId) {
            $this->errorMessages[] = "Row {$row->getIndex()}: could not resolve a campus, row skipped.";

            return;
        }

        $classSectionId = null;

        if (Str::of((string) ($data['class_name'] ?? ''))->trim()->isNotEmpty()) {
            $schoolClass = SchoolClass::withoutGlobalScopes()
                ->where('campus_id', $campusId)
                ->where('name', $data['class_name'])
                ->first();

            if (! $schoolClass) {
                $this->errorMessages[] = "Row {$row->getIndex()}: class \"{$data['class_name']}\" not found for this campus, row skipped.";

                return;
            }

            if (Str::of((string) ($data['section_name'] ?? ''))->trim()->isNotEmpty()) {
                $section = ClassSection::withoutGlobalScopes()
                    ->where('school_class_id', $schoolClass->id)
                    ->where('name', $data['section_name'])
                    ->first();

                if (! $section) {
                    $this->errorMessages[] = "Row {$row->getIndex()}: section \"{$data['section_name']}\" not found under class \"{$data['class_name']}\", row skipped.";

                    return;
                }

                $classSectionId = $section->id;
            }
        }

        $admissionNumber = trim((string) ($data['admission_number'] ?? ''));
        $existing = null;

        if ($admissionNumber !== '') {
            $existing = Student::withoutGlobalScopes()
                ->where('campus_id', $campusId)
                ->where('admission_number', $admissionNumber)
                ->first();
        } else {
            $admissionNumber = $this->generateAdmissionNumber($campusId);
        }

        $payload = [
            'campus_id' => $campusId,
            'class_section_id' => $classSectionId,
            'admission_number' => $admissionNumber,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => Str::of((string) ($data['date_of_birth'] ?? ''))->trim()->isNotEmpty()
                ? $data['date_of_birth']
                : null,
            'gender' => $data['gender'] ?: null,
            'admission_date' => Str::of((string) ($data['admission_date'] ?? ''))->trim()->isNotEmpty()
                ? $data['admission_date']
                : now()->format('Y-m-d'),
            'guardian_name' => $data['guardian_name'] ?? null,
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'guardian_email' => $data['guardian_email'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if ($existing) {
            $existing->update($payload);
        } else {
            $payload['status'] = 'active';
            Student::create($payload);
        }

        $this->importedCount++;
    }

    /**
     * Same scheme as Livewire\Students\Index::generateAdmissionNumber(),
     * parameterized by campus instead of reading session('current_campus_id')
     * — a Manager's import can touch campuses other than whichever one is
     * "current" in their browser session.
     */
    protected function generateAdmissionNumber(int $campusId): string
    {
        $campus = Campus::find($campusId);
        $prefix = ($campus->code ?? 'STU') . '-' . now()->format('Y') . '-';
        $count = Student::withoutGlobalScopes()->where('campus_id', $campusId)->count();

        do {
            $count++;
            $candidate = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } while (
            Student::withoutGlobalScopes()
                ->where('campus_id', $campusId)
                ->where('admission_number', $candidate)
                ->exists()
        );

        return $candidate;
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $errors = implode(' ', $failure->errors());
            $this->errorMessages[] = "Row {$failure->row()}: {$errors}";
        }
    }

    public function onError(Throwable $e): void
    {
        $this->errorMessages[] = 'Unexpected error: ' . $e->getMessage();
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    /** @return array<int, string> */
    public function errorMessages(): array
    {
        return $this->errorMessages;
    }
}