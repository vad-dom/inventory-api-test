<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockBalanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_positive_filter_returns_only_positive_balances(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-POSITIVE-001',
            'name' => 'Positive filter product',
            'is_active' => true,
        ]);

        $positiveWarehouse = Warehouse::query()->create([
            'code' => 'WH-POSITIVE-001',
            'name' => 'Positive balance warehouse',
            'is_active' => true,
        ]);

        $zeroWarehouse = Warehouse::query()->create([
            'code' => 'WH-ZERO-001',
            'name' => 'Zero balance warehouse',
            'is_active' => true,
        ]);

        $positiveIncomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $positiveWarehouse->id,
                'quantity' => 10,
                'comment' => 'Positive balance',
            ]);

        $positiveIncomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $zeroIncomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $zeroWarehouse->id,
                'quantity' => 5,
                'comment' => 'Balance before write-off',
            ]);

        $zeroIncomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $writeOffResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/write-off', [
                'product_id' => $product->id,
                'warehouse_id' => $zeroWarehouse->id,
                'quantity' => 5,
                'comment' => 'Write off balance to zero',
            ]);

        $writeOffResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $positiveWarehouse->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $zeroWarehouse->id,
            'quantity' => 0,
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->getJson('/api/v1/stock/balances?only_positive=true');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonPath('data.0.product.sku', $product->sku)
            ->assertJsonPath('data.0.product.name', $product->name)
            ->assertJsonPath('data.0.warehouse.id', $positiveWarehouse->id)
            ->assertJsonPath('data.0.warehouse.code', $positiveWarehouse->code)
            ->assertJsonPath('data.0.warehouse.name', $positiveWarehouse->name)
            ->assertJsonPath('data.0.quantity', 10)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'product' => [
                            'id',
                            'sku',
                            'name',
                        ],
                        'warehouse' => [
                            'id',
                            'code',
                            'name',
                        ],
                        'quantity',
                    ],
                ],
                'meta' => [
                    'page',
                    'per_page',
                    'total',
                    'request_id',
                ],
            ]);
    }
}
