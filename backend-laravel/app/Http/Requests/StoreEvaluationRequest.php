<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period_id' => 'required|exists:periods,id',
            'employee_id' => [
                'required',
                'exists:employees,id',
                Rule::unique('evaluations')->where(function ($query) {
                    return $query->where('period_id', $this->period_id)
                                 ->where('evaluator_id', $this->evaluator_id);
                }),
            ],
            'evaluator_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'end_contract_date' => 'nullable|date',
            'evaluation_purpose' => 'required',
            'question_1' => 'required|integer',
            'question_2' => 'required|integer',
            'question_3' => 'required|integer',
            'question_4' => 'required|integer',
            'question_5' => 'required|integer',
            'question_6' => 'required|integer',
            'question_7' => 'required|integer',
            'question_8' => 'required|integer',
            'question_9' => 'required|integer',
            'question_10' => 'required|integer',
            'comments' => 'string',            
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.unique' => 'Anda sudah melakukan penilaian untuk karyawan ini pada periode tersebut.',
        ];
    }
}
