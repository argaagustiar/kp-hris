<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'level' => $this->level,
            // Opsional: Hitung jumlah karyawan di posisi ini
            'employees_count' => $this->whenCounted('employees'),
        ];
    }
}