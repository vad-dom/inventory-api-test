<?php

namespace App\Services;

use App\Cache\StockStatisticsCache;
use App\Repositories\StatisticsRepository;

readonly class StatisticsService
{
    public function __construct(
        private StatisticsRepository $statisticsRepository,
        private StockStatisticsCache $statisticsCache,
    ) {}

    public function getStockStatistics(): array
    {
        return $this->statisticsCache->remember(
            fn (): array => [
                'products_total' => $this->statisticsRepository->getProductsTotal(),
                'warehouses_total' => $this->statisticsRepository->getWarehousesTotal(),
                'positions_with_stock' => $this->statisticsRepository->getPositionsWithStock(),
                'total_quantity' => $this->statisticsRepository->getTotalQuantity(),
                'by_warehouse' => $this->statisticsRepository->getByWarehouse(),
                'low_stock' => $this->statisticsRepository->getLowStock(),
            ],
        );
    }
}
