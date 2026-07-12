<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    private const string DEFAULT_SORT = 'created_at';

    private const string DEFAULT_DIRECTION = 'desc';

    private const int DEFAULT_PER_PAGE = 15;

    public function create(array $data): Product
    {
        return Product::query()
            ->create($data)
            ->refresh();
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function findByIdOrFail(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? self::DEFAULT_SORT;
        $direction = $filters['direction'] ?? self::DEFAULT_DIRECTION;
        $perPage = $filters['per_page'] ?? self::DEFAULT_PER_PAGE;

        return Product::query()
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($query) use ($search): void {
                    $query->where('sku', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters): void {
                $query->where('is_active', $filters['is_active']);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);
    }

    public function hasPositiveStock(Product $product): bool
    {
        return $product->stockBalances()
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function hasMovements(Product $product): bool
    {
        return $product->stockMovements()->exists();
    }

    public function getTotalQuantity(Product $product): int
    {
        return (int) $product->stockBalances()->sum('quantity');
    }

    public function loadStockBalances(Product $product): Product
    {
        return $product->load('stockBalances.warehouse');
    }
}
