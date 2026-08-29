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

class ClassTemplateExport implements Export, WithMultipleSheets
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
            'Class Template' => new ClassTemplateDataSheet($this->isManager),
        ];

        if ($this->isManager) {
            $sheets['Campus Codes'] = new ClassTemplateCampusSheet($this->organizationId);
        }

        return $sheets;
    }
}

class ClassTemplateDataSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected bool $isManager;

    public function __construct(bool $isManager)
    {
        $this->isManager = $isManager;
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

    /**
     * Two example rows show that repeating the class name groups sections
     * under the same class.
     */
    public function array(): array
    {
        $rowOne = ['Grade 1', '1', '5000', 'Yes', 'A', '30', 'Yes'];
        $rowTwo = ['Grade 1', '1', '5000', 'Yes', 'B', '30', 'Yes'];

        if ($this->isManager) {
            $rowOne[] = 'MAIN';
            $rowTwo[] = 'MAIN';
        }

        return [$rowOne, $rowTwo];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class ClassTemplateCampusSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
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