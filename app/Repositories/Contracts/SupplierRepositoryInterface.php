<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface
{
    /**
     * Get all suppliers with pagination.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all suppliers with product count.
     */
    public function getSuppliersWithProductCount(): Collection;

    /**
     * Create a new supplier.
     */
    public function create(array $data): Supplier;

    /**
     * Update an existing supplier.
     */
    public function update(Supplier $supplier, array $data): Supplier;

    /**
     * Delete a supplier.
     */
    public function delete(Supplier $supplier): void;

    /**
     * Activate a supplier.
     */
    public function activate(Supplier $supplier): Supplier;

    /**
     * Deactivate a supplier.
     */
    public function deactivate(Supplier $supplier): Supplier;
}
