<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Product;

/**
 * Trait for category-related test helpers.
 * Consolidates category data generation, assertions, and data providers.
 */
trait CategoryTestTrait
{
    use EntityCreationTrait;

    // ==========================================
    // Specialized Creation Methods
    // ==========================================

    /**
     * Create category with products for deletion tests.
     *
     * @param int $productCount
     * @return object{category: Category, products: \Illuminate\Database\Eloquent\Collection<int, Product>}
     */
    protected function createCategoryWithProducts(int $productCount = 3): object
    {
        $category = $this->createCategory();
        $supplier = $this->createSupplier();

        $products = Product::factory()->count($productCount)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        return (object) [
            'category' => $category,
            'products' => $products,
        ];
    }

    /**
     * Create category with specific slug for slug tests.
     *
     * @param string $name
     * @param string $slug
     * @return Category
     */
    protected function createCategoryWithSlug(string $name, string $slug): Category
    {
        return $this->createCategory([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    // ==========================================
    // Assertion Methods
    // ==========================================

    /**
     * Assert category attributes match expected data.
     *
     * @param Category $category
     * @param array<string, mixed> $expectedData
     */
    protected function assertCategoryAttributes(Category $category, array $expectedData): void
    {
        foreach ($expectedData as $attribute => $value) {
            $this->assertEquals(
                $value,
                $category->{$attribute},
                "Failed asserting that category {$attribute} equals expected value"
            );
        }
    }

    /**
     * Assert category was deleted from database.
     *
     * @param int $categoryId
     */
    protected function assertCategoryDeleted(int $categoryId): void
    {
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    /**
     * Assert category exists in database.
     *
     * @param int $categoryId
     */
    protected function assertCategoryExists(int $categoryId): void
    {
        $this->assertDatabaseHas('categories', ['id' => $categoryId]);
    }

    // ==========================================
    // Data Providers
    // ==========================================

    /**
     * Data provider for category permissions tests.
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: int}>
     */
    public static function categoryPermissionsProvider(): array
    {
        return [
            'Admin: Full Access' => ['Admin', 'GET', 'categories.index', 200],
            'Admin: Can show' => ['Admin', 'GET', 'categories.show', 200],
            'Admin: Can create' => ['Admin', 'POST', 'categories.store', 201],
            'Admin: Can update' => ['Admin', 'PUT', 'categories.update', 200],
            'Admin: Can delete without products' => ['Admin', 'DELETE', 'categories.destroy', 200],
            'Worker: Read-only' => ['Worker', 'GET', 'categories.index', 200],
            'Worker: Can show' => ['Worker', 'GET', 'categories.show', 200],
            'Worker: Cannot create' => ['Worker', 'POST', 'categories.store', 403],
            'Worker: Cannot update' => ['Worker', 'PUT', 'categories.update', 403],
            'Worker: Cannot delete' => ['Worker', 'DELETE', 'categories.destroy', 403],
            'Viewer: Read-only' => ['Viewer', 'GET', 'categories.index', 200],
            'Viewer: Can show' => ['Viewer', 'GET', 'categories.show', 200],
            'Viewer: Cannot create' => ['Viewer', 'POST', 'categories.store', 403],
            'Viewer: Cannot update' => ['Viewer', 'PUT', 'categories.update', 403],
            'Viewer: Cannot delete' => ['Viewer', 'DELETE', 'categories.destroy', 403],
        ];
    }

    /**
     * Data provider for category update scenarios.
     *
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: mixed}>
     */
    public static function categoryUpdateProvider(): array
    {
        return [
            'update name only' => [
                ['name' => 'Updated Category Name'],
                'name',
                'Updated Category Name',
            ],
            'update description only' => [
                ['description' => 'New description'],
                'description',
                'New description',
            ],
            'update multiple fields' => [
                ['name' => 'New Name', 'description' => 'New description'],
                'name',
                'New Name',
            ],
        ];
    }

    /**
     * Data provider for category creation with different fields.
     *
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function categoryCreationProvider(): array
    {
        return [
            'basic category' => [
                ['name' => 'Basic Category'],
            ],
            'category with description' => [
                ['name' => 'Category With Description', 'description' => 'A test description'],
            ],
            'category with special characters in name' => [
                ['name' => 'Ferretería General'],
            ],
        ];
    }
}
