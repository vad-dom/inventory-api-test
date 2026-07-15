<?php

namespace App\Repositories;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseRepository
{
    public function create(array $data): Warehouse
    {
        return Warehouse::query()
            ->create($data)
            ->refresh();
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->refresh();
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    public function findByIdOrFail(int $id): Warehouse
    {
        return Warehouse::query()->findOrFail($id);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $isActiveSet = array_key_exists('is_active', $filters);
        $isActive = $filters['is_active'] ?? null;

        $search = $filters['search'] ?? null;

        $perPage = $filters['per_page'] ?? null;

        return Warehouse::query()
            ->when(
                $search !== null && $search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%"),
                ),
            )
            ->when(
                $isActiveSet,
                fn ($query) => $query->where('is_active', $isActive),
            )
            ->paginate($perPage);
    }

    public function hasPositiveStock(Warehouse $warehouse): bool
    {
        return $warehouse->stockBalances()
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function hasMovements(Warehouse $warehouse): bool
    {
        return $warehouse->sourceStockMovements()->exists()
            || $warehouse->targetStockMovements()->exists();
    }

    public function getPositiveStockCount(Warehouse $warehouse): int
    {
        return $warehouse->stockBalances()
            ->where('quantity', '>', 0)
            ->count();
    }

    public function getTotalQuantity(Warehouse $warehouse): int
    {
        return (int) $warehouse->stockBalances()->sum('quantity');
    }
}
