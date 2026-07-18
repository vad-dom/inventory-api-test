<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_can_be_created(): void
    {
        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/warehouses', [
                'code' => 'WH-001',
                'name' => 'Main warehouse',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'WH-001')
            ->assertJsonPath('data.name', 'Main warehouse')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'code',
                    'name',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-001',
            'name' => 'Main warehouse',
            'is_active' => true,
        ]);
    }

    public function test_warehouse_with_movements_cannot_be_deleted(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-PRODUCT-001',
            'name' => 'Test product',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'TEST-WAREHOUSE-001',
            'name' => 'Test warehouse',
            'is_active' => true,
        ]);

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
            ])
            ->assertCreated();

        $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->postJson('/api/v1/stock/write-off', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);

        $this->assertDatabaseCount('stock_movements', 2);

        $response = $this
            ->withHeaders(['X-Api-Key' => config('api.key')])
            ->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'WAREHOUSE_CANNOT_BE_DELETED')
            ->assertJsonPath('error.message', 'Warehouse has movements and cannot be deleted.')
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

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
        ]);
    }
}
