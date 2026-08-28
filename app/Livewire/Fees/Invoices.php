<?php

namespace App\Livewire\Fees;

use App\Models\ClassSection;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')] class Invoices extends Component
{
    use WithPagination;

    public string $month = '';
    public string $search = '';
    public string $statusFilter = '';
    public string $classSectionFilter = '';

    public bool $showPayModal = false;
    public ?int $payingInvoiceId = null;
    public string $payAmount = '';
    public string $payMethod = 'cash';
    public string $payDate = '';
    public string $payNotes = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingClassSectionFilter(): void { $this->resetPage(); }
    public function updatingMonth(): void { $this->resetPage(); }

    public function mount(): void
    {
        $this->authorize('viewAny', FeeInvoice::class);
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        $monthDate = $this->month . '-01';

        $invoices = FeeInvoice::with('student.classSection.schoolClass')
            ->whereDate('month', $monthDate)
            ->when($this->search, fn ($q) => $q->whereHas('student', fn ($q2) => $q2
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('admission_number', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->classSectionFilter, fn ($q) => $q->whereHas('student', fn ($q2) => $q2
                ->where('class_section_id', $this->classSectionFilter)
            ))
            ->orderByDesc('created_at')
            ->paginate(15);

        $classSections = ClassSection::with('schoolClass')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => $s->schoolClass->sort_order . $s->name);

        return view('livewire.fees.invoices', compact('invoices', 'classSections'));
    }

    public function generateInvoices(): void
    {
        $this->authorize('create', FeeInvoice::class);

        $monthDate = $this->month . '-01';
        $dueDate = date('Y-m-10', strtotime($monthDate));

        $students = Student::where('status', 'active')
            ->whereNotNull('class_section_id')
            ->with('classSection.schoolClass')
            ->get();

        $created = 0;

        foreach ($students as $student) {
            $fee = $student->classSection?->schoolClass?->monthly_fee ?? 0;

            if ($fee <= 0) {
                continue;
            }

            $invoice = FeeInvoice::firstOrCreate(
                ['student_id' => $student->id, 'month' => $monthDate],
                [
                    'campus_id' => $student->campus_id,
                    'due_date' => $dueDate,
                    'amount' => $fee,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                ]
            );

            if ($invoice->wasRecentlyCreated) {
                $created++;
            }
        }

        session()->flash('status', "{$created} invoice(s) generated for " . date('F Y', strtotime($monthDate)) . '.');
    }

    public function openPay(int $invoiceId): void
    {
        $invoice = FeeInvoice::findOrFail($invoiceId);
        $this->authorize('create', FeePayment::class);

        $this->payingInvoiceId = $invoice->id;
        $this->payAmount = (string) $invoice->balance;
        $this->payMethod = 'cash';
        $this->payDate = now()->format('Y-m-d');
        $this->payNotes = '';
        $this->showPayModal = true;
    }

    public function recordPayment(): void
    {
        $invoice = FeeInvoice::findOrFail($this->payingInvoiceId);
        $this->authorize('create', FeePayment::class);

        $this->validate([
            'payAmount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->balance],
            'payMethod' => 'required|in:cash,bank,online',
            'payDate' => 'required|date',
            'payNotes' => 'nullable|string|max:255',
        ]);

        FeePayment::create([
            'campus_id' => $invoice->campus_id,
            'fee_invoice_id' => $invoice->id,
            'amount' => $this->payAmount,
            'paid_date' => $this->payDate,
            'method' => $this->payMethod,
            'notes' => $this->payNotes,
            'received_by' => auth()->id(),
        ]);

        $invoice->increment('amount_paid', $this->payAmount);
        $invoice->refresh();
        $invoice->update([
            'status' => $invoice->amount_paid >= $invoice->amount ? 'paid' : 'partial',
        ]);

        $this->showPayModal = false;
        session()->flash('status', 'Payment recorded successfully.');
    }
}