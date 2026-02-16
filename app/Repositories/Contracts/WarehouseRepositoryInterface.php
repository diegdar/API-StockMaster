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
     * Find a warehouse by ID.
     */
    public function findById(int $id): ?Warehouse;

    /**
     * Find a warehouse by slug.
     */
    public function findBySlug(string $slug): ?Warehouse;

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
     * Get all warehouses with capacity information.
     */
    public function getWarehousesWithCapacity(): Collection;

    /**
     * Get all warehouses with inventory count.
     */
    public function getWarehousesWithInventoryCount(): Collection;
}
