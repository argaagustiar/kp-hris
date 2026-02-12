<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
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
            'period_id' => $this->period_id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'employee_code' => $this->employee->employee_code,
                'email' => $this->employee->email,
            ]),
            'period' => $this->whenLoaded('period', fn() => [
                'id' => $this->period->id,
                'name' => $this->period->name,
            ]),
            'sick' => $this->sick,
            'work_accident' => $this->work_accident,
            'permit' => $this->permit,
            'awol' => $this->awol,
            'late_permit' => $this->late_permit,
            'early_leave' => $this->early_leave,
            'annual_leave' => $this->annual_leave,
            'late' => $this->late,
            'warning_letter_1' => $this->warning_letter_1,
            'warning_letter_2' => $this->warning_letter_2,
            'warning_letter_3' => $this->warning_letter_3,
            'subordinate_late' => $this->subordinate_late,
            'subordinate_awol' => $this->subordinate_awol,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
