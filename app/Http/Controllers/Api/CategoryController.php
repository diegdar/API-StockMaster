<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\Category\CreateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Exceptions\DeletionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CategoryService $categoryService
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
        $categories = $this->categoryService->getAllCategories();
        $categories->loadCount('products');

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $dto = CreateCategoryDTO::fromArray($request->validated());
        $category = $this->categoryService->createCategory($dto);

        return response()->json([
            'message' => "The Category '{$category->name}' has been created successfully",
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category): CategoryResource
    {
        $category->loadCount('products');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $dto = UpdateCategoryDTO::fromArray($request->validated());

        $category = $this->categoryService->updateCategory($category, $dto);

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category);

        return response()->json([
            'message' => "The Category has been deleted successfully",
        ], 204);
    }
}
