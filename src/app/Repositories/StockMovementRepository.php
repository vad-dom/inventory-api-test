<?php

namespace App\Repositories;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class StockMovementRepository
{
    public function create(array $data): StockMovement
    {
        return StockMovement::query()->create($data);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $productId = $filters['product_id'] ?? null;
        $warehouseId = $filters['warehouse_id'] ?? null;
        $type = $filters['type'] ?? null;

        $createdFrom = isset($filters['created_from'])
            ? Carbon::parse($filters['created_from'])->startOfDay()
            : null;

        $createdTo = isset($filters['created_to'])
            ? Carbon::parse($filters['created_to'])->endOfDay()
            : null;

        $sort = $filters['sort'] ?? null;
        $direction = $filters['direction'] ?? null;
        $isSortSet = $sort !== null && $direction !== null;

        $perPage = $filters['per_page'] ?? null;

        return StockMovement::query()
            ->with([
                'product',
                'sourceWarehouse',
                'targetWarehouse',
            ])
            ->when(
                $productId !== null,
                fn ($query) => $query->where('product_id', $productId),
            )
            ->when(
                $warehouseId !== null,
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->where('source_warehouse_id', $warehouseId)
                        ->orWhere('target_warehouse_id', $warehouseId),
                ),
            )
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type),
            )
            ->when(
                $createdFrom !== null,
                fn ($query) => $query->where('created_at', '>=', $createdFrom),
            )
            ->when(
                $createdTo !== null,
                fn ($query) => $query->where('created_at', '<=', $createdTo),
            )
            ->when(
                $isSortSet,
                fn ($query) => $query->orderBy($sort, $direction),
            )
            ->paginate($perPage);
    }
}
