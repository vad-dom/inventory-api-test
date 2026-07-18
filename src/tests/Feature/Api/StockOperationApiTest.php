<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOperationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_increases_stock_balance(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-001',
            'name' => 'Test product',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH-001',
            'name' => 'Main warehouse',
            'is_active' => true,
        ]);

        $firstResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 7,
                'comment' => 'First income',
            ]);

        $firstResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', null)
            ->assertJsonPath('data.target_warehouse_id', $warehouse->id)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.quantity', 7)
            ->assertJsonPath('data.comment', 'First income');

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => null,
            'target_warehouse_id' => $warehouse->id,
            'type' => 'income',
            'quantity' => 7,
            'comment' => 'First income',
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
                'comment' => 'Second income',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', null)
            ->assertJsonPath('data.target_warehouse_id', $warehouse->id)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.quantity', 10)
            ->assertJsonPath('data.comment', 'Second income')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'product_id',
                    'source_warehouse_id',
                    'target_warehouse_id',
                    'type',
                    'quantity',
                    'comment',
                    'created_at',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 17,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => null,
            'target_warehouse_id' => $warehouse->id,
            'type' => 'income',
            'quantity' => 10,
            'comment' => 'Second income',
        ]);

        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_write_off_decreases_stock_balance(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-WRITE-OFF-001',
            'name' => 'Test write-off product',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH-WRITE-OFF-001',
            'name' => 'Write-off warehouse',
            'is_active' => true,
        ]);

        $incomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 20,
                'comment' => 'Stock before write-off',
            ]);

        $incomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', null)
            ->assertJsonPath('data.target_warehouse_id', $warehouse->id)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.quantity', 20)
            ->assertJsonPath('data.comment', 'Stock before write-off');

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => null,
            'target_warehouse_id' => $warehouse->id,
            'type' => 'income',
            'quantity' => 20,
            'comment' => 'Stock before write-off',
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/write-off', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 7,
                'comment' => 'Damaged items',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', $warehouse->id)
            ->assertJsonPath('data.target_warehouse_id', null)
            ->assertJsonPath('data.type', 'write_off')
            ->assertJsonPath('data.quantity', 7)
            ->assertJsonPath('data.comment', 'Damaged items')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'product_id',
                    'source_warehouse_id',
                    'target_warehouse_id',
                    'type',
                    'quantity',
                    'comment',
                    'created_at',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 13,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => $warehouse->id,
            'target_warehouse_id' => null,
            'type' => 'write_off',
            'quantity' => 7,
            'comment' => 'Damaged items',
        ]);

        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_write_off_more_than_available_stock_returns_conflict(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-INSUFFICIENT-001',
            'name' => 'Insufficient stock product',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH-INSUFFICIENT-001',
            'name' => 'Insufficient stock warehouse',
            'is_active' => true,
        ]);

        $incomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 5,
                'comment' => 'Initial stock',
            ]);

        $incomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.quantity', 5);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/write-off', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 7,
                'comment' => 'Too many items',
            ]);

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK')
            ->assertJsonPath('error.message', 'Not enough stock for this operation.')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => $warehouse->id,
            'target_warehouse_id' => null,
            'type' => 'write_off',
            'quantity' => 7,
        ]);

        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_transfer_changes_balances_on_both_warehouses(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-TRANSFER-001',
            'name' => 'Test transfer product',
            'is_active' => true,
        ]);

        $sourceWarehouse = Warehouse::query()->create([
            'code' => 'WH-SOURCE-001',
            'name' => 'Source warehouse',
            'is_active' => true,
        ]);

        $targetWarehouse = Warehouse::query()->create([
            'code' => 'WH-TARGET-001',
            'name' => 'Target warehouse',
            'is_active' => true,
        ]);

        $incomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $sourceWarehouse->id,
                'quantity' => 20,
                'comment' => 'Stock before transfer',
            ]);

        $incomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', null)
            ->assertJsonPath('data.target_warehouse_id', $sourceWarehouse->id)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.quantity', 20)
            ->assertJsonPath('data.comment', 'Stock before transfer');

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $targetWarehouse->id,
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/transfer', [
                'product_id' => $product->id,
                'source_warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'quantity' => 7,
                'comment' => 'Transfer to target warehouse',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.source_warehouse_id', $sourceWarehouse->id)
            ->assertJsonPath('data.target_warehouse_id', $targetWarehouse->id)
            ->assertJsonPath('data.type', 'transfer')
            ->assertJsonPath('data.quantity', 7)
            ->assertJsonPath('data.comment', 'Transfer to target warehouse')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'product_id',
                    'source_warehouse_id',
                    'target_warehouse_id',
                    'type',
                    'quantity',
                    'comment',
                    'created_at',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 13,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $targetWarehouse->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'source_warehouse_id' => $sourceWarehouse->id,
            'target_warehouse_id' => $targetWarehouse->id,
            'type' => 'transfer',
            'quantity' => 7,
            'comment' => 'Transfer to target warehouse',
        ]);

        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_transfer_to_same_warehouse_returns_validation_error(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-SAME-WH-001',
            'name' => 'Same warehouse product',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH-SAME-001',
            'name' => 'Same warehouse',
            'is_active' => true,
        ]);

        $incomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 20,
                'comment' => 'Stock before invalid transfer',
            ]);

        $incomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/transfer', [
                'product_id' => $product->id,
                'source_warehouse_id' => $warehouse->id,
                'target_warehouse_id' => $warehouse->id,
                'quantity' => 5,
                'comment' => 'Invalid transfer',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.message', 'Validation failed.')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields' => [
                        'target_warehouse_id',
                    ],
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 15,
        ]);

        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_stock_movements_can_be_filtered_by_type(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-PRODUCT-001',
            'name' => 'Test product',
            'is_active' => true,
        ]);

        $sourceWarehouse = Warehouse::query()->create([
            'code' => 'SOURCE-WAREHOUSE',
            'name' => 'Source warehouse',
            'is_active' => true,
        ]);

        $targetWarehouse = Warehouse::query()->create([
            'code' => 'TARGET-WAREHOUSE',
            'name' => 'Target warehouse',
            'is_active' => true,
        ]);

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $sourceWarehouse->id,
                'quantity' => 10,
            ])
            ->assertCreated();

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/write-off', [
                'product_id' => $product->id,
                'warehouse_id' => $sourceWarehouse->id,
                'quantity' => 2,
            ])
            ->assertCreated();

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/transfer', [
                'product_id' => $product->id,
                'source_warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'quantity' => 3,
            ])
            ->assertCreated();

        $this->assertDatabaseCount('stock_movements', 3);

        $response = $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->getJson('/api/v1/stock/movements?type=income');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_id', $product->id)
            ->assertJsonPath('data.0.source_warehouse_id', null)
            ->assertJsonPath('data.0.target_warehouse_id', $sourceWarehouse->id)
            ->assertJsonPath('data.0.quantity', 10)
            ->assertJsonPath('data.0.type', 'income')
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonPath('data.0.source_warehouse', null)
            ->assertJsonPath('data.0.target_warehouse.id', $sourceWarehouse->id)
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'source_warehouse_id',
                        'target_warehouse_id',
                        'quantity',
                        'type',
                        'comment',
                        'created_at',
                        'product' => [
                            'id',
                            'sku',
                            'name',
                        ],
                        'source_warehouse',
                        'target_warehouse' => [
                            'id',
                            'code',
                            'name',
                        ],
                    ],
                ],
                'meta' => [
                    'request_id',
                    'page',
                    'per_page',
                    'total',
                ],
            ]);
    }
}
