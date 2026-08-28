<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use BelongsToCampus;

    protected $fillable = [
        'campus_id', 'user_id', 'month', 'base_salary', 'adjustments',
        'adjustment_notes', 'net_amount', 'status', 'paid_date', 'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'base_salary' => 'decimal:2',
            'adjustments' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}