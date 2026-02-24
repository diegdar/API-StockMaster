<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait for creating test entities using factories.
 * Centralizes all entity creation methods to avoid duplication across test files.
 */
trait EntityCreationTrait
{
    // ==========================================
    // Data Generation Methods
    // ==========================================

    /**
     * Get valid product data for store/update requests.
     *
     * @param int $categoryId
     * @param int $supplierId
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function getProductData(int $categoryId, int $supplierId, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-' . uniqid(),
            'description' => 'Test description',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'valuation_strategy' => 'fifo',
        ], $overrides);
    }

    // ==========================================
    // General Entity Methods
    // ==========================================

    /**
     * Create basic test entities (product, warehouse, user).
     *
     * @return object{product: Product, warehouse: Warehouse, user: User}
     */
    protected function createTestEntities(): object
    {
        return (object) [
            'product' => $this->createProduct(),
            'warehouse' => $this->createWarehouse(),
            'user' => $this->createUser(),
        ];
    }

    // ==========================================
    // Product Entity Methods
    // ==========================================

    /**
     * Create a product with category and supplier.
     *
     * @param array<string, mixed> $overrides
     * @return Product
     */
    protected function createProduct(array $overrides = []): Product
    {
        $entities = $this->createCategoryAndSupplier();

        return Product::factory()->create(array_merge([
            'category_id' => $entities->category->id,
            'supplier_id' => $entities->supplier->id,
        ], $overrides));
    }

    /**
     * Create product entities (product, category, supplier, and warehouse) with overrides.
     *
     * @param array<string, mixed> $productOverrides
     * @param array<string, mixed> $supplierOverrides
     * @param array<string, mixed> $categoryOverrides
     * @param array<string, mixed> $warehouseOverrides
     * @return object{product: Product, category: Category, supplier: Supplier, warehouse: Warehouse}
     */
    protected function createProductEntities(
        array $productOverrides = [],
        array $supplierOverrides = [],
        array $categoryOverrides = [],
        array $warehouseOverrides = []
    ): object {
        $category = Category::factory()->create($categoryOverrides);
        $supplier = Supplier::factory()->create($supplierOverrides);
        $warehouse = Warehouse::factory()->create($warehouseOverrides);

        $product = Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ], $productOverrides));

        return (object) [
            'product' => $product,
            'category' => $category,
            'supplier' => $supplier,
            'warehouse' => $warehouse,
        ];
    }

    /**
     * Create multiple products for pagination tests.
     *
     * @param int $categoryId
     * @param int $supplierId
     * @param int $count
     * @return void
     */
    protected function createProductsForPagination(int $categoryId, int $supplierId, int $count): void
    {
        Product::factory()->count($count)->create([
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
        ]);
    }

    /**
     * Create multiple products for a specific supplier.
     *
     * @param int $supplierId
     * @param int $count
     * @return Collection<int, Product>
     */
    protected function createProductsForSupplier(int $supplierId, int $count): Collection
    {
        $category = Category::factory()->create();

        return Product::factory()->count($count)->create([
            'category_id' => $category->id,
            'supplier_id' => $supplierId,
        ]);
    }

    /**
     * Create multiple products for a specific category.
     *
     * @param int $categoryId
     * @param int $count
     * @return Collection<int, Product>
     */
    protected function createProductsForCategory(int $categoryId, int $count): Collection
    {
        $supplier = Supplier::factory()->create();

        return Product::factory()->count($count)->create([
            'category_id' => $categoryId,
            'supplier_id' => $supplier->id,
        ]);
    }

    // ==========================================
    // Warehouse Entity Methods
    // ==========================================

    /**
     * Create a warehouse.
     *
     * @param array<string, mixed> $overrides
     * @return Warehouse
     */
    protected function createWarehouse(array $overrides = []): Warehouse
    {
        return Warehouse::factory()->create($overrides);
    }

    /**
     * Create multiple warehouses.
     *
     * @param int $count
     * @return Collection<int, Warehouse>
     */
    protected function createWarehouses(int $count): Collection
    {
        return Warehouse::factory()->count($count)->create();
    }

    /**
     * Create two warehouses for transfer tests.
     *
     * @return object{sourceWarehouse: Warehouse, destinationWarehouse: Warehouse}
     */
    protected function createTransferWarehouses(): object
    {
        return (object) [
            'sourceWarehouse' => Warehouse::factory()->create(),
            'destinationWarehouse' => Warehouse::factory()->create(),
        ];
    }

    // ==========================================
    // Category & Supplier Entity Methods
    // ==========================================

    /**
     * Create a category.
     *
     * @param array<string, mixed> $overrides
     * @return Category
     */
    protected function createCategory(array $overrides = []): Category
    {
        return Category::factory()->create($overrides);
    }

    /**
     * Create multiple categories.
     *
     * @param int $count
     * @return Collection<int, Category>
     */
    protected function createCategories(int $count): Collection
    {
        return Category::factory()->count($count)->create();
    }

    /**
     * Create a supplier.
     *
     * @param array<string, mixed> $overrides
     * @return Supplier
     */
    protected function createSupplier(array $overrides = []): Supplier
    {
        return Supplier::factory()->create($overrides);
    }

    /**
     * Create multiple suppliers.
     *
     * @param int $count
     * @return Collection<int, Supplier>
     */
    protected function createSuppliers(int $count): Collection
    {
        return Supplier::factory()->count($count)->create();
    }

    /**
     * Create category and supplier for product tests.
     *
     * @return object{category: Category, supplier: Supplier}
     */
    protected function createCategoryAndSupplier(): object
    {
        return (object) [
            'category' => Category::factory()->create(),
            'supplier' => Supplier::factory()->create(),
        ];
    }

    // ==========================================
    // Inventory Entity Methods
    // ==========================================

    /**
     * Create inventory for a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return Inventory
     */
    protected function createInventory(int $productId, int $warehouseId, int $quantity): Inventory
    {
        return Inventory::factory()->create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create inventory for a product in a warehouse with default quantity.
     *
     * @param int $warehouseId
     * @param int $quantity
     * @return object{product: Product, inventory: Inventory}
     */
    protected function createProductWithInventory(int $warehouseId, int $quantity = 100): object
    {
        $product = $this->createProduct();
        $inventory = $this->createInventory($product->id, $warehouseId, $quantity);

        return (object) [
            'product' => $product,
            'inventory' => $inventory,
        ];
    }

    // ==========================================
    // User Entity Methods
    // ==========================================

    /**
     * Create a user.
     *
     * @param array<string, mixed> $overrides
     * @return User
     */
    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }
}
