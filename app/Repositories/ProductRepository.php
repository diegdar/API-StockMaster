<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'supplier'])->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'supplier', 'inventories'])->find($id);
    }

    public function getLowStockProducts(): Collection
    {
        return Product::whereHas('inventories', function ($query) {
            $query->whereRaw('quantity < products.min_stock_level');
        })->with(['inventories', 'category', 'supplier'])->get();
    }

    public function getProductsByWarehouse(int $warehouseId): Collection
    {
        return Product::whereHas('inventories', function ($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        })->with(['inventories' => function ($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }, 'category', 'supplier'])->get();
    }

    public function getProductsBySupplier(int $supplierId): Collection
    {
        return Product::where('supplier_id', $supplierId)
            ->with(['category', 'supplier', 'inventories'])
            ->get();
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::where('category_id', $categoryId)
            ->with(['category', 'supplier', 'inventories'])
            ->get();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['category', 'supplier', 'inventories']);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
