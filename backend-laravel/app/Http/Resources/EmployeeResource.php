<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'join_date' => $this->join_date?->format('Y-m-d') ?? null,
            'end_contract_date' => $this->end_contract_date?->format('Y-m-d') ?? null,
            
            // Eager Loaded Relationships
            'position' => [
                'id' => $this->position_id,
                'title' => $this->position->title ?? null,
                'level' => $this->position->level ?? null,
            ],

            'department' => [
                'id' => $this->department_id,
                'name' => $this->department->name ?? null,
            ],
            
            // 'departments' => $this->departments->map(function($dept) {
            //     return [
            //         'id' => $dept->id,
            //         'name' => $dept->name,
            //         'is_primary' => (bool) $dept->pivot->is_primary // Data dari Pivot
            //     ];
            // }),

            'managers' => $this->managers->map(function($mgr) {
                return [
                    'id' => $mgr->id,
                    'name' => $mgr->name,
                    'reporting_type' => $mgr->pivot->reporting_type // Data dari Pivot
                ];
            }),
        ];
    }
}
