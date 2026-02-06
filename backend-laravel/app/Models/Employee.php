<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, HasUuids, SoftDeletes, Notifiable;

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'username',
        'password',
        'position_id',
        'department_id',
        'join_date',
        'end_contract_date',
        'is_active'
    ];

    // Casting tanggal agar otomatis jadi Carbon object
    protected $casts = [
        'join_date' => 'date',
        'end_contract_date' => 'date',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 1. Relasi ke Position (Jabatan Utama)
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // 2. Relasi ke Departments (Bisa banyak)
    // public function departments(): BelongsToMany
    // {
    //     return $this->belongsToMany(Department::class, 'employee_departments')
    //                 ->withPivot('is_primary', 'deleted_at')
    //                 ->withTimestamps();
    // }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Helper: Ambil departemen utama saja
    public function primaryDepartment()
    {
        return $this->belongsToMany(Department::class, 'employee_departments')
                    ->wherePivot('is_primary', true);
    }

    // 3. Relasi ke ATASAN (Managers)
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_reporting_lines', 'employee_id', 'manager_id')
                    ->withPivot('reporting_type', 'deleted_at')
                    ->withTimestamps();
    }

    // 4. Relasi ke BAWAHAN (Subordinates)
    public function subordinates(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_reporting_lines', 'manager_id', 'employee_id')
                    ->withPivot('reporting_type', 'deleted_at')
                    ->withTimestamps();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }
}