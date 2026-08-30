<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->staff->name }} - {{ $payslip->month->format('F Y') }}</title>
    <style>
        body { font-family: sans-serif; color: #1C2541; font-size: 13px; }
        .header { border-bottom: 2px solid #1C2541; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0 0; color: #6B7280; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { padding: 8px 4px; text-align: left; }
        .meta-table td { border: none; padding: 4px 4px; }
        .meta-table td:first-child { color: #6B7280; width: 140px; }
        .amount-table th { border-bottom: 1px solid #E2E5EC; font-size: 11px; text-transform: uppercase; color: #6B7280; }
        .amount-table td { border-bottom: 1px solid #EEF0F4; }
        .amount-table .text-right { text-align: right; }
        .net-row td { border-top: 2px solid #1C2541; border-bottom: none; font-weight: bold; font-size: 15px; padding-top: 12px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 11px; }
        .status-paid { background: #E6F4EC; color: #2F8F5B; }
        .status-draft { background: #EEF0F4; color: #6B7280; }
        .footer { margin-top: 40px; font-size: 11px; color: #9AA1AE; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Payslip</h1>
        <p>{{ $payslip->campus->name ?? 'Campus Suite' }} · {{ $payslip->month->format('F Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td>Staff Name</td>
            <td>{{ $payslip->staff->name }}</td>
        </tr>
        <tr>
            <td>Designation</td>
            <td>{{ $payslip->staff->staffProfile?->designation ?? '—' }}</td>
        </tr>
        <tr>
            <td>Department</td>
            <td>{{ $payslip->staff->staffProfile?->department ?? '—' }}</td>
        </tr>
        <tr>
            <td>Pay Period</td>
            <td>{{ $payslip->month->format('F Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                <span class="status-badge {{ $payslip->status === 'paid' ? 'status-paid' : 'status-draft' }}">
                    {{ ucfirst($payslip->status) }}
                </span>
                @if ($payslip->status === 'paid' && $payslip->paid_date)
                    on {{ $payslip->paid_date->format('M d, Y') }}
                @endif
            </td>
        </tr>
    </table>

    <table class="amount-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Base Salary</td>
                <td class="text-right">{{ number_format($payslip->base_salary, 2) }}</td>
            </tr>
            <tr>
                <td>
                    Adjustments
                    @if ($payslip->adjustment_notes)
                        <br><span style="color: #9AA1AE; font-size: 11px;">{{ $payslip->adjustment_notes }}</span>
                    @endif
                </td>
                <td class="text-right">{{ $payslip->adjustments >= 0 ? '+' : '' }}{{ number_format($payslip->adjustments, 2) }}</td>
            </tr>
            <tr class="net-row">
                <td>Net Pay</td>
                <td class="text-right">{{ number_format($payslip->net_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y') }} · Campus Suite
    </div>

</body>
</html>