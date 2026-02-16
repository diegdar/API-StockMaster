<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    public function getLowStockProducts(): Collection;

    public function getProductsByWarehouse(int $warehouseId): Collection;

    public function getProductsBySupplier(int $supplierId): Collection;

    public function getProductsByCategory(int $categoryId): Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;
}
