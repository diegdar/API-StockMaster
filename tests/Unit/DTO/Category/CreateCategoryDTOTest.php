<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Category;

use App\DTO\Category\CreateCategoryDTO;
use PHPUnit\Framework\TestCase;

class CreateCategoryDTOTest extends TestCase
{
    public function test_creates_dto_from_valid_array(): void
    {
        $data = [
            'name' => 'Electronics',
            'description' => 'Electronic products and devices',
        ];

        $dto = CreateCategoryDTO::fromArray($data);

        $this->assertSame('Electronics', $dto->name);
        $this->assertSame('Electronic products and devices', $dto->description);
    }

    public function test_creates_dto_with_null_description(): void
    {
        $data = [
            'name' => 'Electronics',
        ];

        $dto = CreateCategoryDTO::fromArray($data);

        $this->assertSame('Electronics', $dto->name);
        $this->assertNull($dto->description);
    }

    public function test_converts_to_array(): void
    {
        $data = [
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ];

        $dto = CreateCategoryDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertSame('Electronics', $result['name']);
        $this->assertSame('Electronic products', $result['description']);
    }

    public function test_converts_to_array_filters_null_values(): void
    {
        $data = [
            'name' => 'Electronics',
        ];

        $dto = CreateCategoryDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('description', $result);
    }

    public static function validCategoryDataProvider(): array
    {
        return [
            'category with description' => [[
                'name' => 'Electronics',
                'description' => 'Electronic devices and components',
            ]],
            'category without description' => [[
                'name' => 'Clothing',
            ]],
            'category with long name' => [[
                'name' => 'Home and Garden Furniture and Accessories',
                'description' => 'All items for home and garden',
            ]],
        ];
    }

    /**
     * @dataProvider validCategoryDataProvider
     */
    public function test_valid_data_creates_dto(array $data): void
    {
        $dto = CreateCategoryDTO::fromArray($data);

        $this->assertInstanceOf(CreateCategoryDTO::class, $dto);
        $this->assertSame($data['name'], $dto->name);
    }
}
