<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Product;

use App\DTO\Product\UpdateProductDTO;
use PHPUnit\Framework\TestCase;

class UpdateProductDTOTest extends TestCase
{
    public function test_creates_dto_with_partial_data(): void
    {
        $data = [
            'name' => 'Updated Product',
            'unit_price' => 150.00,
        ];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertSame('Updated Product', $dto->name);
        $this->assertSame(150.00, $dto->unitPrice);
        $this->assertNull($dto->sku);
        $this->assertNull($dto->description);
        $this->assertNull($dto->unitCost);
    }

    public function test_creates_dto_with_all_null_when_empty_array(): void
    {
        $data = [];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertNull($dto->name);
        $this->assertNull($dto->sku);
        $this->assertNull($dto->description);
        $this->assertNull($dto->unitPrice);
        $this->assertNull($dto->unitCost);
        $this->assertNull($dto->categoryId);
        $this->assertNull($dto->supplierId);
        $this->assertNull($dto->valuationStrategy);
        $this->assertNull($dto->minStockLevel);
    }

    public function test_to_array_includes_only_provided_fields(): void
    {
        $data = [
            'name' => 'Updated Product',
            'unit_price' => 150.00,
        ];

        $dto = UpdateProductDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('unit_price', $result);
        $this->assertArrayNotHasKey('sku', $result);
        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('unit_cost', $result);
    }

    public function test_to_array_excludes_null_values(): void
    {
        $data = [
            'name' => 'Updated Product',
        ];

        $dto = UpdateProductDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertCount(1, $result);
        $this->assertSame('Updated Product', $result['name']);
    }

    public function test_has_any_field_returns_true_when_fields_provided(): void
    {
        $data = [
            'name' => 'Updated Product',
        ];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertTrue($dto->hasAnyField());
    }

    public function test_has_any_field_returns_false_when_no_fields(): void
    {
        $data = [];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertFalse($dto->hasAnyField());
    }

    public function test_converts_numeric_strings_to_proper_types(): void
    {
        $data = [
            'unit_price' => '150.50',
            'unit_cost' => '75.25',
            'category_id' => '2',
            'supplier_id' => '3',
            'min_stock_level' => '10',
        ];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertIsFloat($dto->unitPrice);
        $this->assertIsFloat($dto->unitCost);
        $this->assertIsInt($dto->categoryId);
        $this->assertIsInt($dto->supplierId);
        $this->assertIsInt($dto->minStockLevel);
    }

    public function test_handles_min_stock_level_update(): void
    {
        $data = [
            'min_stock_level' => 20,
        ];

        $dto = UpdateProductDTO::fromArray($data);

        $this->assertSame(20, $dto->minStockLevel);
        $this->assertTrue($dto->hasAnyField());
    }

    public static function partialUpdateDataProvider(): array
    {
        return [
            'update name only' => [[
                'name' => 'New Name',
            ]],
            'update price only' => [[
                'unit_price' => 200.00,
            ]],
            'update strategy only' => [[
                'valuation_strategy' => 'lifo',
            ]],
            'update multiple fields' => [[
                'name' => 'New Name',
                'unit_price' => 200.00,
                'min_stock_level' => 15,
            ]],
        ];
    }

    /**
     * @dataProvider partialUpdateDataProvider
     */
    public function test_partial_updates(array $data): void
    {
        $dto = UpdateProductDTO::fromArray($data);

        $this->assertTrue($dto->hasAnyField());
        $this->assertNotEmpty($dto->toArray());
    }
}
