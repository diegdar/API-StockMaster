<?php
declare(strict_types=1);

namespace App\Services\Traits;

use App\DTO\Warehouse\TransferStockDTO;
use App\Exceptions\InsufficientCapacityException;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Warehouse;

trait WarehouseTransferTrait
{
    /**
     * Get a warehouse by ID or fail.
     *
     * @param int $id
     * @return Warehouse
     */
    private function getWarehouseOrFail(int $id): Warehouse
    {
        $warehouse = $this->repository->findById($id);

        if ($warehouse === null) {
            abort(404, "Warehouse with ID {$id} not found");
        }

        return $warehouse;
    }

    /**
     * Get a product by ID or fail.
     *
     * @param int $id
     * @return Product
     */
    private function getProductOrFail(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            abort(404, "Product with ID {$id} not found");
        }

        return $product;
    }

    /**
     * Validate that there is sufficient stock in the warehouse source for the transfer.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @throws InsufficientStockException
     */
    private function validateSufficientStock(int $productId, int $warehouseId, int $quantity): void
    {
        $currentStock = $this->movementRepository->getStockQuantity($productId, $warehouseId);

        if ($currentStock < $quantity) {
            throw new InsufficientStockException($currentStock, $quantity);
        }
    }

    /**
     * Validate that destination warehouse has sufficient capacity.
     *
     * @param int $warehouseId
     * @param int $quantity
     * @throws InsufficientCapacityException
     */
    private function validateDestinationCapacity(int $warehouseId, int $quantity): void
    {
        $availableCapacity = $this->getAvailableCapacity($warehouseId);

        // If null, warehouse has no capacity limit
        if ($availableCapacity === null) {
            return;
        }

        if ($availableCapacity < $quantity) {
            throw new InsufficientCapacityException($availableCapacity, $quantity);
        }
    }

    public function getAvailableCapacity(int $warehouseId): ?int
    {
        $warehouse = $this->repository->findById($warehouseId);

        if (is_null($warehouse?->capacity)) {
            return null;
        }

        $usedCapacity = $this->repository
                        ->getUsedCapacity($warehouse);

        return max(0, $warehouse->capacity - $usedCapacity);
    }

    /**
     * Build movement descriptions for OUT and IN movements.
     *
     * @param string $sourceWarehouseName
     * @param string $destinationWarehouseName
     * @param string|null $baseDescription
     * @return array{out: string, in: string}
     */
    private function buildMovementDescriptions(
        string $sourceWarehouseName,
        string $destinationWarehouseName,
        ?string $baseDescription
    ): array {
        $suffix = $baseDescription ? ": {$baseDescription}" : '';

        return [
            'out' => "Transfer to {$destinationWarehouseName}{$suffix}",
            'in' => "Transfer from {$sourceWarehouseName}{$suffix}",
        ];
    }

    /**
     * Execute the transfer within a transaction.
     *
     * @param TransferStockDTO $dto
     * @param Product $product
     * @param Warehouse $sourceWarehouse
     * @param Warehouse $destinationWarehouse
     * @param array{out: string, in: string} $descriptions
     * @return array<string, mixed>
     */
    private function executeTransfer(
        TransferStockDTO $dto,
        Product $product,
        Warehouse $sourceWarehouse,
        Warehouse $destinationWarehouse,
        array $descriptions
    ): array {
        $movements = $this->movementRepository->createTransferMovements(
            $dto->productId,
            $dto->sourceWarehouseId,
            $dto->destinationWarehouseId,
            $dto->quantity,
            $descriptions['out'],
            $descriptions['in'],
            auth()->id()
        );

        return $this->buildTransferResponse(
            $product,
            $sourceWarehouse,
            $destinationWarehouse,
            $dto->quantity,
            $movements['out'],
            $movements['in']
        );
    }

    /**
     * Build the transfer response array.
     *
     * @param Product $product
     * @param Warehouse $sourceWarehouse
     * @param Warehouse $destinationWarehouse
     * @param int $quantity
     * @param mixed $outMovement
     * @param mixed $inMovement
     * @return array<string, mixed>
     */
    private function buildTransferResponse(
        Product $product,
        Warehouse $sourceWarehouse,
        Warehouse $destinationWarehouse,
        int $quantity,
        mixed $outMovement,
        mixed $inMovement
    ): array {
        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ],
            'source_warehouse' => [
                'id' => $sourceWarehouse->id,
                'name' => $sourceWarehouse->name,
                'slug' => $sourceWarehouse->slug,
            ],
            'destination_warehouse' => [
                'id' => $destinationWarehouse->id,
                'name' => $destinationWarehouse->name,
                'slug' => $destinationWarehouse->slug,
            ],
            'quantity' => $quantity,
            'movements' => [
                'out' => [
                    'id' => $outMovement->id,
                    'type' => $outMovement->type,
                    'quantity' => $outMovement->quantity,
                    'description' => $outMovement->description,
                    'created_at' => $outMovement->created_at->toISOString(),
                ],
                'in' => [
                    'id' => $inMovement->id,
                    'type' => $inMovement->type,
                    'quantity' => $inMovement->quantity,
                    'description' => $inMovement->description,
                    'created_at' => $inMovement->created_at->toISOString(),
                ],
            ],
        ];
    }
}
