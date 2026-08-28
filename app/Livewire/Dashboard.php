<?php

namespace App\Livewire;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Payslip;
use App\Models\Student;
use App\Models\StudentAttendance;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isManager = $user->hasRole('Manager');

        $canViewStudents = $user->can('viewAny', Student::class);
        $canViewAttendance = $user->can('viewAny', StudentAttendance::class);
        $canViewFees = $user->can('viewAny', FeeInvoice::class);
        $canViewPayroll = $user->can('viewAny', Payslip::class);

        $today = now()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        $kpi = $this->buildKpis($today, $monthStart, $monthEnd, $canViewStudents, $canViewAttendance, $canViewFees, $canViewPayroll);
        $alerts = $this->buildAlerts($today, $monthStart, $canViewStudents, $canViewAttendance, $canViewFees, $canViewPayroll);
        $campusRows = $isManager ? $this->buildCampusRegistry($today, $monthStart, $monthEnd) : collect();

        return view('livewire.dashboard', [
            'kpi' => $kpi,
            'alerts' => $alerts,
            'campusRows' => $campusRows,
            'isManager' => $isManager,
            'canViewStudents' => $canViewStudents,
            'canViewAttendance' => $canViewAttendance,
            'canViewFees' => $canViewFees,
            'canViewPayroll' => $canViewPayroll,
        ]);
    }

    protected function buildKpis($today, $monthStart, $monthEnd, $canViewStudents, $canViewAttendance, $canViewFees, $canViewPayroll): array
    {
        $kpi = [];

        if ($canViewStudents) {
            $kpi['total_students'] = Student::where('status', 'active')->count();
        }

        if ($canViewAttendance) {
            $attendanceTotal = StudentAttendance::where('date', $today)->count();
            $attendancePresent = StudentAttendance::where('date', $today)->where('status', 'present')->count();
            $kpi['attendance_pct'] = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : null;
            $kpi['attendance_present'] = $attendancePresent;
            $kpi['attendance_total'] = $attendanceTotal;
        }

        if ($canViewFees) {
            $kpi['fees_collected'] = FeePayment::whereBetween('paid_date', [$monthStart, $monthEnd])->sum('amount');
            $kpi['fees_outstanding'] = FeeInvoice::whereDate('month', $monthStart)->whereIn('status', ['unpaid', 'partial'])->get()->sum('balance');
        }

        if ($canViewPayroll) {
            $kpi['payroll_due'] = Payslip::whereDate('month', $monthStart)->where('status', 'draft')->sum('net_amount');
        }

        return $kpi;
    }

    protected function buildAlerts($today, $monthStart, $canViewStudents, $canViewAttendance, $canViewFees, $canViewPayroll): array
    {
        $alerts = [];

        if ($canViewFees) {
            $overdueInvoices = FeeInvoice::whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', $today)->get();
            if ($overdueInvoices->count() > 0) {
                $alerts[] = [
                    'level' => 'danger',
                    'text' => $overdueInvoices->count() . ' overdue fee invoice(s) totaling ' . number_format($overdueInvoices->sum('balance'), 0),
                ];
            }
        }

        if ($canViewAttendance) {
            $weekStart = now()->startOfWeek()->format('Y-m-d');
            $weekTotal = StudentAttendance::where('date', '>=', $weekStart)->count();
            $weekPresent = StudentAttendance::where('date', '>=', $weekStart)->where('status', 'present')->count();
            $weekPct = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 1) : null;
            if ($weekPct !== null && $weekPct < 90) {
                $alerts[] = [
                    'level' => 'warning',
                    'text' => "Attendance this week is at {$weekPct}%, below the 90% target",
                ];
            }
        }

        if ($canViewPayroll) {
            $draftPayslips = Payslip::whereDate('month', $monthStart)->where('status', 'draft')->count();
            if ($draftPayslips > 0) {
                $alerts[] = [
                    'level' => 'warning',
                    'text' => "{$draftPayslips} payslip(s) still pending payout this month",
                ];
            }
        }

        if ($canViewStudents) {
            $unassigned = Student::where('status', 'active')->whereNull('class_section_id')->count();
            if ($unassigned > 0) {
                $alerts[] = [
                    'level' => 'neutral',
                    'text' => "{$unassigned} active student(s) not yet assigned to a class section",
                ];
            }
        }

        return $alerts;
    }

    protected function buildCampusRegistry($today, $monthStart, $monthEnd)
    {
        $campuses = auth()->user()->organization?->campuses ?? collect();

        return $campuses->map(function ($campus) use ($today, $monthStart, $monthEnd) {
            $studentCount = Student::where('campus_id', $campus->id)->where('status', 'active')->count();

            $attTotal = StudentAttendance::where('campus_id', $campus->id)->where('date', $today)->count();
            $attPresent = StudentAttendance::where('campus_id', $campus->id)->where('date', $today)->where('status', 'present')->count();
            $attPct = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : null;

            $collected = FeePayment::where('campus_id', $campus->id)->whereBetween('paid_date', [$monthStart, $monthEnd])->sum('amount');

            $overdueCount = FeeInvoice::where('campus_id', $campus->id)->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', $today)->count();

            $payrollDue = Payslip::where('campus_id', $campus->id)->whereDate('month', $monthStart)->where('status', 'draft')->sum('net_amount');

            if ($overdueCount > 0) {
                $status = 'Overdue invoices';
                $statusLevel = 'danger';
            } elseif ($attPct !== null && $attPct < 90) {
                $status = 'Attendance dip';
                $statusLevel = 'warning';
            } else {
                $status = 'On track';
                $statusLevel = 'success';
            }

            return [
                'campus' => $campus,
                'students' => $studentCount,
                'attendance_pct' => $attPct,
                'fees_collected' => $collected,
                'payroll_due' => $payrollDue,
                'status' => $status,
                'status_level' => $statusLevel,
            ];
        });
    }
}