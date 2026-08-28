<?php

namespace App\Livewire\Payroll;

use App\Models\Payslip;
use App\Models\StaffProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')] class Index extends Component
{
    use WithPagination;

    public string $month = '';
    public string $search = '';
    public string $statusFilter = '';

    public bool $showAdjustModal = false;
    public ?int $adjustingPayslipId = null;
    public string $adjustAmount = '0';
    public string $adjustNotes = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingMonth(): void { $this->resetPage(); }

    public function mount(): void
    {
        $this->authorize('viewAny', Payslip::class);
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        $monthDate = $this->month . '-01';

        $payslips = Payslip::with('staff.staffProfile')
            ->whereDate('month', $monthDate)
            ->when($this->search, fn ($q) => $q->whereHas('staff', fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.payroll.index', compact('payslips'));
    }

    public function generatePayslips(): void
    {
        $this->authorize('create', Payslip::class);

        $monthDate = $this->month . '-01';
        $isManager = auth()->user()->hasRole('Manager');

        $profiles = StaffProfile::with('user.roles')
            ->where('employment_status', '!=', 'terminated')
            ->get()
            ->when(! $isManager, fn ($collection) => $collection->reject(
                fn ($profile) => $profile->user->hasAnyRole(['Manager', 'Campus Admin'])
            ));

        $created = 0;

        foreach ($profiles as $profile) {
            $base = $profile->base_salary ?? 0;

            if ($base <= 0) {
                continue;
            }

            $payslip = Payslip::firstOrCreate(
                ['user_id' => $profile->user_id, 'month' => $monthDate],
                [
                    'campus_id' => $profile->campus_id,
                    'base_salary' => $base,
                    'adjustments' => 0,
                    'net_amount' => $base,
                    'status' => 'draft',
                ]
            );

            if ($payslip->wasRecentlyCreated) {
                $created++;
            }
        }

        session()->flash('status', "{$created} payslip(s) generated for " . date('F Y', strtotime($monthDate)) . '.');
    }

    public function openAdjust(int $payslipId): void
    {
        $payslip = Payslip::findOrFail($payslipId);
        $this->authorize('update', $payslip);

        if ($payslip->status === 'paid') {
            return;
        }

        $this->adjustingPayslipId = $payslip->id;
        $this->adjustAmount = (string) $payslip->adjustments;
        $this->adjustNotes = $payslip->adjustment_notes ?? '';
        $this->showAdjustModal = true;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustAmount' => 'required|numeric',
            'adjustNotes' => 'nullable|string|max:255',
        ]);

        $payslip = Payslip::findOrFail($this->adjustingPayslipId);
        $this->authorize('update', $payslip);

        $payslip->update([
            'adjustments' => $this->adjustAmount,
            'adjustment_notes' => $this->adjustNotes,
            'net_amount' => $payslip->base_salary + $this->adjustAmount,
        ]);

        $this->showAdjustModal = false;
        session()->flash('status', 'Adjustment saved.');
    }

    public function markPaid(int $payslipId): void
    {
        $payslip = Payslip::findOrFail($payslipId);
        $this->authorize('update', $payslip);

        $payslip->update([
            'status' => 'paid',
            'paid_date' => now()->format('Y-m-d'),
            'paid_by' => auth()->id(),
        ]);

        session()->flash('status', 'Payslip marked as paid.');
    }
}