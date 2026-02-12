<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'period_id', 'employee_id', 'sick', 'work_accident', 'permit', 'awol', 'late_permit', 
        'early_leave', 'annual_leave', 'late', 'warning_letter_1', 'warning_letter_2', 'warning_letter_3', 
        'subordinate_late', 'subordinate_awol'
    ];

    // Relasi ke Employees (One-to-Many)
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
    
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}