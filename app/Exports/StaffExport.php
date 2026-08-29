<?php

namespace App\Exports;

use App\Models\StaffProfile;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffExport implements Export, FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected string $search;

    protected string $statusFilter;

    public function __construct(string $search = '', string $statusFilter = '')
    {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
    }

    public function collection(): Enumerable
    {
        return StaffProfile::with('user.roles')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('employment_status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Role', 'Designation', 'Department',
            'Joining Date', 'Base Salary', 'Employment Status',
        ];
    }

    public function map($profile): array
    {
        return [
            $profile->user->name,
            $profile->user->email,
            $profile->user->roles->first()?->name ?? '',
            $profile->designation,
            $profile->department ?? '',
            $profile->joining_date?->format('Y-m-d'),
            $profile->base_salary,
            $profile->employment_status,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}