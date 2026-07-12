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
        $perPage = $filters['per_page'] ?? null;

        return Warehouse::query()
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters): void {
                $query->where('is_active', $filters['is_active']);
            })
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
