<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user && $user->hasRole('Admin');
        $isWorkerOrAdmin = $user && ($user->hasRole('Admin') || $user->hasRole('Worker'));

        return [
            // Public fields (visible to all authenticated users)
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Worker+ fields (visible to Worker and Admin)
            'unit_price' => $this->when($isWorkerOrAdmin, (float) $this->unit_price),
            'supplier_id' => $this->when($isWorkerOrAdmin, $this->supplier_id),
            'valuation_strategy' => $this->when($isWorkerOrAdmin, $this->valuation_strategy),

            // Admin-only fields (sensitive financial data)
            'unit_cost' => $this->when($isAdmin, (float) $this->unit_cost),
            'margin' => $this->when($isAdmin, $this->unit_price - $this->unit_cost),
            'margin_percentage' => $this->when($isAdmin, (($this->unit_price - $this->unit_cost) / $this->unit_cost) * 100),
        ];
    }
}
