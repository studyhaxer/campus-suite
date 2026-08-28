<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use BelongsToCampus;

    protected $fillable = ['campus_id', 'school_class_id', 'name', 'capacity', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->schoolClass->name . ' - ' . $this->name;
    }
}