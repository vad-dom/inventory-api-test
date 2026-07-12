<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Requests\Warehouse\WarehousesRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Responses\ApiResponse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $warehouseService,
    ) {}

    public function index(WarehousesRequest $request): JsonResponse
    {
        $warehouses = $this->warehouseService->paginate($request->validated());

        return ApiResponse::success(
            data: WarehouseResource::collection($warehouses->items()),
            meta: [
                'page' => $warehouses->currentPage(),
                'per_page' => $warehouses->perPage(),
                'total' => $warehouses->total(),
            ],
        );
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseService->create($request->validated());

        return ApiResponse::success(
            data: WarehouseResource::make($warehouse),
            status: Response::HTTP_CREATED,
        );
    }

    public function show(int $id): JsonResponse
    {
        $warehouse = $this->warehouseService->getById($id);

        return ApiResponse::success(
            data: WarehouseResource::make($warehouse),
        );
    }

    public function update(UpdateWarehouseRequest $request, int $id): JsonResponse
    {
        $warehouse = $this->warehouseService->update($request->validated(), $id);

        return ApiResponse::success(
            data: WarehouseResource::make($warehouse),
        );
    }

    public function deactivate(int $id): JsonResponse
    {
        $warehouse = $this->warehouseService->deactivate($id);

        return ApiResponse::success(
            data: WarehouseResource::make($warehouse),
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->warehouseService->delete($id);

        return ApiResponse::success();
    }
}
