<?php

namespace App\Exports;

use App\Models\Campus;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateExport implements Export, WithMultipleSheets
{
    use Exportable;

    protected bool $isManager;

    protected int $organizationId;

    public function __construct(bool $isManager, int $organizationId)
    {
        $this->isManager = $isManager;
        $this->organizationId = $organizationId;
    }

    public function sheets(): array
    {
        $sheets = [
            'Student Template' => new StudentTemplateDataSheet($this->isManager),
        ];

        if ($this->isManager) {
            $sheets['Campus Codes'] = new StudentTemplateCampusSheet($this->organizationId);
        }

        return $sheets;
    }
}

class StudentTemplateDataSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected bool $isManager;

    public function __construct(bool $isManager)
    {
        $this->isManager = $isManager;
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

    /**
     * Admission Number left blank on purpose: shows that it auto-generates
     * if omitted.
     */
    public function array(): array
    {
        $example = [
            '', 'Jane', 'Doe', '2015-03-14', 'female',
            now()->format('Y-m-d'), 'Grade 1', 'A',
            'John Doe', '03001234567', 'guardian@example.com', '123 Main St',
        ];

        if ($this->isManager) {
            $example[] = 'MAIN';
        }

        return [$example];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class StudentTemplateCampusSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected int $organizationId;

    public function __construct(int $organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function headings(): array
    {
        return ['Campus Code', 'Campus Name'];
    }

    public function array(): array
    {
        return Campus::where('organization_id', $this->organizationId)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn ($campus) => [$campus->code, $campus->name])
            ->toArray();
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}