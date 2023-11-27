<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Exceptions\AuthException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Artisan;

use App\Role;
use App\User;
use App\Supplier;

class RolesServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
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
    public function testFailsToStore()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);

        app()->make('RolesService')->store(array(
            'name' => 'Pippo',
            'parent_id' => 0,
        ));
    }

    /*
        Salvataggio Ruolo
    */
    public function testStore()
    {
        $this->actingAs($this->userWithAdminPerm);

        $role = app()->make('RolesService')->store(array(
            'name' => 'Pippo',
			'actions' => ['supplier.view', 'users.view'],
        ));

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
        $target = $this->userWithNoPerms->targetsByAction('supplier.view');
        $this->assertTrue(count($target) > 0);

        $this->nextRound();

        app()->make('RolesService')->detachUser($this->userWithNoPerms->id, $role->id, null);
        $target = $this->userWithNoPerms->targetsByAction('supplier.view');
        $this->assertTrue(count($target) == 0);
    }

    /*
        Modifica Ruolo con permessi sbagliati
    */
    public function testFailsToUpdate()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('RolesService')->update(0, array());
    }

    /*
        Modifica Ruolo con ID non esistente
    */
    public function testFailsToUpdateNoID()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithAdminPerm);
        app()->make('RolesService')->update('id', array());
    }

    /*
        Modifica Ruolo
    */
    public function testUpdate()
    {
        $this->actingAs($this->userWithAdminPerm);

		$role = Role::inRandomOrder()->first();
		$this->assertNotEquals('Mario', $role->name);

        $role = app()->make('RolesService')->update($role->id, array(
            'name' => 'Mario',
        ));

        $this->assertEquals('Mario', $role->name);
    }

    /*
        Cancellazione Ruolo con permessi sbagliati
    */
    public function testFailsToDestroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
		$role = Role::inRandomOrder()->first();
        app()->make('RolesService')->destroy($role->id);
    }

    /*
        Cancellazione Ruolo
    */
    public function testDestroy()
    {
        $this->actingAs($this->userWithAdminPerm);
		$role = Role::inRandomOrder()->first();
        app()->make('RolesService')->destroy($role->id);

        $this->nextRound();
        $role = Role::find($role->id);
        $this->assertNull($role);
    }
}
