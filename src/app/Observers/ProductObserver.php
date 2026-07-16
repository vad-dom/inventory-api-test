<?php

namespace App\Observers;

use App\Models\Product;

readonly class ProductObserver extends BaseObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->forgetStockStatisticsCache();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->forgetStockStatisticsCache();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->forgetStockStatisticsCache();
    }
}
