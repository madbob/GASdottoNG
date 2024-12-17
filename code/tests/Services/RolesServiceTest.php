<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Role;
use App\User;
use App\Supplier;

class RolesServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier1 = Supplier::factory()->create();
        $this->supplier2 = Supplier::factory()->create();

        $this->userWithAdminPerm = $this->createRoleAndUser($this->gas, 'gas.permissions,users.admin');
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
    }

    /*
        Salvataggio Ruolo con permessi sbagliati
    */
    public function test_fails_to_store()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        app()->make('RolesService')->store([
            'name' => 'Pippo',
            'parent_id' => 0,
        ]);
    }

    /*
        Salvataggio Ruolo
    */
    public function test_store()
    {
        $this->actingAs($this->userWithAdminPerm);

        $role = app()->make('RolesService')->store([
            'name' => 'Pippo',
            'actions' => ['supplier.view', 'users.view'],
        ]);

        $this->assertEquals('Pippo', $role->name);
        $this->assertEquals(0, $role->parent_id);
        $this->assertTrue($role->enabledAction('supplier.view'));
        $this->assertTrue($role->enabledAction('users.view'));
        $this->assertFalse($role->enabledAction('supplier.modify'));

        $this->nextRound();

        app()->make('RolesService')->attachAction($role->id, 'supplier.modify');

        $this->nextRound();

        $role = Role::find($role->id);
        $this->assertTrue($role->enabledAction('supplier.modify'));

        $this->nextRound();

        app()->make('RolesService')->detachAction($role->id, 'supplier.modify');

        $this->nextRound();

        $role = Role::find($role->id);
        $this->assertFalse($role->enabledAction('supplier.modify'));

        $this->nextRound();

        app()->make('RolesService')->attachUser($this->userWithNoPerms->id, $role->id, null);
        $user = app()->make('UsersService')->show($this->userWithNoPerms->id);
        $target = $user->targetsByAction('supplier.view');
        $this->assertTrue(count($target) > 0);

        $this->nextRound();

        app()->make('RolesService')->detachUser($this->userWithNoPerms->id, $role->id, null);
        $user = app()->make('UsersService')->show($this->userWithNoPerms->id);
        $target = $user->targetsByAction('supplier.view');
        $this->assertTrue(count($target) == 0);
    }

    /*
        Modifica Ruolo con permessi sbagliati
    */
    public function test_fails_to_update()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('RolesService')->update(0, []);
    }

    /*
        Modifica Ruolo con ID non esistente
    */
    public function test_fails_to_update_no_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('RolesService')->update('id', []);
    }

    /*
        Modifica Ruolo
    */
    public function test_update()
    {
        $this->actingAs($this->userWithAdminPerm);

        $role = Role::inRandomOrder()->first();
        $this->assertNotEquals('Mario', $role->name);

        $role = app()->make('RolesService')->update($role->id, [
            'name' => 'Mario',
        ]);

        $this->assertEquals('Mario', $role->name);
    }

    /*
        Cancellazione Ruolo con permessi sbagliati
    */
    public function test_fails_to_destroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        $role = Role::inRandomOrder()->first();
        app()->make('RolesService')->destroy($role->id);
    }

    /*
        Cancellazione Ruolo
    */
    public function test_destroy()
    {
        $this->actingAs($this->userWithAdminPerm);
        $role = Role::inRandomOrder()->first();
        app()->make('RolesService')->destroy($role->id);

        $this->nextRound();
        $role = Role::find($role->id);
        $this->assertNull($role);
    }
}
