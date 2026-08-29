<?php

namespace App\Imports;

use App\Models\Campus;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;
use Maatwebsite\Excel\Concerns\Import;

/**
 * The staff template workbook (see StaffTemplateExport) has two sheets:
 * "Staff Template" (the actual data to import) and, for managers, "Campus
 * Codes" (a read-only reference lookup). Without WithMultipleSheets, Laravel
 * Excel applies the same import concerns to every sheet in the workbook, so
 * this wrapper routes only the first sheet (the real data) to the row
 * importer and leaves the Campus Codes sheet untouched.
 */
class StaffImport implements Import, WithMultipleSheets
{
    protected StaffDataSheetImport $dataSheetImport;

    public function __construct(int $organizationId, bool $isManager, ?int $lockedCampusId)
    {
        $this->dataSheetImport = new StaffDataSheetImport($organizationId, $isManager, $lockedCampusId);
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

class StaffDataSheetImport implements OnEachRow, SkipsOnError, SkipsOnFailure, WithHeadingRow, WithValidation
{
    /**
     * Fixed temporary password assigned to every staff account created via import.
     * Staff should change this after first login.
     */
    protected const DEFAULT_PASSWORD = '11223344';

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

    protected function assignableRoles(): array
    {
        return $this->isManager
            ? ['Campus Admin', 'Teacher', 'Accountant']
            : ['Teacher', 'Accountant'];
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', 'string', 'in:' . implode(',', $this->assignableRoles())],
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'base_salary' => 'nullable|numeric|min:0',
            'employment_status' => 'nullable|in:active,on_leave,terminated',
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
            'email.unique' => 'A user with this email already exists.',
            'role.in' => 'Role must be one of: ' . implode(', ', $this->assignableRoles()) . '.',
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

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'organization_id' => $this->organizationId,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$data['role']]);

        StaffProfile::create([
            'campus_id' => $campusId,
            'user_id' => $user->id,
            'designation' => $data['designation'],
            'department' => $data['department'] ?? null,
            'joining_date' => $data['joining_date'],
            'base_salary' => Str::of((string) ($data['base_salary'] ?? ''))->trim()->isNotEmpty()
                ? $data['base_salary']
                : null,
            'employment_status' => $data['employment_status'] ?: 'active',
        ]);

        $user->campuses()->sync([$campusId]);

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

    /** @return array<int, string> */
    public function errorMessages(): array
    {
        return $this->errorMessages;
    }
}