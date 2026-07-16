<?php

namespace App\Observers;

use App\Models\StockMovement;

readonly class StockMovementObserver extends BaseObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        $this->forgetStockStatisticsCache();
    }
}
