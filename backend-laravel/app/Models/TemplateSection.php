<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'template_id',
        'name',
        'description_en',
        'description_jp',
        'sequence_order'
    ];

    // Relasi balik ke Parent Template
    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }

    // Relasi ke Questions (Pertanyaan di dalam section ini)
    // Diurutkan otomatis
    public function questions(): HasMany
    {
        return $this->hasMany(TemplateQuestion::class, 'section_id')
                    ->orderBy('sequence_order', 'asc');
    }
}