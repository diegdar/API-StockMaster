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

        // Validate sufficient stock in source warehouse
        $currentStock = Inventory::where('product_id', $dto->productId)
            ->where('warehouse_id', $dto->sourceWarehouseId)
            ->value('quantity') ?? 0;

        if ($currentStock < $dto->quantity) {
            throw new InsufficientStockException($currentStock, $dto->quantity);
        }

        // Build description
        $baseDescription = $dto->description ?? '';
        $outDescription = "Transfer to {$destinationWarehouse->name}" . ($baseDescription ? ": {$baseDescription}" : '');
        $inDescription = "Transfer from {$sourceWarehouse->name}" . ($baseDescription ? ": {$baseDescription}" : '');

        // Execute transfer within transaction
        return DB::transaction(function () use ($dto, $product, $sourceWarehouse, $destinationWarehouse, $outDescription, $inDescription): array {
            $userId = auth()->id();

            // Create OUT movement
            $outMovement = $this->movementRepository->createOutMovement(
                $dto->productId,
                $dto->sourceWarehouseId,
                $dto->quantity,
                $outDescription,
                $userId
            );

            // Create IN movement
            $inMovement = $this->movementRepository->createInMovement(
                $dto->productId,
                $dto->destinationWarehouseId,
                $dto->quantity,
                $inDescription,
                $userId
            );

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
                'quantity' => $dto->quantity,
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
        });
    }

}
