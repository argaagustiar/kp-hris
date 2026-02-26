<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    // 3. Relasi ke BAWAHAN (Subordinates)
    public function subordinates(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_reporting_lines', 'employee_id', 'manager_id')
                    ->where('reporting_type', 'subordinate')
                    ->withPivot('reporting_type', 'deleted_at')
                    ->withTimestamps();
    }

    // 4. Relasi ke ATASAN (Heads/Managers)
    public function heads(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_reporting_lines', 'employee_id', 'manager_id')
                    ->where('reporting_type', 'head')
                    ->withPivot('reporting_type', 'deleted_at')
                    ->withTimestamps();
    }

    // 5. Relasi ke REKAN KERJA (Coworkers)
    public function coworkers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_reporting_lines', 'employee_id', 'manager_id')
                    ->where('reporting_type', 'coworker')
                    ->withPivot('reporting_type', 'deleted_at')
                    ->withTimestamps();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }

    public function evaluations(): HasMany
    {
        // Return evaluations for this employee (evaluations where `employee_id` == this employee's id)
        // Previously this returned evaluations by this employee (using `evaluator_id`), which
        // caused confusion when eager-loading `evaluator` to find evaluations *about* an employee.
        return $this->hasMany(Evaluation::class, 'employee_id', 'id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_id', 'id');
    }
}