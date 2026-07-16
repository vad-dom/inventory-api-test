<?php

namespace App\Observers;

use App\Cache\StockStatisticsCache;

abstract readonly class BaseObserver
{
    public function __construct(
        protected StockStatisticsCache $statisticsCache,
    ) {}

    protected function forgetStockStatisticsCache(): void
    {
        $this->statisticsCache->forget();
    }
}
