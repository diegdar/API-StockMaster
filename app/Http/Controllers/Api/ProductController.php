<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProductService $productService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('role:Admin', only: ['store', 'update', 'destroy']),
        ];
    }

    /**
     * Retrieve a paginated list of products.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getAll(15);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductRequest $request the store product request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = CreateProductDTO::fromArray($request->validated());
        $product = $this->productService->create($dto->toArray());

        return response()->json([
            'message' => "The Product '{$product->name}' has been created successfully",
        ], 201);
    }

    /**
     * Get a single product by id.
     *
     * @param int $product_id the product id to show
     * @return ProductResource
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Get a single product by sku.
     *
     * @param string $product_sku the product sku
     * @return ProductResource The product resource
     */
    public function showBySku(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Update a product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse|ProductResource
     *
     * @throws ValidationException if the request data is invalid
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse|ProductResource
    {
        $dto = UpdateProductDTO::fromArray($request->validated());

        if (!$dto->hasAnyField()) {
            return response()->json([
                'message' => 'No fields to update',
            ], 422);
        }

        $updatedProduct = $this->productService->update($product, $dto->toArray());

        return response()->json([
            'message' => "The Product '{$updatedProduct->name}' has been updated successfully",
        ], 200);
    }

    /**
     * Delete a product.
     *
     * @param int $prodcut_id the product id
     * @return JsonResponse A JSON response with a success message
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return response()->json([
            'message' => "The Product has been deleted successfully",
        ], 200);
    }

    /**
     * Get products by warehouse id.
     *
     * @param Warehouse $warehouse The warehouse id to get products from
     * @return AnonymousResourceCollection A collection of ProductResource
     */
    public function getProductsByWarehouseId(Warehouse $warehouse): AnonymousResourceCollection
    {
        $products = $this->productService->getProductsByWarehouse($warehouse);

        return ProductResource::collection($products);
    }

    /**
     * Get products by supplier id.
     *
     * @param Supplier $supplier The supplier to get products from
     * @return AnonymousResourceCollection A collection of ProductResource
     */
    public function getProductsBySupplierId(Supplier $supplier): AnonymousResourceCollection
    {
        $products = $this->productService->getProductsBySupplier($supplier);

        return ProductResource::collection($products);
    }

    /**
     * Get products by category id.
     *
     * @param Category $category The category to get products from
     * @return AnonymousResourceCollection A collection of ProductResource
     */
    public function getProductsByCategoryId(Category $category): AnonymousResourceCollection
    {
        $products = $this->productService->getProductsByCategory($category);

        return ProductResource::collection($products);
    }




}
