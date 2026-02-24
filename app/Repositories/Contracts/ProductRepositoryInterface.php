<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function getLowStockProducts(): Collection;

    public function getProductsByWarehouse(Warehouse $warehouse): BaseCollection;

    public function getProductsBySupplier(Supplier $supplier): Collection;

    public function getProductsByCategory(Category  $category): Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;
}
