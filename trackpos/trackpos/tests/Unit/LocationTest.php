<?php

namespace Tests\Unit;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_location()
    {
        $location = Location::create([
            'name' => 'Main Store',
            'type' => 'store',
            'address' => '123 Main Street',
            'city' => 'New York',
            'phone' => '555-1234',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'Main Store',
            'type' => 'store',
        ]);
    }

    public function test_it_has_default_is_active_value()
    {
        $location = Location::create([
            'name' => 'Test Store',
            'type' => 'warehouse',
        ]);

        $this->assertTrue($location->is_active);
    }

    public function test_it_can_have_products()
    {
        $location = Location::create([
            'name' => 'Test Store',
            'type' => 'store',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $location->products());
    }

    public function test_it_filters_active_locations()
    {
        Location::create(['name' => 'Active Store', 'type' => 'store', 'is_active' => true]);
        Location::create(['name' => 'Inactive Store', 'type' => 'store', 'is_active' => false]);

        $activeLocations = Location::active()->get();

        $this->assertEquals(1, $activeLocations->count());
        $this->assertEquals('Active Store', $activeLocations->first()->name);
    }

    public function test_it_filters_by_type()
    {
        Location::create(['name' => 'Store 1', 'type' => 'store']);
        Location::create(['name' => 'Warehouse 1', 'type' => 'warehouse']);

        $stores = Location::whereType('store')->get();

        $this->assertEquals(1, $stores->count());
    }
}