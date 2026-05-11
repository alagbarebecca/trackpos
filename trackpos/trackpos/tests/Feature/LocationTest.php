<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
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
    public function user_can_view_locations_list()
    {
        Location::create(['name' => 'Main Store', 'type' => 'store']);
        Location::create(['name' => 'Warehouse', 'type' => 'warehouse']);

        $response = $this->actingAs($this->user)->get('/locations');

        $response->assertStatus(200);
        $response->assertSee('Main Store');
        $response->assertSee('Warehouse');
    }

    /** @test */
    public function user_can_create_a_location()
    {
        $locationData = [
            'name' => 'New Store',
            'type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'phone' => '555-1234',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->post('/locations', $locationData);

        $response->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', ['name' => 'New Store']);
    }

    /** @test */
    public function user_can_update_a_location()
    {
        $location = Location::create([
            'name' => 'Old Name',
            'type' => 'store',
        ]);

        $response = $this->actingAs($this->user)->put("/locations/{$location->id}", [
            'name' => 'Updated Name',
            'type' => 'store',
            'is_active' => true,
        ]);

        $response->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', ['name' => 'Updated Name']);
    }

    /** @test */
    public function user_can_delete_a_location()
    {
        $location = Location::create([
            'name' => 'To Delete',
            'type' => 'store',
        ]);

        $response = $this->actingAs($this->user)->delete("/locations/{$location->id}");

        $response->assertRedirect('/locations');
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    /** @test */
    public function location_requires_name()
    {
        $response = $this->actingAs($this->user)->post('/locations', [
            'type' => 'store',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function location_requires_valid_type()
    {
        $response = $this->actingAs($this->user)->post('/locations', [
            'name' => 'Test',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors('type');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_locations()
    {
        $response = $this->get('/locations');

        $response->assertRedirect('/login');
    }
}