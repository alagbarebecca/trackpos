<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user and assign role
        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        
        $role = \App\Models\Role::create(['name' => 'admin']);
        $this->user->roles()->attach($role->id);
    }

    /** @test */
    public function user_can_access_import_export_page()
    {
        $response = $this->actingAs($this->user)->get('/import-export');

        $response->assertStatus(200);
        $response->assertSee('Import/Export');
    }

    /** @test */
    public function user_can_export_products()
    {
        // Create a product
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Test Product',
            'code' => 'TP001',
            'barcode' => '123456789',
            'price' => 10.00,
            'cost' => 5.00,
            'quantity' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)->get('/import-export/products/export');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=products.csv');
    }

    /** @test */
    public function user_can_export_sales()
    {
        $response = $this->actingAs($this->user)->get('/import-export/sales/export');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=sales.csv');
    }

    /** @test */
    public function user_can_export_customers()
    {
        $response = $this->actingAs($this->user)->get('/import-export/customers/export');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=customers.csv');
    }

    /** @test */
    public function user_can_download_sample_template()
    {
        $response = $this->actingAs($this->user)->get('/import-export/template');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_import_export()
    {
        $response = $this->get('/import-export');

        $response->assertRedirect('/login');
    }
}