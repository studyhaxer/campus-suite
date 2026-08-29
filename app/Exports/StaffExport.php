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

class StaffTemplateExport implements Export, WithMultipleSheets
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
            'Staff Template' => new StaffTemplateDataSheet($this->isManager),
        ];

        if ($this->isManager) {
            $sheets['Campus Codes'] = new StaffTemplateCampusSheet($this->organizationId);
        }

        return $sheets;
    }
}

class StaffTemplateDataSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected bool $isManager;

    public function __construct(bool $isManager)
    {
        $this->isManager = $isManager;
    }

    public function headings(): array
    {
        $headings = [
            'Name', 'Email', 'Role', 'Designation', 'Department',
            'Joining Date', 'Base Salary', 'Employment Status',
        ];

        if ($this->isManager) {
            $headings[] = 'Campus Code';
        }

        return $headings;
    }

    public function array(): array
    {
        $example = [
            'Jane Doe', 'jane.doe@example.com', 'Teacher', 'Senior Math Teacher', 'Academics',
            now()->format('Y-m-d'), '50000', 'active',
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

class StaffTemplateCampusSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
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