<?php

namespace App\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class StockStatisticsCache
{
    private const string CACHE_KEY = 'statistics.stock';

    private const int CACHE_TTL_SECONDS = 60;

    public function remember(Closure $callback): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            $callback,
        );
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
