<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories\Traits;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

trait RepositoryTestTrait
{
    /**
     * Create basic test entities (product, warehouse, user).
     *
     * @return object{product: Product, warehouse: Warehouse, user: User}
     */
    protected function createTestEntities(): object
    {
        return (object) [
            'product' => Product::factory()->create(),
            'warehouse' => Warehouse::factory()->create(),
            'user' => User::factory()->create(),
        ];
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
     * Assert stock movement exists in database.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param string $type
     * @param int $quantity
     * @param string|null $description
     */
    protected function assertStockMovementExists(
        int $productId,
        int $warehouseId,
        string $type,
        int $quantity,
        ?string $description = null
    ): void {
        $data = [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'quantity' => $quantity,
        ];

        if ($description !== null) {
            $data['description'] = $description;
        }

        $this->assertDatabaseHas('stock_movements', $data);
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
     * Create multiple suppliers.
     *
     * @param int $count
     * @return Collection<int, Supplier>
     */
    protected function createSuppliers(int $count): Collection
    {
        return Supplier::factory()->count($count)->create();
    }
}
