<?php

namespace App\Repositories;

use App\Models\StockBalance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StockBalanceRepository
{
    public function getLocked(
        int $productId,
        array $warehouseIds,
    ): Collection {
        return StockBalance::query()
            ->where('product_id', $productId)
            ->whereIn('warehouse_id', $warehouseIds)
            ->orderBy('warehouse_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('warehouse_id');
    }

    public function create(array $data): StockBalance
    {
        return StockBalance::query()->create($data);
    }

    public function update(
        StockBalance $stockBalance,
        array $data,
    ): StockBalance {
        $stockBalance->update($data);

        return $stockBalance->refresh();
    }

    public function ensureExists(int $productId, int $warehouseId): void
    {
        StockBalance::query()->insertOrIgnore([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function increment(
        int $productId,
        int $warehouseId,
        int $quantity,
    ): void {
        StockBalance::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->increment('quantity', $quantity);
    }

    public function decrementIfEnough(
        int $productId,
        int $warehouseId,
        int $quantity,
    ): bool {
        $updatedRowCount = StockBalance::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        return $updatedRowCount === 1;
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? null;
        $direction = $filters['direction'] ?? null;
        $isSortSet = $sort !== null && $direction !== null;

        $perPage = $filters['per_page'] ?? null;

        return StockBalance::query()
            ->with(['product', 'warehouse'])
            ->when(
                isset($filters['product_id']),
                fn ($query) => $query->where('product_id', $filters['product_id']),
            )
            ->when(
                isset($filters['warehouse_id']),
                fn ($query) => $query->where('warehouse_id', $filters['warehouse_id']),
            )
            ->when(
                ($filters['only_positive'] ?? false) === true,
                fn ($query) => $query->where('quantity', '>', 0),
            )
            ->when(
                $isSortSet,
                fn ($query) => $query->orderBy($sort, $direction),
            )
            ->paginate($perPage);
    }

    public function findOrZeroBalance(
        int $productId,
        int $warehouseId,
    ): StockBalance {
        return StockBalance::query()->firstOrNew(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => 0,
            ],
        );
    }
}
