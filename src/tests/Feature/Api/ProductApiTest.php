<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/products', [
                'sku' => 'TEST-SKU-001',
                'name' => 'Test product',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'TEST-SKU-001')
            ->assertJsonPath('data.name', 'Test product')
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'sku',
                    'name',
                    'description',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-SKU-001',
            'name' => 'Test product',
            'description' => null,
            'is_active' => true,
        ]);
    }

    public function test_product_cannot_be_created_without_sku(): void
    {
        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/products', [
                'name' => 'Test product',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields' => [
                        'sku',
                    ],
                ],
                'meta' => [
                    'request_id',
                ],
            ]);

        $this->assertDatabaseMissing('products', [
            'name' => 'Test product',
        ]);
    }

    public function test_product_with_positive_stock_cannot_be_deactivated(): void
    {
        $product = Product::query()->create([
            'sku' => 'TEST-SKU-DEACTIVATE-001',
            'name' => 'Product with stock',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'code' => 'WH-DEACTIVATE-001',
            'name' => 'Warehouse',
            'is_active' => true,
        ]);

        $incomeResponse = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->postJson('/api/v1/stock/income', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
                'comment' => 'Initial stock',
            ]);

        $incomeResponse
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        $response = $this
            ->withHeader('X-Api-Key', config('api.key'))
            ->patchJson("/api/v1/products/{$product->id}/deactivate");

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'PRODUCT_HAS_STOCK')
            ->assertJsonPath('error.message', 'Product has stock and cannot be deactivated.')
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

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    }

    public function test_product_with_movements_cannot_be_deleted(): void
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
            ->deleteJson("/api/v1/products/{$product->id}");

        $response
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'PRODUCT_CANNOT_BE_DELETED')
            ->assertJsonPath('error.message', 'Product has movements and cannot be deleted.')
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

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }
}
