<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends Model
{
    use BelongsToCampus;

    protected $fillable = ['campus_id', 'student_id', 'month', 'due_date', 'amount', 'amount_paid', 'status'];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function getBalanceAttribute(): float
    {
        return round($this->amount - $this->amount_paid, 2);
    }
}