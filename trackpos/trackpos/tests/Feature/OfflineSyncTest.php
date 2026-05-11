<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
    }

    public function test_it_can_sync_offline_sale()
    {
        // Create a test product
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'barcode' => '123456789',
            'sell_price' => 100.00,
            'stock_quantity' => 50,
            'is_active' => true,
        ]);

        $saleData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ],
            'payment_method' => 'cash',
            'paid_amount' => 250.00,
            'tax_rate' => 10,
            'discount' => 20,
            'customer_id' => null,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/pos/sync-sale', $saleData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('sales', [
            'payment_method' => 'cash',
            'total' => 200, // (100*2) - 20 + 18 = 218-20+18
        ]);
    }

    public function test_it_updates_stock_after_sync()
    {
        $initialStock = 50;
        
        $product = Product::create([
            'name' => 'Stock Test Product',
            'sku' => 'STOCK-001',
            'barcode' => '111111111',
            'sell_price' => 50.00,
            'stock_quantity' => $initialStock,
            'is_active' => true,
        ]);

        $saleData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ]
            ],
            'payment_method' => 'card',
            'paid_amount' => 275.00,
            'tax_rate' => 10,
            'discount' => 0,
        ];

        $this->actingAs($this->user)
            ->postJson('/pos/sync-sale', $saleData);

        $product->refresh();
        $this->assertEquals($initialStock - 5, $product->stock_quantity);
    }

    public function test_it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/pos/sync-sale', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items', 'payment_method', 'paid_amount']);
    }

    public function test_it_rejects_invalid_payment_method()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-002',
            'sell_price' => 100.00,
            'stock_quantity' => 10,
        ]);

        $saleData = [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ],
            'payment_method' => 'invalid',
            'paid_amount' => 100.00,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/pos/sync-sale', $saleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_it_can_handle_multiple_items()
    {
        $product1 = Product::create([
            'name' => 'Product 1',
            'sku' => 'P1-001',
            'sell_price' => 100.00,
            'stock_quantity' => 20,
        ]);
        
        $product2 = Product::create([
            'name' => 'Product 2',
            'sku' => 'P2-001', 
            'sell_price' => 50.00,
            'stock_quantity' => 30,
        ]);

        $saleData = [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 3],
            ],
            'payment_method' => 'mobile',
            'paid_amount' => 350.00,
            'tax_rate' => 10,
            'discount' => 0,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/pos/sync-sale', $saleData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }

    public function test_it_can_get_pending_count()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/pos/pending-count');

        $response->assertStatus(200)
            ->assertJsonStructure(['pending']);
    }

    public function test_it_handles_authentication_required()
    {
        $response = $this->postJson('/pos/sync-sale', [
            'items' => [],
            'payment_method' => 'cash',
            'paid_amount' => 100,
        ]);

        $response->assertStatus(401);
    }
}