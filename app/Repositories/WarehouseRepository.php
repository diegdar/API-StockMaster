<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Get all warehouses with pagination.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Warehouse::query()
            ->withCount('inventories')
            ->paginate($perPage);
    }

    /**
     * Create a new warehouse.
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    /**
     * Update an existing warehouse.
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->fresh();
    }

    /**
     * Delete a warehouse.
     */
    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    /**
     * Get all warehouses with inventory count.
     */
    public function getWarehousesWithInventoryCount(): Collection
    {
        return Warehouse::withCount('inventories')->get();
    }

    /**
     * Find a warehouse by ID.
     *
     * @param int $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    /**
     * Get the total quantity of all products in inventory for a given warehouse.
     *
     * @param Warehouse $warehouse
     * @return int
     */
    public function getUsedCapacity(Warehouse $warehouse): int
    {
        return  $warehouse
                    ->inventories()
                    ->sum('quantity');
    }
}
