<?php
declare(strict_types=1);

namespace App\DTO\Product;

readonly class UpdateProductDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $sku = null,
        public ?string $description = null,
        public ?float $unitPrice = null,
        public ?float $unitCost = null,
        public ?int $categoryId = null,
        public ?int $supplierId = null,
        public ?string $valuationStrategy = null,
        public ?int $minStockLevel = null
    ) {}

    /**
     * Creates an UpdateProductDTO from an array of data.
     *
     * The array should contain the keys 'name', 'sku', 'description',
     * 'unit_price', 'unit_cost', 'category_id', 'supplier_id',
     * 'valuation_strategy', and 'min_stock_level'.
     *
     * If a key is not present in the array, the corresponding field
     * of the UpdateProductDTO will be set to null.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            sku: $data['sku'] ?? null,
            description: $data['description'] ?? null,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            supplierId: isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            valuationStrategy: $data['valuation_strategy'] ?? null,
            minStockLevel: isset($data['min_stock_level']) ? (int) $data['min_stock_level'] : null
        );
    }

    /**
     * Converts the UpdateProductDTO to an array.
     *
     * The array will contain all the fields of the UpdateProductDTO
     * that have a non-null value.
     *
     * @return array
     */
    public function toArray(): array
    {
        return collect([
            'name'               => $this->name,
            'sku'                => $this->sku,
            'description'        => $this->description,
            'unit_price'         => $this->unitPrice,
            'unit_cost'          => $this->unitCost,
            'category_id'        => $this->categoryId,
            'supplier_id'        => $this->supplierId,
            'valuation_strategy' => $this->valuationStrategy,
            'min_stock_level'    => $this->minStockLevel,
        ])->whereNotNull()->toArray();
    }

    /**
     * Checks if any of the fields in the UpdateProductDTO have a non-null value.
     *
     * @return bool True if any of the fields have a non-null value, false otherwise.
     */
    public function hasAnyField(): bool
    {
        return $this->name !== null
            || $this->sku !== null
            || $this->description !== null
            || $this->unitPrice !== null
            || $this->unitCost !== null
            || $this->categoryId !== null
            || $this->supplierId !== null
            || $this->valuationStrategy !== null
            || $this->minStockLevel !== null;
    }
}
