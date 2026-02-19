<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\Warehouse\CreateWarehouseDTO;
use App\DTO\Warehouse\TransferStockDTO;
use App\DTO\Warehouse\UpdateWarehouseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\TransferStockRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WarehouseController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly WarehouseService $service
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
            new Middleware('role:Admin', only: ['store', 'update', 'destroy']),
            new Middleware('role:Admin|Worker', only: ['transfer']),
        ];
    }

    /**
     * Display a listing of warehouses.
     */
    public function index(): AnonymousResourceCollection
    {
        $warehouses = $this->service->getAll(15);

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $dto = CreateWarehouseDTO::fromArray($request->validated());
        $warehouse = $this->service->create($dto);

        return response()->json([
            'message' => "The Warehouse '{$warehouse->name}' has been created successfully",
            'data' => new WarehouseResource($warehouse),
        ], 201);
    }

    /**
     * Get a single warehouse by id.
     *
     * @param Warehouse $warehouse
     * @return WarehouseResource
     */
    public function show(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
    }

    /**
     * Show a single warehouse by its slug.
     *
     * @param Warehouse $warehouse
     * @return JsonResponse
     */
    public function showBySlug(Warehouse $warehouse): JsonResponse
    {
        $warehouse->loadCount('inventories');

        return response()->json([
            'data' => new WarehouseResource($warehouse),
        ]);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $dto = UpdateWarehouseDTO::fromArray($request->validated());
        $warehouse = $this->service->update($warehouse, $dto);

        return response()->json([
            'message' => "The Warehouse '{$warehouse->name}' has been updated successfully",
            'data' => new WarehouseResource($warehouse),
        ], 200);
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->service->delete($warehouse);

        return response()->json([
            'message' => "The Warehouse has been deleted successfully",
        ], status: 200);
    }

    /**
    * Get the capacity metrics of a warehouse.
    *
    * @param Warehouse $warehouse
    * @return JsonResponse
    */
    public function capacity(Warehouse $warehouse): JsonResponse
    {
        $capacity = $this->service->getWarehouseCapacity($warehouse);

        return response()->json([
            'name' => $warehouse->name,
            'location' => $warehouse->location,
            ...$capacity,
        ]);
    }

    /**
     * Retrieve a list of all warehouses.
     *
     * @return AnonymousResourceCollection
     */
    public function listWarehouses(): AnonymousResourceCollection
    {
        $warehouses = $this->service->getAllWarehouses();

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Retrieve a list of all warehouses including their capacity metrics.
     *
     * @return AnonymousResourceCollection
     */
    public function listWarehousesWithCapacity(): AnonymousResourceCollection
    {
        $warehouses = $this->service->getWarehousesWithCapacity();

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Get all warehouses including their capacity metrics and inventory count.
     *
     * @return AnonymousResourceCollection
     */
    public function listWarehousesWithInventory(): AnonymousResourceCollection
    {
        $warehouses = $this->service->getWarehousesWithCapacity();

        return WarehouseResource::collection($warehouses);
    }

    /**
     * Transfer stock between warehouses.
     *
     * @param TransferStockRequest $request
     * @return JsonResponse
     */
    public function transfer(TransferStockRequest $request): JsonResponse
    {
        $dto = TransferStockDTO::fromRequest($request);
        $result = $this->service->transferBetweenWarehouses($dto);

        return response()->json([
            'message' => 'Transfer completed successfully',
            'data' => $result,
        ]);
    }
}
