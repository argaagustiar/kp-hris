<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'period_id',
        'employee_id',
        'evaluator_id',
        'period_start',
        'period_end',
        'evaluation_purpose',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'question_5',
        'question_6',
        'question_7',
        'question_8',
        'question_9',
        'question_10',
        'comments'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'evaluation_date' => 'datetime',        
        'evaluation_purpose' => 'string', 
        'question_1' => 'integer',
        'question_2' => 'integer',
        'question_3' => 'integer',
        'question_4' => 'integer',
        'question_5' => 'integer',
        'question_6' => 'integer',
        'question_7' => 'integer',
        'question_8' => 'integer',
        'question_9' => 'integer',
        'question_10' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }
}