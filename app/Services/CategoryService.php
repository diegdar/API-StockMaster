<?php
declare(strict_types=1);

namespace App\Services;

use App\DTO\Category\CreateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Exceptions\DeletionException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {}

    public function getAllCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($perPage);
    }

    public function findCategoryById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    public function findCategoryBySlug(string $slug): ?Category
    {
        return $this->repository->findBySlug($slug);
    }

    public function createCategory(CreateCategoryDTO $dto): Category
    {
        return $this->repository->create($dto->toArray());
    }

    public function updateCategory(Category $category, UpdateCategoryDTO $dto): Category
    {
        return $this->repository->update($category, $dto->toArray());
    }

    public function deleteCategory(Category $category): bool
    {
        if ($category->products()->count() > 0) {
            throw new DeletionException(
                "Cannot delete category because it has associated products."
            );
        }

        return $this->repository->delete($category);
    }
}
