<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department'); // Ambil ID dari URL

        return [
            'name' => 'required|string|max:255',
            // Validasi: parent_id tidak boleh sama dengan ID diri sendiri
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($departmentId) {
                    if ($value == $departmentId) {
                        $fail('A department cannot be its own parent.');
                    }
                },
            ]
        ];
    }
}