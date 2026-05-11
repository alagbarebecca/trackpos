<?php

namespace Tests\Unit;

use App\Models\WasteRecord;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteRecordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_waste_record()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $waste = WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
            'reason' => 'Expired',
            'notes' => 'Product expired past date',
        ]);

        $this->assertDatabaseHas('waste_records', [
            'product_id' => $product->id,
            'quantity' => 5,
            'reason' => 'Expired',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_product()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $waste = WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 3,
            'reason' => 'Damaged',
        ]);

        $this->assertEquals($product->id, $waste->product->id);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $waste = WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 3,
            'reason' => 'Damaged',
        ]);

        $this->assertEquals($user->id, $waste->user->id);
    }

    /** @test */
    public function it_requires_quantity_greater_than_zero()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 0,
            'reason' => 'Test',
        ]);
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Create waste record with specific date
        WasteRecord::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 2,
            'reason' => 'Test',
            'created_at' => now()->subDay(),
        ]);

        $wasteToday = WasteRecord::whereDate('created_at', today())->get();

        $this->assertEquals(0, $wasteToday->count());
    }
}