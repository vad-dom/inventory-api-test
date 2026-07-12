<?php

namespace App\Services;

use App\Exceptions\ApiBusinessException;
use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class WarehouseService
{
    public function __construct(
        private WarehouseRepository $warehouseRepository,
    ) {}

    public function create(array $data): Warehouse
    {
        return $this->warehouseRepository->create($data);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->warehouseRepository->paginate($filters);
    }

    public function getById(int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findByIdOrFail($id);

        $warehouse->setAttribute(
            'positive_stock_count',
            $this->warehouseRepository->getPositiveStockCount($warehouse),
        );

        $warehouse->setAttribute(
            'total_quantity',
            $this->warehouseRepository->getTotalQuantity($warehouse),
        );

        return $warehouse;
    }

    public function update(array $data, int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findByIdOrFail($id);

        return $this->warehouseRepository->update($warehouse, $data);
    }

    public function deactivate(int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findByIdOrFail($id);

        if ($this->warehouseRepository->hasPositiveStock($warehouse)) {
            throw new ApiBusinessException(
                errorCode: 'WAREHOUSE_HAS_STOCK',
                message: 'Warehouse has stock and cannot be deactivated.',
            );
        }

        return $this->warehouseRepository->update(
            $warehouse,
            [
                'is_active' => false,
            ],
        );
    }

    public function delete(int $id): void
    {
        $warehouse = $this->warehouseRepository->findByIdOrFail($id);

        if ($this->warehouseRepository->hasPositiveStock($warehouse)) {
            throw new ApiBusinessException(
                errorCode: 'WAREHOUSE_CANNOT_BE_DELETED',
                message: 'Warehouse has stock and cannot be deleted.',
            );
        }

        if ($this->warehouseRepository->hasMovements($warehouse)) {
            throw new ApiBusinessException(
                errorCode: 'WAREHOUSE_CANNOT_BE_DELETED',
                message: 'Warehouse has movements and cannot be deleted.',
            );
        }

        $this->warehouseRepository->delete($warehouse);
    }
}
