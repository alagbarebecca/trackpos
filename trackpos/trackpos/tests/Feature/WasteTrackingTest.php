<?php

namespace Tests\Feature;

use App\Models\WasteRecord;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        
        $role = \App\Models\Role::create(['name' => 'admin']);
        $this->user->roles()->attach($role->id);
    }

    /** @test */
    public function user_can_view_waste_records()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'quantity' => 5,
            'reason' => 'Expired',
        ]);

        $response = $this->actingAs($this->user)->get('/waste');

        $response->assertStatus(200);
        $response->assertSee('Expired');
    }

    /** @test */
    public function user_can_create_waste_record()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $wasteData = [
            'product_id' => $product->id,
            'quantity' => 3,
            'reason' => 'Damaged',
            'notes' => 'Packaging damaged',
        ];

        $response = $this->actingAs($this->user)->post('/waste', $wasteData);

        $response->assertRedirect('/waste');
        $this->assertDatabaseHas('waste_records', [
            'product_id' => $product->id,
            'quantity' => 3,
            'reason' => 'Damaged',
        ]);
    }

    /** @test */
    public function user_can_delete_waste_record()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $waste = WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'quantity' => 2,
            'reason' => 'Test',
        ]);

        $response = $this->actingAs($this->user)->delete("/waste/{$waste->id}");

        $response->assertRedirect('/waste');
        $this->assertDatabaseMissing('waste_records', ['id' => $waste->id]);
    }

    /** @test */
    public function waste_record_requires_product()
    {
        $response = $this->actingAs($this->user)->post('/waste', [
            'quantity' => 1,
            'reason' => 'Test',
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    /** @test */
    public function waste_record_requires_quantity()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($this->user)->post('/waste', [
            'product_id' => $product->id,
            'reason' => 'Test',
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    /** @test */
    public function waste_record_requires_reason()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($this->user)->post('/waste', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_waste()
    {
        $response = $this->get('/waste');

        $response->assertRedirect('/login');
    }
}