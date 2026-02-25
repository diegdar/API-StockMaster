<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\Category\CreateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Exceptions\DeletionException;
use App\Models\Category;
use App\Services\CategoryService;
use Tests\TestCase;
use Tests\Traits\CategoryTestTrait;
use App\Repositories\CategoryRepository;

class CategoryServiceTest extends TestCase
{
    use CategoryTestTrait;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoryService(
            repository: new CategoryRepository()
        );
    }

    public function test_get_all_categories(): void
    {
        $this->createCategories(5);

        $result = $this->service->getAllCategories();

        $this->assertCount(5, $result->items());
    }

    public function test_create_category(): void
    {
        $dto = new CreateCategoryDTO(
            name: 'Electronics',
            description: 'Electronic devices'
        );

        $result = $this->service->createCategory($dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals('Electronics', $result->name);
        $this->assertEquals('Electronic devices', $result->description);
    }

    public function test_update_category(): void
    {
        $category = $this->createCategory([
            'name' => 'Old Name',
            'description' => 'Old description',
        ]);

        $dto = new UpdateCategoryDTO(
            name: 'New Name',
            description: 'New description'
        );

        $result = $this->service->updateCategory($category, $dto);

        $this->assertEquals('New Name', $result->name);
        $this->assertEquals('New description', $result->description);
    }

    public function test_delete_category_without_products(): void
    {
        $category = $this->createCategory();

        $this->service->deleteCategory($category);

        $this->assertNull(Category::find($category->id));
    }

    public function test_delete_category_with_products_throws_deletion_exception(): void
    {
        $entities = $this->createCategoryWithProducts(1);

        $this->expectException(DeletionException::class);

        $this->service->deleteCategory($entities->category);
    }

    public function test_delete_category_with_products_returns_false(): void
    {
        $entities = $this->createCategoryWithProducts(1);

        try {
            $this->service->deleteCategory($entities->category);
        } catch (DeletionException $e) {
            $this->assertCategoryExists($entities->category->id);
        }
    }
}
