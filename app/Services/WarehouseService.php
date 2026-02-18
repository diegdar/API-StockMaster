<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\Warehouse\CreateWarehouseDTO;
use App\DTO\Warehouse\TransferStockDTO;
use App\DTO\Warehouse\UpdateWarehouseDTO;
use App\Exceptions\DeletionException;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
        private readonly StockMovementRepositoryInterface $movementRepository
    ) {}

    /**
     * Get all warehouses with pagination.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($perPage);
    }

    /**
     * Create a new warehouse.
     */
    public function create(CreateWarehouseDTO $dto): Warehouse
    {
        return $this->repository->create($dto->toArray());
    }

    /**
     * Update an existing warehouse.
     */
    public function update(Warehouse $warehouse, UpdateWarehouseDTO $dto): ?Warehouse
    {
        return $this->repository->update($warehouse, $dto->toArray());
    }

    /**
     * Delete a warehouse if it has no inventory.
     *
     * @throws DeletionException if warehouse has inventory
     */
    public function delete(Warehouse $warehouse): void
    {
        if ($warehouse->inventories()->exists()) {
            throw new DeletionException('Cannot delete warehouse with existing inventories');
        }

        $this->repository->delete($warehouse);
    }

    /**
     * Get all warehouses information.
     */
    public function getAllWarehouses(): Collection
    {
        return $this->repository->getAllWarehouses();
    }

    /**
     * Get all warehouses with inventory count.
     */
    public function getWarehousesWithInventoryCount(): Collection
    {
        return $this->repository->getWarehousesWithInventoryCount();
    }

    /**
     * Get warehouse capacity information.
     *
     * @return array<string, mixed>|null
     */
    public function getWarehouseCapacity(Warehouse $warehouse): ?array
    {
        $usedCapacity = $warehouse->inventories()->sum('quantity');
        $totalCapacity = $warehouse->capacity;

        return [
            'total_capacity' => $totalCapacity,
            'used_capacity' => $usedCapacity,
            'available_capacity' => $totalCapacity !== null ? $totalCapacity - $usedCapacity : null,
            'utilization_percentage' => $totalCapacity !== null && $totalCapacity > 0
                ? round(($usedCapacity / $totalCapacity) * 100, 2)
                : null,
        ];
    }

    /**
     * Get all warehouses with their calculated capacity metrics.
     *
     * @return Collection<int, Warehouse>
     */
    public function getWarehousesWithCapacity(): Collection
    {
        $warehouses = $this->repository->getWarehousesWithInventoryCount();

        $warehouses->each(function (Warehouse $warehouse): void {
            $capacity = $this->getWarehouseCapacity($warehouse);
            $warehouse->total_capacity = $capacity['total_capacity'];
            $warehouse->used_capacity = $capacity['used_capacity'];
            $warehouse->available_capacity = $capacity['available_capacity'];
            $warehouse->utilization_percentage = $capacity['utilization_percentage'];
        });

        return $warehouses;
    }

    /**
     * Transfer stock between warehouses.
     *
     * @param TransferStockDTO $dto
     * @return array<string, mixed>
     * @throws InsufficientStockException
     */
    public function transferBetweenWarehouses(TransferStockDTO $dto): array
    {
        $sourceWarehouse = Warehouse::findOrFail($dto->sourceWarehouseId);
        $destinationWarehouse = Warehouse::findOrFail($dto->destinationWarehouseId);
        $product = Product::findOrFail($dto->productId);

        $this->validateSufficientStock($dto->productId, $dto->sourceWarehouseId, $dto->quantity);

        $descriptions = $this->buildMovementDescriptions(
            $sourceWarehouse->name,
            $destinationWarehouse->name,
            $dto->description
        );

        return DB::transaction(fn() => $this->executeTransfer(
            $dto,
            $product,
            $sourceWarehouse,
            $destinationWarehouse,
            $descriptions
        ));
    }

    /**
     * Validate that there is sufficient stock for the transfer.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @throws InsufficientStockException
     */
    private function validateSufficientStock(int $productId, int $warehouseId, int $quantity): void
    {
        $currentStock = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;

        if ($currentStock < $quantity) {
            throw new InsufficientStockException($currentStock, $quantity);
        }
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
        $userId = auth()->id();

        $outMovement = $this->movementRepository->createOutMovement(
            $dto->productId,
            $dto->sourceWarehouseId,
            $dto->quantity,
            $descriptions['out'],
            $userId
        );

        $inMovement = $this->movementRepository->createInMovement(
            $dto->productId,
            $dto->destinationWarehouseId,
            $dto->quantity,
            $descriptions['in'],
            $userId
        );

        return $this->buildTransferResponse(
            $product,
            $sourceWarehouse,
            $destinationWarehouse,
            $dto->quantity,
            $outMovement,
            $inMovement
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
