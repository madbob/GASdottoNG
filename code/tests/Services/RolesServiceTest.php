<?php

namespace Tests\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use MadBob\Larastrap\Integrations\LarastrapStack;

use Tests\TestCase;
use App\Exceptions\AuthException;
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
        Salvataggio Ruolo
    */
    public function test_store()
    {
        $this->actingAs($this->userWithAdminPerm);

        $request = LarastrapStack::autoreadRender('commons.addingbutton', [
            'template' => 'permissions.base-edit',
            'typename' => 'role',
            'typename_readable' => __('texts.permissions.role'),
            'targeturl' => 'roles',
            'autoread' => true,
        ]);

        $request = array_merge($request, [
            'name' => 'Pippo',
            'parent' => 0,
            'actions' => ['supplier.view', 'users.view'],
        ]);

        $role = app()->make('RolesService')->store($request);

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
        app()->make('RolesService')->store([]);
    }

    /*
        Modifica Ruolo
    */
    public function test_update()
    {
        $this->actingAs($this->userWithAdminPerm);

        $role = Role::inRandomOrder()->first();
        $this->assertNotEquals('Mario', $role->name);

        $request = LarastrapStack::autoreadRender('permissions.edit', ['role' => $role]);
        $request = array_merge($request, [
            'name' => 'Mario',
            'parent' => $role->parent_id,
        ]);

        $role = app()->make('RolesService')->store($request);

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
