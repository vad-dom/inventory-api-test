<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\IncomeRequest;
use App\Http\Requests\Stock\StockBalancesRequest;
use App\Http\Requests\Stock\StockMovementsRequest;
use App\Http\Requests\Stock\TransferRequest;
use App\Http\Requests\Stock\WriteOffRequest;
use App\Http\Resources\StockBalanceResource;
use App\Http\Resources\StockMovementResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function income(IncomeRequest $request): JsonResponse
    {
        $movement = $this->stockService->income($request->validated());

        return ApiResponse::success(
            data: StockMovementResource::make($movement),
            status: Response::HTTP_CREATED,
        );
    }

    public function writeOff(WriteOffRequest $request): JsonResponse
    {
        $movement = $this->stockService->writeOff($request->validated());

        return ApiResponse::success(
            data: StockMovementResource::make($movement),
            status: Response::HTTP_CREATED,
        );
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $movement = $this->stockService->transfer($request->validated());

        return ApiResponse::success(
            data: StockMovementResource::make($movement),
            status: Response::HTTP_CREATED,
        );
    }

    public function balances(StockBalancesRequest $request): JsonResponse
    {
        $balances = $this->stockService->paginateBalances($request->validated());

        return ApiResponse::success(
            data: StockBalanceResource::collection($balances->items()),
            meta: [
                'page' => $balances->currentPage(),
                'per_page' => $balances->perPage(),
                'total' => $balances->total(),
            ],
        );
    }

    public function balance(Product $product, Warehouse $warehouse): JsonResponse
    {
        $balance = $this->stockService->getBalance($product, $warehouse);

        return ApiResponse::success(
            data: StockBalanceResource::make($balance),
        );
    }

    public function movements(StockMovementsRequest $request): JsonResponse
    {
        $movements = $this->stockService->paginateMovements($request->validated());

        return ApiResponse::success(
            data: StockMovementResource::collection($movements->items()),
            meta: [
                'page' => $movements->currentPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        );
    }
}
