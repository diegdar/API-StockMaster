<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Tests\TestCase;
use Tests\Traits\CategoryTestTrait;

class CategoryRepositoryTest extends TestCase
{
    use CategoryTestTrait;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CategoryRepository();
    }

    public function test_get_all_returns_paginated_categories(): void
    {
        $this->createCategories(20);

        $result = $this->repository
            ->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_get_categories_with_product_count(): void
    {
        $this->createCategoryWithProducts();

        $result = $this->repository
            ->getCategoriesWithProductCount();

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('products_count', $result->first()->toArray());
    }

    public function test_create_category(): void
    {
        $data = [
            'name' => 'New Category',
            'description' => 'Category description',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals('New Category', $result->name);
        $this->assertEquals('Category description', $result->description);
    }

    public function test_update_category(): void
    {
        $category = $this->createCategory([
            'name' => 'Old Name',
            'description' => 'Old description',
        ]);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $result = $this->repository->update($category, $data);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('Updated description', $result->description);
    }

    public function test_delete_category(): void
    {
        $category = $this->createCategory();

        $this->repository->delete($category);

        $this->assertNull(Category::find($category->id));
    }
}
