<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnswer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'evaluation_id',
        'question_id',
        'input_value',      // Nilai mentah (Qty atau Skala 1-5)
        'calculated_score'  // Nilai akhir (Input * Weight)
    ];

    protected $casts = [
        'input_value' => 'decimal:2',
        'calculated_score' => 'decimal:2',
    ];

    // Relasi balik ke Header Evaluasi
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    // Relasi ke Pertanyaan (Untuk ambil label/teks soal)
    public function question(): BelongsTo
    {
        return $this->belongsTo(TemplateQuestion::class, 'question_id');
    }
}