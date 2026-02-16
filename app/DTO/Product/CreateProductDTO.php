<?php
declare(strict_types=1);

namespace App\DTO\Product;

readonly class CreateProductDTO
{
    public function __construct(
        public string $name,
        public string $sku,
        public string $description,
        public float $unitPrice,
        public float $unitCost,
        public int $categoryId,
        public int $supplierId,
        public string $valuationStrategy,
        public int $minStockLevel = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            sku: $data['sku'],
            description: $data['description'] ?? '',
            unitPrice: (float) $data['unit_price'],
            unitCost: (float) $data['unit_cost'],
            categoryId: (int) $data['category_id'],
            supplierId: (int) $data['supplier_id'],
            valuationStrategy: $data['valuation_strategy'],
            minStockLevel: (int) ($data['min_stock_level'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit_price' => $this->unitPrice,
            'unit_cost' => $this->unitCost,
            'category_id' => $this->categoryId,
            'supplier_id' => $this->supplierId,
            'valuation_strategy' => $this->valuationStrategy,
            'min_stock_level' => $this->minStockLevel,
        ];
    }
}
