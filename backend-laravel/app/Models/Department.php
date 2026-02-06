<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['name', 'parent_id'];

    // 1. Relasi ke Parent Department (Induk)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    // 2. Relasi ke Sub-Departments (Anak)
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    // 3. Relasi ke Employees (Many-to-Many)
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_departments')
                    ->withPivot('is_primary', 'deleted_at')
                    ->withTimestamps();
    }
}