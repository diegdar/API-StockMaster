<?php
declare(strict_types=1);

namespace Tests\Unit\DTO\Category;

use App\DTO\Category\UpdateCategoryDTO;
use PHPUnit\Framework\TestCase;

class UpdateCategoryDTOTest extends TestCase
{
    public function test_creates_dto_with_partial_data(): void
    {
        $data = [
            'name' => 'Updated Electronics',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertSame('Updated Electronics', $dto->name);
        $this->assertNull($dto->description);
    }

    public function test_creates_dto_with_all_fields(): void
    {
        $data = [
            'name' => 'Updated Electronics',
            'description' => 'Updated description',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertSame('Updated Electronics', $dto->name);
        $this->assertSame('Updated description', $dto->description);
    }

    public function test_creates_dto_with_all_null_when_empty_array(): void
    {
        $data = [];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertNull($dto->name);
        $this->assertNull($dto->description);
    }

    public function test_to_array_includes_only_provided_fields(): void
    {
        $data = [
            'name' => 'Updated Electronics',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('description', $result);
    }

    public function test_to_array_excludes_null_values(): void
    {
        $data = [
            'name' => 'Updated Electronics',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);
        $result = $dto->toArray();

        $this->assertCount(1, $result);
        $this->assertSame('Updated Electronics', $result['name']);
    }

    public function test_has_any_field_returns_true_when_fields_provided(): void
    {
        $data = [
            'name' => 'Updated Electronics',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertTrue($dto->hasAnyField());
    }

    public function test_has_any_field_returns_false_when_no_fields(): void
    {
        $data = [];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertFalse($dto->hasAnyField());
    }

    public function test_handles_description_only_update(): void
    {
        $data = [
            'description' => 'New description only',
        ];

        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertNull($dto->name);
        $this->assertSame('New description only', $dto->description);
        $this->assertTrue($dto->hasAnyField());
    }

    public static function partialUpdateDataProvider(): array
    {
        return [
            'update name only' => [[
                'name' => 'New Category Name',
            ]],
            'update description only' => [[
                'description' => 'New description',
            ]],
            'update both fields' => [[
                'name' => 'New Name',
                'description' => 'New description',
            ]],
        ];
    }

    /**
     * @dataProvider partialUpdateDataProvider
     */
    public function test_partial_updates(array $data): void
    {
        $dto = UpdateCategoryDTO::fromArray($data);

        $this->assertTrue($dto->hasAnyField());
        $this->assertNotEmpty($dto->toArray());
    }
}
