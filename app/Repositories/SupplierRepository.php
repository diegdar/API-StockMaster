<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository implements SupplierRepositoryInterface
{
    /**
     * Get all suppliers with pagination.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::withCount('products')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all suppliers with product count.
     */
    public function getSuppliersWithProductCount(): Collection
    {
        return Supplier::withCount('products')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new supplier.
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }

    /**
     * Delete a supplier.
     */
    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    /**
     * Activate a supplier.
     */
    public function activate(Supplier $supplier): Supplier
    {
        $supplier->update(['is_active' => true]);

        return $supplier->fresh();
    }

    /**
     * Deactivate a supplier.
     */
    public function deactivate(Supplier $supplier): Supplier
    {
        $supplier->update(['is_active' => false]);

        return $supplier->fresh();
    }
}
