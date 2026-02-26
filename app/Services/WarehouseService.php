<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\Warehouse\CreateWarehouseDTO;
use App\DTO\Warehouse\TransferStockDTO;
use App\DTO\Warehouse\UpdateWarehouseDTO;
use App\Exceptions\DeletionException;
use App\Exceptions\InsufficientCapacityException;
use App\Exceptions\InsufficientStockException;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Services\Traits\WarehouseTransferTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    use WarehouseTransferTrait;

    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /**
     * List all warehouses with theris inventory.
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
     * @throws InsufficientCapacityException
     */
    public function transferBetweenWarehouses(TransferStockDTO $dto): array
    {
        $sourceWarehouse = $this->getWarehouseOrFail($dto->sourceWarehouseId);
        $destinationWarehouse = $this->getWarehouseOrFail($dto->destinationWarehouseId);
        $product = $this->getProductOrFail($dto->productId);

        $this->validateSufficientStock($dto->productId, $dto->sourceWarehouseId, $dto->quantity);
        $this->validateDestinationCapacity($dto->destinationWarehouseId, $dto->quantity);

        $descriptions = $this->buildMovementDescriptions(
            $sourceWarehouse->name,
            $destinationWarehouse->name,
            $dto->description
        );

        return DB::transaction(fn() =>
            $this->executeTransfer(
                $dto,
                $product,
                $sourceWarehouse,
                $destinationWarehouse,
                $descriptions
            )
        );
    }
}
