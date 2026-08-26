<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'code', 'name', 'address', 'city', 'region',
        'contact_phone', 'contact_email', 'timezone', 'currency',
        'academic_year', 'grading_scale', 'pay_cycle', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}