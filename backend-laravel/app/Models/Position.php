<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['title'];

    // Relasi ke Employees (One-to-Many)
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}