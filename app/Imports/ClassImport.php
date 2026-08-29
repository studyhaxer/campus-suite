<?php

namespace App\Imports;

use App\Models\Campus;
use App\Models\ClassSection;
use App\Models\SchoolClass;
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
 * Mirrors StaffImport: the template workbook (see ClassTemplateExport) has a
 * "Class Template" data sheet and, for managers, a read-only "Campus Codes"
 * lookup sheet. WithMultipleSheets routes only sheet 0 (the real data) to the
 * row importer so the Campus Codes sheet is left untouched.
 *
 * Each row represents one Section. Rows that share the same class_name (and
 * campus) are grouped under the same SchoolClass — the class fields (sort
 * order, monthly fee, active flag) are repeated on every row belonging to
 * that class and are upserted each time, so the last row processed for a
 * given class "wins" if values differ across its rows.
 */
class ClassImport implements Import, WithMultipleSheets
{
    protected ClassDataSheetImport $dataSheetImport;

    public function __construct(int $organizationId, bool $isManager, ?int $lockedCampusId)
    {
        $this->dataSheetImport = new ClassDataSheetImport($organizationId, $isManager, $lockedCampusId);
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

    public function importedClassCount(): int
    {
        return $this->dataSheetImport->importedClassCount();
    }

    /** @return array<int, string> */
    public function errorMessages(): array
    {
        return $this->dataSheetImport->errorMessages();
    }
}

class ClassDataSheetImport implements OnEachRow, SkipsOnError, SkipsOnFailure, WithHeadingRow, WithValidation
{
    protected int $organizationId;

    protected bool $isManager;

    protected ?int $lockedCampusId;

    protected int $importedCount = 0;

    /** @var array<int, true> distinct school_class IDs touched, keyed for uniqueness */
    protected array $touchedClassIds = [];

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
            'class_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'class_active' => 'nullable|in:yes,no,Yes,No,YES,NO,1,0',
            'section_name' => 'required|string|max:255',
            'section_capacity' => 'nullable|integer|min:0',
            'section_active' => 'nullable|in:yes,no,Yes,No,YES,NO,1,0',
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
            'class_active.in' => 'Class Active must be Yes or No.',
            'section_active.in' => 'Section Active must be Yes or No.',
        ];
    }

    protected function toBool(?string $value, bool $default = true): bool
    {
        if ($value === null || Str::of((string) $value)->trim()->isEmpty()) {
            return $default;
        }

        return in_array(strtolower(trim($value)), ['yes', '1', 'true'], true);
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

        $schoolClass = SchoolClass::firstOrNew([
            'campus_id' => $campusId,
            'name' => $data['class_name'],
        ]);

        if (isset($data['sort_order']) && Str::of((string) $data['sort_order'])->trim()->isNotEmpty()) {
            $schoolClass->sort_order = (int) $data['sort_order'];
        }

        if (Str::of((string) ($data['monthly_fee'] ?? ''))->trim()->isNotEmpty()) {
            $schoolClass->monthly_fee = $data['monthly_fee'];
        }

        $schoolClass->is_active = $this->toBool($data['class_active'] ?? null, true);
        $schoolClass->save();

        $this->touchedClassIds[$schoolClass->id] = true;

        ClassSection::updateOrCreate(
            [
                'school_class_id' => $schoolClass->id,
                'name' => $data['section_name'],
            ],
            [
                'campus_id' => $campusId,
                'capacity' => Str::of((string) ($data['section_capacity'] ?? ''))->trim()->isNotEmpty()
                    ? $data['section_capacity']
                    : null,
                'is_active' => $this->toBool($data['section_active'] ?? null, true),
            ]
        );

        $this->importedCount++;
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

    public function importedClassCount(): int
    {
        return count($this->touchedClassIds);
    }

    /** @return array<int, string> */
    public function errorMessages(): array
    {
        return $this->errorMessages;
    }
}