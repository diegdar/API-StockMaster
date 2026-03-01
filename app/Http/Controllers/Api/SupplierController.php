<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourcesCollection;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierService $supplierService
    ) {}

    /**
     * Display a listing of the suppliers.
     */
    public function index()
    {
        $suppliers = $this->supplierService->getAllSuppliers();

        return SupplierResource::collection($suppliers);
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $dto = CreateSupplierDTO::fromArray($request->all());
        $supplier = $this->supplierService->createSupplier($dto);

        return response()->json([
            'message' => "The Supplier '{$supplier->name}' has been created successfully",
            'data' => new SupplierResource($supplier),
        ], 201);
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): SupplierResource
    {
        $supplier->loadCount('products');

        return new SupplierResource($supplier);
    }

    /**
     * Display the specified supplier by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $supplier = Supplier::where('slug', $slug)->firstOrFail();
        $supplier->loadCount('products');

        return response()->json([
            'data' => new SupplierResource($supplier),
        ]);
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $dto = UpdateSupplierDTO::fromArray($request->all());
        $supplier = $this->supplierService->updateSupplier($supplier, $dto);

        return response()->json([
            'message' => "The Supplier '{$supplier->name}' has been updated successfully",
            'data' => new SupplierResource($supplier),
        ]);
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->supplierService->deleteSupplier($supplier);

        return response()->json([
            'message' => "The Supplier '{$supplier->name}' has been deleted successfully",
        ]);
    }
}
