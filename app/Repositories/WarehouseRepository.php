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
        return Warehouse::paginate($perPage);
    }

    /**
     * Find a warehouse by ID.
     */
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    /**
     * Find a warehouse by slug.
     */
    public function findBySlug(string $slug): ?Warehouse
    {
        return Warehouse::where('slug', $slug)->first();
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
     * Get all warehouses with capacity information.
     */
    public function getWarehousesWithCapacity(): Collection
    {
        return Warehouse::orderBy('name')->get();
    }

    /**
     * Get all warehouses with inventory count.
     */
    public function getWarehousesWithInventoryCount(): Collection
    {
        return Warehouse::withCount('inventories')->get();
    }
}
