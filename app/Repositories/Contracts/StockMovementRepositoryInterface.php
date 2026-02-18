<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\StockMovement;
use Illuminate\Support\Collection;

interface StockMovementRepositoryInterface
{
    /**
     * Create a new stock movement.
     *
     * @param array<string, mixed> $data
     * @return StockMovement
     */
    public function create(array $data): StockMovement;

    /**
     * Create an OUT movement for stock transfer.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @param string $description
     * @param int $userId
     * @return StockMovement
     */
    public function createOutMovement(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $description,
        int $userId
    ): StockMovement;

    /**
     * Create an IN movement for stock transfer.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @param string $description
     * @param int $userId
     * @return StockMovement
     */
    public function createInMovement(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $description,
        int $userId
    ): StockMovement;

    /**
     * Get stock movements for a specific product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return Collection
     */
    public function getByProductAndWarehouse(int $productId, int $warehouseId): Collection;

    /**
     * Get current stock quantity for a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return int
     */
    public function getStockQuantity(int $productId, int $warehouseId): int;

    /**
     * Create transfer movements (OUT and IN) for stock transfer between warehouses.
     *
     * @param int $productId
     * @param int $sourceWarehouseId
     * @param int $destinationWarehouseId
     * @param int $quantity
     * @param string $outDescription
     * @param string $inDescription
     * @param int $userId
     * @return array{out: StockMovement, in: StockMovement}
     */
    public function createTransferMovements(
        int $productId,
        int $sourceWarehouseId,
        int $destinationWarehouseId,
        int $quantity,
        string $outDescription,
        string $inDescription,
        int $userId
    ): array;
}
