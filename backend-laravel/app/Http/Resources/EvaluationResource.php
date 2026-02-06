<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_start' => $this->period_start?->format('Y-m-d') ?? null,
            'period_end' => $this->period_end?->format('Y-m-d') ?? null,
            'end_contract_date' => $this->end_contract_date?->format('Y-m-d') ?? null,
            'evaluation_purpose' => $this->evaluation_purpose ?? null,
            'question_1' => $this->question_1,
            'question_2' => $this->question_2,
            'question_3' => $this->question_3,
            'question_4' => $this->question_4,
            'question_5' => $this->question_5,
            'question_6' => $this->question_6,
            'question_7' => $this->question_7,
            'question_8' => $this->question_8,
            'question_9' => $this->question_9,
            'question_10' => $this->question_10,
            'comments' => $this->comments,
            
            'period' => [
                'id' => $this->period_id,
                'start_date' => $this->period->start_date?->format('Y-m-d') ?? null,
                'end_date' => $this->period->end_date?->format('Y-m-d') ?? null,
            ],

            'employee' => [
                'id' => $this->employee_id,
                'name' => $this->employee->name ?? null,
            ],

            'evaluator' => [
                'id' => $this->evaluator_id,
                'name' => $this->evaluator->name ?? null,
            ],            
        ];
    }
}
