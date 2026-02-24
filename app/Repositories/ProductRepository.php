<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

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

    public function getProductsByWarehouse(Warehouse $warehouse): BaseCollection
    {
        return $warehouse->inventories()
                ->get()
                ->pluck('product');
    }

  public function getProductsBySupplier(Supplier $supplier): Collection
    {
        return $supplier->products()
            ->with(['category', 'inventories'])
            ->get();
    }

    public function getProductsByCategory(Category  $category): Collection
    {
        return $category->products()
            ->with(['supplier', 'inventories'])
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
