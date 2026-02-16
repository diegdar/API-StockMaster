<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CategoryRepository();
    }

    public function test_get_all_returns_paginated_categories(): void
    {
        Category::factory()->count(20)->create();

        $result = $this->repository->getAll(5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasPages());
    }

    public function test_find_by_id_returns_category(): void
    {
        $category = Category::factory()->create();

        $result = $this->repository->findById($category->id);

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById(9999);

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $result = $this->repository->findBySlug('electronics');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result->id);
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $result = $this->repository->findBySlug('non-existent');

        $this->assertNull($result);
    }

    public function test_get_categories_with_product_count(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $result = $this->repository->getCategoriesWithProductCount();

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
        $category = Category::factory()->create([
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
        $category = Category::factory()->create();

        $result = $this->repository->delete($category);

        $this->assertTrue($result);
        $this->assertNull(Category::find($category->id));
    }
}
