<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StatisticsRepository
{
    private const int LOW_STOCK_LOWER_BOUND = 0;

    private const int LOW_STOCK_UPPER_BOUND = 5;

    public function getProductsTotal(): int
    {
        return Product::query()->count();
    }

    public function getWarehousesTotal(): int
    {
        return Warehouse::query()->count();
    }

    public function getPositionsWithStock(): int
    {
        return StockBalance::query()
            ->where('quantity', '>', 0)
            ->distinct()
            ->count('product_id');
    }

    public function getTotalQuantity(): int
    {
        return (int) StockBalance::query()->sum('quantity');
    }

    public function getByWarehouse(): array
    {
        $rows = DB::select(<<<'SQL'
            SELECT
                warehouses.id AS warehouse_id,
                warehouses.code AS warehouse_code,
                warehouses.name AS warehouse_name,
                COUNT(stock_balances.product_id) AS positions,
                SUM(stock_balances.quantity) AS quantity
            FROM stock_balances
            INNER JOIN warehouses
                ON warehouses.id = stock_balances.warehouse_id
            WHERE stock_balances.quantity > 0
            GROUP BY
                warehouses.id,
                warehouses.code,
                warehouses.name
        SQL);

        return array_map(
            static fn (object $row): array => [
                'warehouse_id' => (int) $row->warehouse_id,
                'warehouse_code' => $row->warehouse_code,
                'warehouse_name' => $row->warehouse_name,
                'positions' => (int) $row->positions,
                'quantity' => (int) $row->quantity,
            ],
            $rows,
        );
    }

    public function getLowStock(): array
    {
        $rows = DB::select(<<<'SQL'
            SELECT
                products.id AS product_id,
                products.sku AS product_sku,
                products.name AS product_name,
                SUM(stock_balances.quantity) AS quantity
            FROM stock_balances
            INNER JOIN products
                ON products.id = stock_balances.product_id
            GROUP BY
                products.id,
                products.sku,
                products.name
            HAVING
                SUM(stock_balances.quantity) > :lowerBound
                AND SUM(stock_balances.quantity) < :upperBound
        SQL,
            [
                'lowerBound' => self::LOW_STOCK_LOWER_BOUND,
                'upperBound' => self::LOW_STOCK_UPPER_BOUND,
            ],
        );

        return array_map(
            static fn (object $row): array => [
                'product_id' => (int) $row->product_id,
                'sku' => $row->product_sku,
                'name' => $row->product_name,
                'total_quantity' => (int) $row->quantity,
            ],
            $rows,
        );
    }
}
