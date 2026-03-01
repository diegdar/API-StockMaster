<?php
declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Supplier;

trait SupplierTestTrait
{
    /**
     * Create a supplier for testing.
     */
    protected function createSupplier(array $attributes = []): Supplier
    {
        return Supplier::factory()->create($attributes);
    }

    /**
     * Create multiple suppliers for testing.
     */
    protected function createSuppliers(int $count, array $attributes = []): Collection
    {
        return Supplier::factory()->count($count)->create($attributes);
    }

    /**
     * Create a supplier with products for testing.
     *
     * @return array{supplier: Supplier, products: Collection}
     */
    protected function createSupplierWithProducts(int $productCount = 1): array
    {
        $supplier = $this->createSupplier();
        $category = \App\Models\Category::factory()->create();

        $products = $supplier->products()->createMany(
            \App\Models\Product::factory()->count($productCount)->make([
                'category_id' => $category->id,
            ])->toArray()
        );

        return [
            'supplier' => $supplier,
            'products' => $products,
        ];
    }

    /**
     * Assert supplier has correct structure.
     */
    protected function assertSupplierStructure(array $supplier): void
    {
        $this->assertArrayHasKey('name', $supplier);
        $this->assertArrayHasKey('slug', $supplier);
        $this->assertArrayHasKey('contact_email', $supplier);
        $this->assertArrayHasKey('phone', $supplier);
        $this->assertArrayHasKey('address', $supplier);
        $this->assertArrayHasKey('is_active', $supplier);
        $this->assertArrayHasKey('products_count', $supplier);
    }

    /**
     * Assert supplier does not expose internal fields.
     */
    protected function assertSupplierNoInternalFields(array $supplier): void
    {
        $this->assertArrayNotHasKey('id', $supplier);
        $this->assertArrayNotHasKey('created_at', $supplier);
        $this->assertArrayNotHasKey('updated_at', $supplier);
    }
}
