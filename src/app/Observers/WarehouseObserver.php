<?php

namespace App\Observers;

use App\Models\Warehouse;

readonly class WarehouseObserver extends BaseObserver
{
    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        $this->forgetStockStatisticsCache();
    }

    /**
     * Handle the Warehouse "updated" event.
     */
    public function updated(Warehouse $warehouse): void
    {
        $this->forgetStockStatisticsCache();
    }

    /**
     * Handle the Warehouse "deleted" event.
     */
    public function deleted(Warehouse $warehouse): void
    {
        $this->forgetStockStatisticsCache();
    }
}
