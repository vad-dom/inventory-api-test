<?php

namespace Tests\Feature\Api;

use App\Cache\StockStatisticsCache;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_statistics_are_calculated_correctly(): void
    {
        $firstProduct = Product::query()->create([
            'sku' => 'PRODUCT-001',
            'name' => 'First product',
            'is_active' => true,
        ]);

        $lowStockProduct = Product::query()->create([
            'sku' => 'PRODUCT-002',
            'name' => 'Low stock product',
            'is_active' => true,
        ]);

        Product::query()->create([
            'sku' => 'PRODUCT-003',
            'name' => 'Product without stock',
            'is_active' => true,
        ]);

        $mainWarehouse = Warehouse::query()->create([
            'code' => 'MAIN',
            'name' => 'Main warehouse',
            'is_active' => true,
        ]);

        $secondaryWarehouse = Warehouse::query()->create([
            'code' => 'SECONDARY',
            'name' => 'Secondary warehouse',
            'is_active' => true,
        ]);

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/income', [
                'product_id' => $firstProduct->id,
                'warehouse_id' => $mainWarehouse->id,
                'quantity' => 10,
            ])
            ->assertCreated();

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/transfer', [
                'product_id' => $firstProduct->id,
                'source_warehouse_id' => $mainWarehouse->id,
                'target_warehouse_id' => $secondaryWarehouse->id,
                'quantity' => 4,
            ])
            ->assertCreated();

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/income', [
                'product_id' => $lowStockProduct->id,
                'warehouse_id' => $mainWarehouse->id,
                'quantity' => 3,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $firstProduct->id,
            'warehouse_id' => $mainWarehouse->id,
            'quantity' => 6,
        ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $firstProduct->id,
            'warehouse_id' => $secondaryWarehouse->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $lowStockProduct->id,
            'warehouse_id' => $mainWarehouse->id,
            'quantity' => 3,
        ]);

        $response = $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->getJson('/api/v1/statistics/stock');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.products_total', 3)
            ->assertJsonPath('data.warehouses_total', 2)
            ->assertJsonPath('data.positions_with_stock', 2)
            ->assertJsonPath('data.total_quantity', 13)
            ->assertJsonCount(2, 'data.by_warehouse')
            ->assertJsonCount(1, 'data.low_stock')
            ->assertJsonFragment([
                'warehouse_id' => $mainWarehouse->id,
                'warehouse_code' => 'MAIN',
                'warehouse_name' => 'Main warehouse',
                'positions' => 2,
                'quantity' => 9,
            ])
            ->assertJsonFragment([
                'warehouse_id' => $secondaryWarehouse->id,
                'warehouse_code' => 'SECONDARY',
                'warehouse_name' => 'Secondary warehouse',
                'positions' => 1,
                'quantity' => 4,
            ])
            ->assertJsonFragment([
                'product_id' => $lowStockProduct->id,
                'sku' => 'PRODUCT-002',
                'name' => 'Low stock product',
                'total_quantity' => 3,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products_total',
                    'warehouses_total',
                    'positions_with_stock',
                    'total_quantity',
                    'by_warehouse' => [
                        '*' => [
                            'warehouse_id',
                            'warehouse_code',
                            'warehouse_name',
                            'positions',
                            'quantity',
                        ],
                    ],
                    'low_stock' => [
                        '*' => [
                            'product_id',
                            'sku',
                            'name',
                            'total_quantity',
                        ],
                    ],
                ],
                'meta' => [
                    'request_id',
                ],
            ]);
    }

    public function test_stock_statistics_cache_is_invalidated_after_product_is_created(): void
    {
        $statisticsCache = app(StockStatisticsCache::class);
        $statisticsCache->forget();

        Product::query()->create([
            'sku' => 'PRODUCT-001',
            'name' => 'First product',
            'description' => 'First product description',
        ]);

        $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->getJson('/api/v1/statistics/stock')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.products_total', 1);

        $this->assertTrue(Cache::has('statistics.stock'));

        Product::query()->create([
            'sku' => 'PRODUCT-002',
            'name' => 'Second product',
            'description' => 'Second product description',
        ]);

        $this->assertFalse(Cache::has('statistics.stock'));

        $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->getJson('/api/v1/statistics/stock')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.products_total', 2);

        $this->assertTrue(Cache::has('statistics.stock'));
    }
}
