<?php

namespace App\Services;

use App\Exceptions\ApiBusinessException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\ProductRepository;
use App\Repositories\StockBalanceRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\WarehouseRepository;
use Illuminate\Support\Facades\DB;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

readonly class StockService
{
    public function __construct(
        private ProductRepository $productRepository,
        private WarehouseRepository $warehouseRepository,
        private StockBalanceRepository $stockBalanceRepository,
        private StockMovementRepository $stockMovementRepository,
    ) {}

    public function income(array $data): StockMovement
    {
        $product = $this->getActiveProduct($data['product_id']);
        $warehouse = $this->getActiveWarehouse($data['warehouse_id']);

        return DB::transaction(function () use ($data, $product, $warehouse): StockMovement {
            $this->stockBalanceRepository->ensureExists(
                $product->id,
                $warehouse->id,
            );

            $this->stockBalanceRepository->increment(
                $product->id,
                $warehouse->id,
                $data['quantity'],
            );

            return $this->stockMovementRepository->create([
                'product_id' => $product->id,
                'source_warehouse_id' => null,
                'target_warehouse_id' => $warehouse->id,
                'type' => StockMovement::TYPE_INCOME,
                'quantity' => $data['quantity'],
                'comment' => $data['comment'] ?? null,
            ]);
        }, 3);
    }

    public function writeOff(array $data): StockMovement
    {
        $product = $this->getActiveProduct($data['product_id']);
        $warehouse = $this->getActiveWarehouse($data['warehouse_id']);

        return DB::transaction(function () use ($data, $product, $warehouse): StockMovement {
            $decremented = $this->stockBalanceRepository->decrementIfEnough(
                $product->id,
                $warehouse->id,
                $data['quantity'],
            );

            if (! $decremented) {
                throw new ApiBusinessException(
                    errorCode: 'INSUFFICIENT_STOCK',
                    message: 'Not enough stock for this operation.',
                );
            }

            return $this->stockMovementRepository->create([
                'product_id' => $product->id,
                'source_warehouse_id' => $warehouse->id,
                'target_warehouse_id' => null,
                'type' => StockMovement::TYPE_WRITE_OFF,
                'quantity' => $data['quantity'],
                'comment' => $data['comment'] ?? null,
            ]);
        }, 3);
    }

    public function transfer(array $data): StockMovement
    {
        $product = $this->getActiveProduct($data['product_id']);

        [$sourceWarehouse, $targetWarehouse] = $this->getTransferWarehouses($data);

        return DB::transaction(function () use (
            $data,
            $product,
            $sourceWarehouse,
            $targetWarehouse,
        ): StockMovement {
            $this->stockBalanceRepository->ensureExists(
                $product->id,
                $targetWarehouse->id,
            );

            $balances = $this->stockBalanceRepository->getLocked(
                $product->id,
                [
                    $sourceWarehouse->id,
                    $targetWarehouse->id,
                ],
            );

            $sourceBalance = $balances->get($sourceWarehouse->id);
            $targetBalance = $balances->get($targetWarehouse->id);

            if (
                $sourceBalance === null
                || $sourceBalance->quantity < $data['quantity']
            ) {
                throw new ApiBusinessException(
                    errorCode: 'INSUFFICIENT_STOCK',
                    message: 'Not enough stock for this operation.',
                );
            }

            if ($targetBalance === null) {
                throw new LogicException('Target stock balance was not created.');
            }

            $this->stockBalanceRepository->update(
                $sourceBalance,
                [
                    'quantity' => $sourceBalance->quantity - $data['quantity'],
                ],
            );

            $this->stockBalanceRepository->update(
                $targetBalance,
                [
                    'quantity' => $targetBalance->quantity + $data['quantity'],
                ],
            );

            return $this->stockMovementRepository->create([
                'product_id' => $product->id,
                'source_warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'type' => StockMovement::TYPE_TRANSFER,
                'quantity' => $data['quantity'],
                'comment' => $data['comment'] ?? null,
            ]);
        }, 3);
    }

    private function getActiveProduct(int $id): Product
    {
        $product = $this->productRepository->findByIdOrFail($id);

        if (! $product->is_active) {
            throw new ApiBusinessException(
                errorCode: 'BAD_REQUEST',
                message: 'Product is inactive.',
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        return $product;
    }

    private function getActiveWarehouse(int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findByIdOrFail($id);

        if (! $warehouse->is_active) {
            throw new ApiBusinessException(
                errorCode: 'BAD_REQUEST',
                message: 'Warehouse is inactive.',
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        return $warehouse;
    }

    /**
     * @return array{0: Warehouse, 1: Warehouse}
     */
    private function getTransferWarehouses(array $data): array
    {
        return [
            $this->getActiveWarehouse($data['source_warehouse_id']),
            $this->getActiveWarehouse($data['target_warehouse_id']),
        ];
    }
}
