<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAttendanceRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // max 5MB
            'period_id' => 'required|uuid|exists:periods,id',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'File is required',
            'file.mimes' => 'File must be an Excel or CSV file',
            'file.max' => 'File size must not exceed 5MB',
            'period_id.required' => 'Period ID is required',
            'period_id.uuid' => 'Period ID must be a valid UUID',
            'period_id.exists' => 'Selected period does not exist',
        ];
    }
}
