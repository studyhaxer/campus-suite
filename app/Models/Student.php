<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use BelongsToCampus;

    protected $fillable = [
        'campus_id', 'class_section_id', 'admission_number', 'first_name', 'last_name',
        'date_of_birth', 'gender', 'admission_date', 'guardian_name', 'guardian_phone',
        'guardian_email', 'address', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}