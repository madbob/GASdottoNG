<?php

namespace Tests\Navigation;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Role;
use App\User;

class BrowsingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->make('RolesService')->initSystemRoles();

        $this->userWithAdminPerm = User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userWithAdminPerm->addRole(roleByIdentifier('admin'), $this->gas);
    }

    public function test_admin()
    {
        $routes = [
            'profile',
            'users.index',
            'suppliers.index',
            'orders.index',
            'bookings.index',
            'movements.index',
            'stats.index',
            'notifications.index',
        ];

        foreach($routes as $route) {
            $url = route($route);
            $response = $this->actingAs($this->userWithAdminPerm)->get($url);
            $response->assertStatus(200);
        }

        $url = route('gas.edit', $this->gas->id);
        $response = $this->actingAs($this->userWithAdminPerm)->get($url);
        $response->assertStatus(200);
    }
}
