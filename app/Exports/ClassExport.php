<?php

namespace App\Exports;

use App\Models\Campus;
use App\Models\SchoolClass;
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
 * Exports the ACTUAL current Class + Section records (not a blank template).
 * One row per Section, class fields repeated per row — same column layout
 * as ClassTemplateExport so the downloaded file can be edited and
 * re-imported through ClassImport unchanged.
 *
 * Classes with no sections yet still get one row, with the section columns
 * left blank, so they aren't silently dropped from the export.
 */
class ClassExport implements Export, FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = SchoolClass::query()
            ->with(['sections' => fn ($q) => $q->orderBy('name'), 'campus'])
            ->whereHas('campus', fn ($q) => $q->where('organization_id', $this->organizationId));

        if (! $this->isManager) {
            $query->where('campus_id', $this->lockedCampusId);
        }

        $rows = collect();

        foreach ($query->orderBy('sort_order')->orderBy('name')->get() as $schoolClass) {
            if ($schoolClass->sections->isEmpty()) {
                $rows->push([
                    'schoolClass' => $schoolClass,
                    'section' => null,
                ]);

                continue;
            }

            foreach ($schoolClass->sections as $section) {
                $rows->push([
                    'schoolClass' => $schoolClass,
                    'section' => $section,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = [
            'Class Name', 'Sort Order', 'Monthly Fee', 'Class Active',
            'Section Name', 'Section Capacity', 'Section Active',
        ];

        if ($this->isManager) {
            $headings[] = 'Campus Code';
        }

        return $headings;
    }

    public function map($row): array
    {
        $schoolClass = $row['schoolClass'];
        $section = $row['section'];

        $mapped = [
            $schoolClass->name,
            $schoolClass->sort_order,
            $schoolClass->monthly_fee,
            $schoolClass->is_active ? 'Yes' : 'No',
            $section?->name,
            $section?->capacity,
            $section ? ($section->is_active ? 'Yes' : 'No') : null,
        ];

        if ($this->isManager) {
            $mapped[] = $schoolClass->campus?->code;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}