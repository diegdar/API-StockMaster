<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;

/**
 * Trait for warehouse-related test helpers.
 * Consolidates warehouse transfer setup, assertions, and data providers.
 */
trait WarehouseTestTrait
{
    use EntityCreationTrait;
    use InteractsWithAuthentication;

    // ==========================================
    // Transfer Setup Methods
    // ==========================================

    /**
     * Setup transfer test data.
     *
     * @return array{product: Product, source: Warehouse, destination: Warehouse}
     */
    protected function setupTransferData(): array
    {
        $source = Warehouse::factory()->create(['is_active' => true]);
        $destination = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $source->id,
            'quantity' => 100,
        ]);

        return compact('product', 'source', 'destination');
    }

    /**
     * Setup transfer test with inventory.
     *
     * @param int $sourceQuantity
     * @param string $sourceName
     * @param string $destinationName
     * @return object{user: User, sourceWarehouse: Warehouse, destinationWarehouse: Warehouse, product: Product}
     */
    protected function setupTransferTest(
        int $sourceQuantity,
        string $sourceName = 'Source Warehouse',
        string $destinationName = 'Destination Warehouse'
    ): object {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sourceWarehouse = Warehouse::factory()->create(['name' => $sourceName]);
        $destinationWarehouse = Warehouse::factory()->create(['name' => $destinationName]);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => $sourceQuantity,
        ]);

        return (object) [
            'user' => $user,
            'sourceWarehouse' => $sourceWarehouse,
            'destinationWarehouse' => $destinationWarehouse,
            'product' => $product,
        ];
    }

    /**
     * Setup transfer test without inventory in source warehouse.
     *
     * @return object{user: User, sourceWarehouse: Warehouse, destinationWarehouse: Warehouse, product: Product}
     */
    protected function setupTransferTestWithoutInventory(): object
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return (object) [
            'user' => $user,
            'sourceWarehouse' => Warehouse::factory()->create(),
            'destinationWarehouse' => Warehouse::factory()->create(),
            'product' => Product::factory()->create(),
        ];
    }

    /**
     * Setup single warehouse transfer test.
     *
     * @param int $quantity
     * @return object{user: User, warehouse: Warehouse, product: Product}
     */
    protected function setupSingleWarehouseTransfer(int $quantity): object
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $warehouse = Warehouse::factory()->create(['name' => 'Single Warehouse']);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $quantity,
        ]);

        return (object) [
            'user' => $user,
            'warehouse' => $warehouse,
            'product' => $product,
        ];
    }

    /**
     * Setup transfer test with destination warehouse capacity.
     *
     * @param int $sourceQuantity
     * @param int|null $destinationCapacity
     * @return object{user: User, sourceWarehouse: Warehouse, destinationWarehouse: Warehouse, product: Product}
     */
    protected function setupTransferWithCapacity(
        int $sourceQuantity,
        ?int $destinationCapacity
    ): object {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sourceWarehouse = Warehouse::factory()->create(['name' => 'Source Warehouse']);
        $destinationWarehouse = Warehouse::factory()->create([
            'name' => 'Destination Warehouse',
            'capacity' => $destinationCapacity,
        ]);
        $product = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => $sourceQuantity,
        ]);

        return (object) [
            'user' => $user,
            'sourceWarehouse' => $sourceWarehouse,
            'destinationWarehouse' => $destinationWarehouse,
            'product' => $product,
        ];
    }

    // ==========================================
    // Assertion Methods
    // ==========================================

    /**
     * Assert transfer response structure and values.
     *
     * @param array<string, mixed> $result
     * @param Product $product
     * @param Warehouse $sourceWarehouse
     * @param Warehouse $destinationWarehouse
     * @param int $quantity
     */
    protected function assertTransferResponse(
        array $result,
        Product $product,
        Warehouse $sourceWarehouse,
        Warehouse $destinationWarehouse,
        int $quantity
    ): void {
        $this->assertEquals($product->id, $result['product']['id']);
        $this->assertEquals($product->name, $result['product']['name']);
        $this->assertEquals($sourceWarehouse->id, $result['source_warehouse']['id']);
        $this->assertEquals($destinationWarehouse->id, $result['destination_warehouse']['id']);
        $this->assertEquals($quantity, $result['quantity']);
        $this->assertEquals('out', $result['movements']['out']['type']);
        $this->assertEquals($quantity, $result['movements']['out']['quantity']);
        $this->assertEquals('in', $result['movements']['in']['type']);
        $this->assertEquals($quantity, $result['movements']['in']['quantity']);
    }

    /**
     * Assert inventory quantities after transfer.
     *
     * @param int $productId
     * @param int $sourceWarehouseId
     * @param int $expectedSourceQuantity
     * @param int $destinationWarehouseId
     * @param int $expectedDestinationQuantity
     */
    protected function assertInventoryQuantities(
        int $productId,
        int $sourceWarehouseId,
        int $expectedSourceQuantity,
        int $destinationWarehouseId,
        int $expectedDestinationQuantity
    ): void {
        $sourceInventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $sourceWarehouseId)
            ->first();
        $destinationInventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $destinationWarehouseId)
            ->first();

        $this->assertEquals($expectedSourceQuantity, $sourceInventory->quantity);
        $this->assertEquals($expectedDestinationQuantity, $destinationInventory->quantity);
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

    // ==========================================
    // Data Providers
    // ==========================================

    /**
     * Data provider for users that can read resources.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCanRead(): array
    {
        return [
            'Admin' => ['Admin'],
            'Worker' => ['Worker'],
            'Viewer' => ['Viewer'],
        ];
    }

    /**
     * Data provider for users that cannot create/update/delete resources.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCannotManage(): array
    {
        return [
            'Worker' => ['Worker'],
            'Viewer' => ['Viewer'],
        ];
    }

    /**
     * Data provider for users that can transfer stock.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCanTransfer(): array
    {
        return [
            'Admin' => ['Admin'],
            'Worker' => ['Worker'],
        ];
    }
}
