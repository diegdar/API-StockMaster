<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
            'inventories_count' => $this->whenCounted('inventories'),
            'total_capacity' => $this->when(isset($this->total_capacity), $this->total_capacity),
            'used_capacity' => $this->when(isset($this->used_capacity), $this->used_capacity),
            'available_capacity' => $this->when(isset($this->available_capacity), $this->available_capacity),
            'utilization_percentage' => $this->when(isset($this->utilization_percentage), $this->utilization_percentage),
        ];
    }
}
