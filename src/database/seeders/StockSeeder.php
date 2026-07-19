<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use RuntimeException;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (StockMovement::query()->exists()) {
            $this->command?->info('Demo stock data already exists.');

            return;
        }

        $products = Product::query()
            ->whereIn('sku', [
                'SKU-001',
                'SKU-002',
                'SKU-003',
                'SKU-004',
                'SKU-005',
                'SKU-006',
                'SKU-007',
                'SKU-008',
                'SKU-009',
                'SKU-010',
            ])
            ->get()
            ->keyBy('sku');

        $warehouses = Warehouse::query()
            ->whereIn('code', [
                'WH-MSK',
                'WH-SPB',
                'WH-KZN',
            ])
            ->get()
            ->keyBy('code');

        if ($products->count() !== 10 || $warehouses->count() !== 3) {
            throw new RuntimeException(
                'Products and warehouses must be seeded before stock data.'
            );
        }

        $operations = [
            [
                'sku' => 'SKU-001',
                'income' => 100,
                'transfer' => 30,
                'write_off' => 10,
            ],
            [
                'sku' => 'SKU-002',
                'income' => 80,
                'transfer' => 20,
                'write_off' => 5,
            ],
            [
                'sku' => 'SKU-003',
                'income' => 150,
                'transfer' => 40,
                'write_off' => 15,
            ],
            [
                'sku' => 'SKU-004',
                'income' => 200,
                'transfer' => 50,
                'write_off' => 20,
            ],
            [
                'sku' => 'SKU-005',
                'income' => 120,
                'transfer' => 30,
                'write_off' => 10,
            ],
            [
                'sku' => 'SKU-006',
                'income' => 70,
                'transfer' => 15,
                'write_off' => 5,
            ],
            [
                'sku' => 'SKU-007',
                'income' => 90,
                'transfer' => 25,
                'write_off' => 10,
            ],
            [
                'sku' => 'SKU-008',
                'income' => 60,
                'transfer' => 10,
                'write_off' => 5,
            ],
            [
                'sku' => 'SKU-009',
                'income' => 180,
                'transfer' => 45,
                'write_off' => 15,
            ],
            [
                'sku' => 'SKU-010',
                'income' => 110,
                'transfer' => 25,
                'write_off' => 10,
            ],
        ];

        $stockService = app(StockService::class);

        foreach ($operations as $operation) {
            $product = $products->get($operation['sku']);

            $stockService->income([
                'product_id' => $product->id,
                'warehouse_id' => $warehouses->get('WH-MSK')->id,
                'quantity' => $operation['income'],
                'comment' => 'Initial demo stock.',
            ]);

            $stockService->transfer([
                'product_id' => $product->id,
                'source_warehouse_id' => $warehouses->get('WH-MSK')->id,
                'target_warehouse_id' => $warehouses->get('WH-SPB')->id,
                'quantity' => $operation['transfer'],
                'comment' => 'Demo transfer between warehouses.',
            ]);

            $stockService->writeOff([
                'product_id' => $product->id,
                'warehouse_id' => $warehouses->get('WH-MSK')->id,
                'quantity' => $operation['write_off'],
                'comment' => 'Demo write-off.',
            ]);
        }
    }
}
