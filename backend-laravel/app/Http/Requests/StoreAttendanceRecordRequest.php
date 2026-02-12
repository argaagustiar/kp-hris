<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'period_id' => 'required|uuid|exists:periods,id',
            'employee_id' => 'required|uuid|exists:employees,id',
            'sick' => 'nullable|integer|min:0',
            'work_accident' => 'nullable|integer|min:0',
            'permit' => 'nullable|integer|min:0',
            'awol' => 'nullable|integer|min:0',
            'late_permit' => 'nullable|integer|min:0',
            'early_leave' => 'nullable|integer|min:0',
            'annual_leave' => 'nullable|integer|min:0',
            'late' => 'nullable|integer|min:0',
            'warning_letter_1' => 'nullable|boolean',
            'warning_letter_2' => 'nullable|boolean',
            'warning_letter_3' => 'nullable|boolean',
            'subordinate_late' => 'nullable|integer|min:0',
            'subordinate_awol' => 'nullable|integer|min:0',
        ];
    }
}
