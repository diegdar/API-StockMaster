<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    /**
     * Get all warehouses with pagination.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new warehouse.
     */
    public function create(array $data): Warehouse;

    /**
     * Update an existing warehouse.
     */
    public function update(Warehouse $warehouse, array $data): Warehouse;

    /**
     * Delete a warehouse.
     */
    public function delete(Warehouse $warehouse): void;

    /**
     * Get all warehouses information.
     */
    public function getAllWarehouses(): Collection;

    /**
     * Get all warehouses with inventory count.
     */
    public function getWarehousesWithInventoryCount(): Collection;

    /**
     * Find a warehouse by ID with its capacity information.
     *
     * @param int $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse;

    /**
     * Get available capacity for a warehouse.
     *
     * @param int $warehouseId
     * @return int|null Returns null if warehouse has no capacity limit
     */
    public function getAvailableCapacity(int $warehouseId): ?int;
}
