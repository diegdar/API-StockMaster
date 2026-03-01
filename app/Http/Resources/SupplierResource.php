<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'contact_email' => $this->contact_email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'products_count' => $this->whenCounted('products', $this->products_count ?? 0),
        ];
    }
}
