<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DeletionException;
use App\Models\Product;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage);
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->productRepository->update($product, $data);
    }

    public function delete(Product $product): void
    {
        if ($product->inventories()->exists()) {
            throw new DeletionException("Cannot delete product because it has inventory records. Adjust inventory to zero first.");
        }

        if ($product->stockMovements()->exists()) {
            throw new DeletionException("Cannot delete product because it has stock movement records. This data is required for auditing.");
        }

        if ($product->restockAlerts()->where('is_active', true)->exists()) {
            throw new DeletionException("Cannot delete product because it has active restock alerts. Resolve alerts first.");
        }

        $this->productRepository->delete($product);
    }
}
