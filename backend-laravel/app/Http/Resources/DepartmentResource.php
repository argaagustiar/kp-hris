<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            
            // Tampilkan nama parent jika ada (Eager Loaded)
            'parent_name' => $this->whenLoaded('parent', function () {
                return $this->parent->name ?? null;
            }),

            // Opsional: Tampilkan children jika diperlukan
            'children' => DepartmentResource::collection($this->whenLoaded('children')),
        ];
    }
}