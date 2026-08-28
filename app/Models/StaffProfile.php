<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use BelongsToCampus;

    protected $fillable = [
        'campus_id', 'user_id', 'designation', 'department',
        'joining_date', 'base_salary', 'employment_status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}