<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationTemplate extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi ke Sections (A, B, C, D)
    // Diurutkan otomatis berdasarkan sequence_order
    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class, 'template_id')
                    ->orderBy('sequence_order', 'asc');
    }

    // Scope untuk mengambil template yang aktif saja
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}