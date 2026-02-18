<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Support\Collection;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    /**
     * Create a new stock movement.
     *
     * @param array<string, mixed> $data
     * @return StockMovement
     */
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

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
    ): StockMovement {
        return $this->create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => 'out',
            'quantity' => $quantity,
            'description' => $description,
            'user_id' => $userId,
        ]);
    }

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
    ): StockMovement {
        return $this->create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => 'in',
            'quantity' => $quantity,
            'description' => $description,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get stock movements for a specific product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return Collection
     */
    public function getByProductAndWarehouse(int $productId, int $warehouseId): Collection
    {
        return StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get current stock quantity for a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return int
     */
    public function getStockQuantity(int $productId, int $warehouseId): int
    {
        return (int) Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity');
    }

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
    ): array {
        $outMovement = $this->createOutMovement(
            $productId,
            $sourceWarehouseId,
            $quantity,
            $outDescription,
            $userId
        );

        $inMovement = $this->createInMovement(
            $productId,
            $destinationWarehouseId,
            $quantity,
            $inDescription,
            $userId
        );

        return [
            'out' => $outMovement,
            'in' => $inMovement,
        ];
    }
}
