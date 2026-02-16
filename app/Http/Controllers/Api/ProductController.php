<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getAll(15);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $dto = CreateProductDTO::fromArray($request->validated());
        $product = $this->productService->create($dto->toArray());

        return response()->json([
            'message' => "The Product '{$product->name}' has been created successfully",
        ], 201);
    }

    public function show(Product $product): ProductResource
    {
        $product = $this->productService->findById($product->id);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse|ProductResource
    {
        $dto = UpdateProductDTO::fromArray($request->validated());

        if (!$dto->hasAnyField()) {
            return response()->json([
                'message' => 'No fields to update',
            ], 422);
        }

        $updatedProduct = $this->productService->update($product, $dto->toArray());

        return new ProductResource($updatedProduct);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return response()->json([
            'message' => "The Product has been deleted successfully",
        ], 200);
    }
}
