<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports the ACTUAL current Student records — same column layout as
 * StudentTemplateExport so the file can be edited and re-imported through
 * StudentImport unchanged (matched back up by Admission Number).
 *
 * Uses withoutGlobalScopes() for the same reason as StudentImport: a
 * Manager's export can span every campus in the organization, not just
 * whichever one is "current" in session.
 */
class StudentExport implements Export, FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected bool $isManager;

    protected int $organizationId;

    protected ?int $lockedCampusId;

    public function __construct(bool $isManager, int $organizationId, ?int $lockedCampusId)
    {
        $this->isManager = $isManager;
        $this->organizationId = $organizationId;
        $this->lockedCampusId = $lockedCampusId;
    }

    public function collection(): Collection
    {
        $query = Student::withoutGlobalScopes()
            ->with(['classSection.schoolClass', 'campus'])
            ->whereHas('campus', fn ($q) => $q->where('organization_id', $this->organizationId));

        if (! $this->isManager) {
            $query->where('campus_id', $this->lockedCampusId);
        }

        return $query->orderBy('first_name')->orderBy('last_name')->get();
    }

    public function headings(): array
    {
        $headings = [
            'Admission Number', 'First Name', 'Last Name', 'Date of Birth', 'Gender',
            'Admission Date', 'Class Name', 'Section Name',
            'Guardian Name', 'Guardian Phone', 'Guardian Email', 'Address',
        ];

        if ($this->isManager) {
            $headings[] = 'Campus Code';
        }

        return $headings;
    }

    public function map($student): array
    {
        $mapped = [
            $student->admission_number,
            $student->first_name,
            $student->last_name,
            optional($student->date_of_birth)->format('Y-m-d'),
            $student->gender,
            optional($student->admission_date)->format('Y-m-d'),
            $student->classSection?->schoolClass?->name,
            $student->classSection?->name,
            $student->guardian_name,
            $student->guardian_phone,
            $student->guardian_email,
            $student->address,
        ];

        if ($this->isManager) {
            $mapped[] = $student->campus?->code;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}