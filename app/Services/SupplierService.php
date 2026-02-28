<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Exceptions\DeletionException;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierService
{
    public function __construct(
        private SupplierRepositoryInterface $repository
    ) {}

    /**
     * Get all suppliers with pagination.
     */
    public function getAllSuppliers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($perPage);
    }

    /**
     * Create a new supplier.
     */
    public function createSupplier(CreateSupplierDTO $dto): Supplier
    {
        return $this->repository->create($dto->toArray());
    }

    /**
     * Update an existing supplier.
     */
    public function updateSupplier(Supplier $supplier, UpdateSupplierDTO $dto): Supplier
    {
        $data = array_filter($dto->toArray(), fn ($value) => $value !== null);

        return $this->repository->update($supplier, $data);
    }

    /**
     * Delete a supplier.
     * Throws DeletionException if supplier has associated products.
     */
    public function deleteSupplier(Supplier $supplier): void
    {
        if ($supplier->products()->count() > 0) {
            throw new DeletionException(
                "Cannot delete supplier '{$supplier->name}' because it has associated products."
            );
        }

        $this->repository->delete($supplier);
    }

    /**
     * Activate a supplier.
     */
    public function activateSupplier(Supplier $supplier): Supplier
    {
        return $this->repository->activate($supplier);
    }

    /**
     * Deactivate a supplier.
     */
    public function deactivateSupplier(Supplier $supplier): Supplier
    {
        return $this->repository->deactivate($supplier);
    }

    /**
     * Get supplier performance metrics.
     */
    public function getSupplierPerformance(Supplier $supplier): array
    {
        $totalProducts = $supplier->products()->count();

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'total_products' => $totalProducts,
            'is_active' => $supplier->is_active,
        ];
    }
}
