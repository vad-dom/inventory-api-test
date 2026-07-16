<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
    ) {}

    public function stock(): JsonResponse
    {
        return ApiResponse::success(
            data: $this->statisticsService->getStockStatistics(),
        );
    }
}
