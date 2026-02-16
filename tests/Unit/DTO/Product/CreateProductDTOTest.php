<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Product;

use App\DTO\Product\CreateProductDTO;
use PHPUnit\Framework\TestCase;

class CreateProductDTOTest extends TestCase
{
    public function test_creates_dto_from_valid_array(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'unit_price' => 100.50,
            'unit_cost' => 50.25,
            'category_id' => 1,
            'supplier_id' => 2,
            'valuation_strategy' => 'fifo',
            'min_stock_level' => 10,
        ];

        $dto = CreateProductDTO::fromArray($data);

        $this->assertSame('Test Product', $dto->name);
        $this->assertSame('TEST-001', $dto->sku);
        $this->assertSame('Test description', $dto->description);
        $this->assertSame(100.50, $dto->unitPrice);
        $this->assertSame(50.25, $dto->unitCost);
        $this->assertSame(1, $dto->categoryId);
        $this->assertSame(2, $dto->supplierId);
        $this->assertSame('fifo', $dto->valuationStrategy);
        $this->assertSame(10, $dto->minStockLevel);
    }

    public function test_creates_dto_with_default_min_stock_level(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'unit_price' => 100.50,
            'unit_cost' => 50.25,
            'category_id' => 1,
            'supplier_id' => 2,
            'valuation_strategy' => 'fifo',
        ];

        $dto = CreateProductDTO::fromArray($data);

        $this->assertSame(0, $dto->minStockLevel);
    }

    public function test_converts_to_array(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'unit_price' => 100.50,
            'unit_cost' => 50.25,
            'category_id' => 1,
            'supplier_id' => 2,
            'valuation_strategy' => 'fifo',
            'min_stock_level' => 10,
        ];

        $dto = CreateProductDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('sku', $result);
        $this->assertArrayHasKey('unit_price', $result);
        $this->assertArrayHasKey('unit_cost', $result);
        $this->assertArrayHasKey('category_id', $result);
        $this->assertArrayHasKey('supplier_id', $result);
        $this->assertArrayHasKey('valuation_strategy', $result);
        $this->assertArrayHasKey('min_stock_level', $result);
    }

    public function test_converts_decimal_values_to_float(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'unit_price' => '100.50',
            'unit_cost' => '50.25',
            'category_id' => '1',
            'supplier_id' => '2',
            'valuation_strategy' => 'fifo',
            'min_stock_level' => '10',
        ];

        $dto = CreateProductDTO::fromArray($data);

        $this->assertIsFloat($dto->unitPrice);
        $this->assertIsFloat($dto->unitCost);
        $this->assertIsInt($dto->categoryId);
        $this->assertIsInt($dto->supplierId);
        $this->assertIsInt($dto->minStockLevel);
    }

    public static function validProductDataProvider(): array
    {
        return [
            'fifo strategy' => [[
                'name' => 'Product 1',
                'sku' => 'PROD-001',
                'description' => 'Description 1',
                'unit_price' => 100.00,
                'unit_cost' => 50.00,
                'category_id' => 1,
                'supplier_id' => 1,
                'valuation_strategy' => 'fifo',
                'min_stock_level' => 5,
            ]],
            'lifo strategy' => [[
                'name' => 'Product 2',
                'sku' => 'PROD-002',
                'description' => 'Description 2',
                'unit_price' => 200.00,
                'unit_cost' => 100.00,
                'category_id' => 2,
                'supplier_id' => 2,
                'valuation_strategy' => 'lifo',
                'min_stock_level' => 10,
            ]],
            'avg strategy' => [[
                'name' => 'Product 3',
                'sku' => 'PROD-003',
                'description' => 'Description 3',
                'unit_price' => 150.00,
                'unit_cost' => 75.00,
                'category_id' => 1,
                'supplier_id' => 3,
                'valuation_strategy' => 'avg',
                'min_stock_level' => 0,
            ]],
        ];
    }

    /**
     * @dataProvider validProductDataProvider
     */
    public function test_valid_data_creates_dto(array $data): void
    {
        $dto = CreateProductDTO::fromArray($data);

        $this->assertInstanceOf(CreateProductDTO::class, $dto);
        $this->assertSame($data['name'], $dto->name);
        $this->assertSame($data['sku'], $dto->sku);
        $this->assertSame($data['valuation_strategy'], $dto->valuationStrategy);
    }
}
