<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateQuestion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'section_id',
        'label_en',
        'description_en',
        'description_jp',
        'key_identifier',
        'input_type',
        'weight_point',
        'sequence_order'
    ];

    protected $casts = [
        'weight_point' => 'decimal:2', // Memastikan output JSON berupa angka desimal presisi
        'sequence_order' => 'integer',
    ];

    // Relasi balik ke Section
    public function section(): BelongsTo
    {
        return $this->belongsTo(TemplateSection::class, 'section_id');
    }
}